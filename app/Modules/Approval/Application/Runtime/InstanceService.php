<?php

/**
 * 审批单、任务、记录和抄送
 *
 * @package     App\Modules\Approval\Application\Runtime
 * @license     MIT License (MIT)
 * @author      Sunny Yang <githup@163.com> QQ4873473
 * @copyright   Copyright (c) 2024 YourCompany. All rights reserved.
 * @link        http://www.budff.com
 */

namespace App\Modules\Approval\Application\Runtime;

use App\Modules\Approval\Application\Design\FormSchema;
use App\Modules\Approval\Application\Design\ProcessGraph;
use App\Modules\Approval\Domain\InstanceStatus;
use App\Modules\Approval\Domain\TaskStatus;
use App\Modules\Approval\Domain\VersionStatus;
use App\Modules\Approval\Infrastructure\Persistence\ApprovalDefinition;
use App\Modules\Approval\Infrastructure\Persistence\ApprovalInstance;
use App\Modules\Approval\Infrastructure\Persistence\ApprovalRecord;
use App\Modules\Approval\Infrastructure\Persistence\ApprovalTask;
use App\Modules\Approval\Infrastructure\Persistence\ApprovalVersion;
use App\Modules\Iam\Application\Contracts\MemberDirectory;
use App\Shared\Exceptions\BusinessException;
use App\Shared\Exceptions\ErrorCode;
use Illuminate\Support\Facades\DB;

/**
 * 审批单、任务、记录和抄送
 */
class InstanceService
{
    /**
     * 注入依赖
     *
     * 处理人是否属于本企业，由 IAM 成员目录判断。
     */
    public function __construct(private readonly MemberDirectory $members) {}

    /**
     * 按已发布版本发起审批。调用方可以是审批中心，也可以是其他模块。
     *
     * @param  array<string, mixed>  $formData
     * @return array<string, mixed>
     */
    public function start(string $definitionCode, int $applicantUserId, string $title, array $formData, string $sourceModule = '', int $sourceId = 0): array
    {
        if (! $this->members->isActive($applicantUserId)) {
            throw new BusinessException(ErrorCode::VALIDATION, '申请人不是当前企业的有效成员', 422);
        }

        $definition = ApprovalDefinition::query()->where('code', $definitionCode)->where('status', 1)->first();

        if ($definition === null || ! $definition->current_version_id) {
            throw new BusinessException(ErrorCode::VALIDATION, '流程未发布或已停用', 422);
        }

        $version = ApprovalVersion::query()->find($definition->current_version_id);

        if ($version === null || (int) $version->status !== VersionStatus::PUBLISHED) {
            throw new BusinessException(ErrorCode::VALIDATION, '流程未发布或已停用', 422);
        }

        $schema = FormSchema::normalize((array) $version->form_schema);
        $clean = FormSchema::validateData($schema, $formData);
        $graph = ProcessGraph::normalize((array) $version->flow_schema);
        $title = trim($title) !== '' ? mb_substr(trim($title), 0, 200) : (string) $definition->name;

        return DB::transaction(function () use ($definition, $version, $applicantUserId, $title, $clean, $graph, $sourceModule, $sourceId): array {
            $instance = ApprovalInstance::query()->create([
                'definition_id' => $definition->id,
                'version_id' => $version->id,
                'serial_no' => 'T'.bin2hex(random_bytes(8)),
                'title' => $title,
                'applicant_user_id' => $applicantUserId,
                'status' => InstanceStatus::APPROVING,
                'form_data' => $clean,
                'current_node_key' => 'start',
                'source_module' => mb_substr($sourceModule, 0, 32),
                'source_id' => max(0, $sourceId),
                'submitted_at' => now(),
                'finished_at' => null,
            ]);
            $instance->serial_no = 'AP'.now()->format('Ymd').str_pad((string) $instance->id, 6, '0', STR_PAD_LEFT);
            $instance->save();
            $this->record($instance, 0, 'start', $applicantUserId, 'submit', '');
            $this->proceed($instance, $graph, 'start');

            return $this->show((int) $instance->id, $applicantUserId);
        });
    }

    /**
     * @return array<string, mixed>|null
     */
    public function find(int $instanceId): ?array
    {
        $instance = ApprovalInstance::query()->find($instanceId);

        if ($instance === null) {
            return null;
        }

        return $this->present($instance, 0);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function mine(int $userId): array
    {
        return $this->listInstances(ApprovalInstance::query()->where('applicant_user_id', $userId)->orderByDesc('id')->get());
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function data(): array
    {
        return $this->listInstances(ApprovalInstance::query()->orderByDesc('id')->limit(200)->get());
    }

    /**
     * 待办、已办和抄送都从任务上取，只看当前用户
     *
     * @return list<array<string, mixed>>
     */
    public function tasks(int $userId, string $box): array
    {
        $query = ApprovalTask::query()->where('assignee_user_id', $userId)->orderByDesc('id');

        if ($box === 'todo') {
            $query->where('task_type', 'approve')->where('status', TaskStatus::PENDING);
        } elseif ($box === 'done') {
            $query->where('task_type', 'approve')->where('status', TaskStatus::DONE);
        } elseif ($box === 'cc') {
            $query->where('task_type', 'cc');
        } else {
            throw new BusinessException(ErrorCode::VALIDATION, '任务分类不正确', 422);
        }

        $tasks = $query->limit(200)->get();
        $instances = ApprovalInstance::query()->whereIn('id', $tasks->pluck('instance_id')->all())->get()->keyBy('id');
        $definitions = ApprovalDefinition::query()->whereIn('id', $instances->pluck('definition_id')->all())->get()->keyBy('id');
        $names = $this->names();
        $rows = [];

        foreach ($tasks as $task) {
            $instance = $instances[(int) $task->instance_id] ?? null;

            if ($instance === null) {
                continue;
            }

            $definition = $definitions[(int) $instance->definition_id] ?? null;
            $rows[] = [
                'id' => (int) $task->id,
                'instance_id' => (int) $instance->id,
                'serial_no' => (string) $instance->serial_no,
                'title' => (string) $instance->title,
                'definition_name' => (string) ($definition->name ?? ''),
                'node_name' => (string) $task->node_name,
                'task_type' => (string) $task->task_type,
                'status' => (int) $task->status,
                'instance_status' => (int) $instance->status,
                'applicant_name' => $names[(int) $instance->applicant_user_id] ?? '用户#'.$instance->applicant_user_id,
                'submitted_at' => $instance->submitted_at?->toIso8601String(),
            ];
        }

        return $rows;
    }

    /**
     * @return array<string, mixed>
     */
    public function show(int $id, int $userId): array
    {
        $instance = $this->findInstance($id);
        $this->markCcRead($instance, $userId);

        return $this->present($instance->refresh(), $userId);
    }

    /**
     * @return array<string, mixed>
     */
    public function approve(int $id, int $userId, string $comment): array
    {
        return $this->act($id, $userId, 'approve', $comment);
    }

    /**
     * @return array<string, mixed>
     */
    public function reject(int $id, int $userId, string $comment): array
    {
        return $this->act($id, $userId, 'reject', $comment);
    }

    /**
     * @return array<string, mixed>
     */
    public function revoke(int $id, int $userId, string $comment): array
    {
        $instance = $this->findInstance($id);

        if ((int) $instance->applicant_user_id !== $userId) {
            throw new BusinessException(ErrorCode::VALIDATION, '只有申请人可以撤销', 422);
        }

        if ((int) $instance->status !== InstanceStatus::APPROVING) {
            throw new BusinessException(ErrorCode::VALIDATION, '当前状态不能撤销', 422);
        }

        $definition = ApprovalDefinition::query()->find($instance->definition_id);

        if ($definition === null || (int) $definition->allow_revoke !== 1) {
            throw new BusinessException(ErrorCode::VALIDATION, '该流程不允许撤销审批中的申请', 422);
        }

        DB::transaction(function () use ($instance, $userId, $comment): void {
            $this->skipPending($instance);
            $instance->status = InstanceStatus::REVOKED;
            $instance->current_node_key = '';
            $instance->finished_at = now();
            $instance->save();
            $this->record($instance, 0, '', $userId, 'revoke', $comment);
        });

        return $this->present($instance->refresh(), $userId);
    }

    /**
     * @return array<string, mixed>
     */
    public function comment(int $id, int $userId, string $comment): array
    {
        $instance = $this->findInstance($id);
        $comment = trim($comment);

        if ($comment === '') {
            throw new BusinessException(ErrorCode::VALIDATION, '评论不能为空', 422);
        }

        $involved = (int) $instance->applicant_user_id === $userId
            || ApprovalTask::query()->where('instance_id', $instance->id)->where('assignee_user_id', $userId)->exists();

        if (! $involved) {
            throw new BusinessException(ErrorCode::VALIDATION, '只有相关人员可以评论', 422);
        }

        $this->record($instance, 0, (string) $instance->current_node_key, $userId, 'comment', $comment);

        return $this->present($instance->refresh(), $userId);
    }

    /**
     * @return array<string, mixed>
     */
    private function act(int $id, int $userId, string $action, string $comment): array
    {
        $instance = $this->findInstance($id);

        if ((int) $instance->status !== InstanceStatus::APPROVING) {
            throw new BusinessException(ErrorCode::VALIDATION, '当前状态不能审批', 422);
        }

        $task = ApprovalTask::query()
            ->where('instance_id', $instance->id)
            ->where('assignee_user_id', $userId)
            ->where('task_type', 'approve')
            ->where('status', TaskStatus::PENDING)
            ->where('node_key', $instance->current_node_key)
            ->first();

        if ($task === null) {
            throw new BusinessException(ErrorCode::VALIDATION, '没有待你处理的审批任务', 422);
        }

        $version = ApprovalVersion::query()->find($instance->version_id);
        $graph = ProcessGraph::normalize((array) ($version->flow_schema ?? []));
        $node = $graph->node((string) $task->node_key);

        DB::transaction(function () use ($instance, $task, $userId, $action, $comment, $graph, $node): void {
            $task->status = TaskStatus::DONE;
            $task->acted_at = now();
            $task->save();
            $this->record($instance, (int) $task->id, (string) $task->node_key, $userId, $action, $comment);

            if ($action === 'reject') {
                $this->skipPending($instance);
                $instance->status = InstanceStatus::REJECTED;
                $instance->current_node_key = '';
                $instance->finished_at = now();
                $instance->save();

                return;
            }

            $waiting = ApprovalTask::query()
                ->where('instance_id', $instance->id)
                ->where('node_key', $task->node_key)
                ->where('task_type', 'approve')
                ->where('status', TaskStatus::PENDING)
                ->exists();

            if (($node['sign'] ?? 'any') === 'all' && $waiting) {
                return;
            }

            if ($waiting) {
                $this->skipNode($instance, (string) $task->node_key);
            }

            $this->proceed($instance, $graph, (string) $task->node_key);
        });

        return $this->present($instance->refresh(), $userId);
    }

    private function proceed(ApprovalInstance $instance, ProcessGraph $graph, string $fromKey): void
    {
        $key = $fromKey;

        for ($step = 0; $step < 30; $step++) {
            $next = $graph->next($key, (array) $instance->form_data);

            if ($next['type'] === 'end') {
                $instance->status = InstanceStatus::APPROVED;
                $instance->current_node_key = '';
                $instance->finished_at = now();
                $instance->save();

                return;
            }

            if ($next['type'] === 'approve') {
                $this->openTasks($instance, $graph, $next, 'approve');
                $instance->current_node_key = $next['key'];
                $instance->save();

                return;
            }

            if ($next['type'] === 'cc') {
                $this->openTasks($instance, $graph, $next, 'cc');
                $key = $next['key'];

                continue;
            }

            throw new BusinessException(ErrorCode::VALIDATION, '流程节点类型不正确', 422);
        }

        throw new BusinessException(ErrorCode::VALIDATION, '流程分支过长或存在循环', 422);
    }

    /**
     * @param  array<string, mixed>  $node
     */
    private function openTasks(ApprovalInstance $instance, ProcessGraph $graph, array $node, string $taskType): void
    {
        $userIds = $graph->assignees($node, (int) $instance->applicant_user_id);

        if ($userIds === []) {
            throw new BusinessException(ErrorCode::VALIDATION, '节点「'.$node['name'].'」没有处理人', 422);
        }

        foreach ($userIds as $userId) {
            if (! $this->members->isActive($userId)) {
                throw new BusinessException(ErrorCode::VALIDATION, '节点「'.$node['name'].'」里有不是本企业成员的人员', 422);
            }

            $task = ApprovalTask::query()->create([
                'instance_id' => $instance->id,
                'node_key' => $node['key'],
                'node_name' => $node['name'],
                'assignee_user_id' => $userId,
                'task_type' => $taskType,
                'status' => TaskStatus::PENDING,
                'acted_at' => null,
            ]);

            if ($taskType === 'cc') {
                $this->record($instance, (int) $task->id, $node['key'], (int) $instance->applicant_user_id, 'cc', $node['name']);
            }
        }
    }

    private function skipPending(ApprovalInstance $instance): void
    {
        ApprovalTask::query()
            ->where('instance_id', $instance->id)
            ->where('status', TaskStatus::PENDING)
            ->update(['status' => TaskStatus::SKIPPED, 'acted_at' => now()]);
    }

    private function skipNode(ApprovalInstance $instance, string $nodeKey): void
    {
        ApprovalTask::query()
            ->where('instance_id', $instance->id)
            ->where('node_key', $nodeKey)
            ->where('task_type', 'approve')
            ->where('status', TaskStatus::PENDING)
            ->update(['status' => TaskStatus::SKIPPED, 'acted_at' => now()]);
    }

    private function markCcRead(ApprovalInstance $instance, int $userId): void
    {
        if ($userId <= 0) {
            return;
        }

        ApprovalTask::query()
            ->where('instance_id', $instance->id)
            ->where('assignee_user_id', $userId)
            ->where('task_type', 'cc')
            ->where('status', TaskStatus::PENDING)
            ->update(['status' => TaskStatus::DONE, 'acted_at' => now()]);
    }

    private function record(ApprovalInstance $instance, int $taskId, string $nodeKey, int $actorUserId, string $action, string $comment): void
    {
        ApprovalRecord::query()->create([
            'instance_id' => $instance->id,
            'task_id' => $taskId,
            'node_key' => $nodeKey,
            'actor_user_id' => $actorUserId,
            'action' => $action,
            'comment' => mb_substr(trim($comment), 0, 500),
        ]);
    }

    private function findInstance(int $id): ApprovalInstance
    {
        $instance = ApprovalInstance::query()->find($id);

        if ($instance === null) {
            throw new BusinessException(ErrorCode::NOT_FOUND, '审批单不存在', 404);
        }

        return $instance;
    }

    /**
     * @param  \Illuminate\Support\Collection<int, ApprovalInstance>  $instances
     * @return list<array<string, mixed>>
     */
    private function listInstances($instances): array
    {
        $definitions = ApprovalDefinition::query()->whereIn('id', $instances->pluck('definition_id')->all())->get()->keyBy('id');
        $versions = ApprovalVersion::query()->whereIn('id', $instances->pluck('version_id')->all())->get()->keyBy('id');
        $names = $this->names();
        $rows = [];

        foreach ($instances as $instance) {
            $definition = $definitions[(int) $instance->definition_id] ?? null;
            $version = $versions[(int) $instance->version_id] ?? null;
            $rows[] = [
                'id' => (int) $instance->id,
                'serial_no' => (string) $instance->serial_no,
                'title' => (string) $instance->title,
                'definition_name' => (string) ($definition->name ?? ''),
                'version_no' => (int) ($version->version_no ?? 0),
                'applicant_name' => $names[(int) $instance->applicant_user_id] ?? '用户#'.$instance->applicant_user_id,
                'status' => (int) $instance->status,
                'submitted_at' => $instance->submitted_at?->toIso8601String(),
                'finished_at' => $instance->finished_at?->toIso8601String(),
            ];
        }

        return $rows;
    }

    /**
     * @return array<string, mixed>
     */
    private function present(ApprovalInstance $instance, int $viewerUserId): array
    {
        $definition = ApprovalDefinition::query()->find($instance->definition_id);
        $version = ApprovalVersion::query()->find($instance->version_id);
        $names = $this->names();
        $tasks = ApprovalTask::query()->where('instance_id', $instance->id)->orderBy('id')->get();
        $records = ApprovalRecord::query()->where('instance_id', $instance->id)->orderBy('id')->get();
        $myTask = $tasks->first(fn (ApprovalTask $task): bool => (int) $task->assignee_user_id === $viewerUserId
            && $task->task_type === 'approve'
            && (int) $task->status === TaskStatus::PENDING
            && $task->node_key === $instance->current_node_key);

        return [
            'id' => (int) $instance->id,
            'serial_no' => (string) $instance->serial_no,
            'title' => (string) $instance->title,
            'definition_id' => (int) $instance->definition_id,
            'definition_name' => (string) ($definition->name ?? ''),
            'definition_code' => (string) ($definition->code ?? ''),
            'version_id' => (int) $instance->version_id,
            'version_no' => (int) ($version->version_no ?? 0),
            'applicant_user_id' => (int) $instance->applicant_user_id,
            'applicant_name' => $names[(int) $instance->applicant_user_id] ?? '用户#'.$instance->applicant_user_id,
            'status' => (int) $instance->status,
            'form_schema' => $version->form_schema ?? ['fields' => []],
            'form_data' => $instance->form_data ?? [],
            'flow_schema' => $version->flow_schema ?? ['nodes' => [], 'edges' => []],
            'current_node_key' => (string) $instance->current_node_key,
            'source_module' => (string) $instance->source_module,
            'source_id' => (int) $instance->source_id,
            'allow_revoke' => (int) ($definition->allow_revoke ?? 0),
            'can_approve' => $myTask !== null,
            'can_revoke' => $viewerUserId === (int) $instance->applicant_user_id
                && (int) $instance->status === InstanceStatus::APPROVING
                && (int) ($definition->allow_revoke ?? 0) === 1,
            'submitted_at' => $instance->submitted_at?->toIso8601String(),
            'finished_at' => $instance->finished_at?->toIso8601String(),
            'tasks' => $tasks->map(fn (ApprovalTask $task): array => [
                'id' => (int) $task->id,
                'node_key' => (string) $task->node_key,
                'node_name' => (string) $task->node_name,
                'assignee_user_id' => (int) $task->assignee_user_id,
                'assignee_name' => $names[(int) $task->assignee_user_id] ?? '用户#'.$task->assignee_user_id,
                'task_type' => (string) $task->task_type,
                'status' => (int) $task->status,
                'acted_at' => $task->acted_at?->toIso8601String(),
            ])->all(),
            'records' => $records->map(fn (ApprovalRecord $record): array => [
                'id' => (int) $record->id,
                'action' => (string) $record->action,
                'comment' => (string) $record->comment,
                'node_key' => (string) $record->node_key,
                'actor_user_id' => (int) $record->actor_user_id,
                'actor_name' => $names[(int) $record->actor_user_id] ?? '用户#'.$record->actor_user_id,
                'created_at' => $record->created_at?->toIso8601String(),
            ])->all(),
        ];
    }

    /**
     * @return array<int, string>
     */
    private function names(): array
    {
        $names = [];

        foreach ($this->members->listActive() as $member) {
            $names[$member['user_id']] = $member['name'] !== '' ? $member['name'] : $member['email'];
        }

        return $names;
    }
}
