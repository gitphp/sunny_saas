<?php

/**
 * 流程定义、分组和版本
 *
 * @package     App\Modules\Approval\Application\Definition
 * @license     MIT License (MIT)
 * @author      Sunny Yang <githup@163.com> QQ4873473
 * @copyright   Copyright (c) 2024 YourCompany. All rights reserved.
 * @link        http://www.budff.com
 */

namespace App\Modules\Approval\Application\Definition;

use App\Modules\Approval\Application\Design\FormSchema;
use App\Modules\Approval\Application\Design\ProcessGraph;
use App\Modules\Approval\Domain\VersionStatus;
use App\Modules\Approval\Infrastructure\Persistence\ApprovalDefinition;
use App\Modules\Approval\Infrastructure\Persistence\ApprovalGroup;
use App\Modules\Approval\Infrastructure\Persistence\ApprovalVersion;
use App\Modules\Iam\Application\Contracts\MemberDirectory;
use App\Shared\Exceptions\BusinessException;
use App\Shared\Exceptions\ErrorCode;
use Illuminate\Support\Facades\DB;

/**
 * 流程定义、分组和版本
 */
class DefinitionService
{
    /**
     * 注入依赖
     *
     * 选人走 IAM 契约，避免审批模块引用成员模型。
     */
    public function __construct(private readonly MemberDirectory $members) {}

    /**
     * @return list<array<string, mixed>>
     */
    public function groups(): array
    {
        return ApprovalGroup::query()->orderBy('sort')->orderBy('id')->get()->map(fn (ApprovalGroup $group): array => $this->presentGroup($group))->all();
    }

    /**
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    public function storeGroup(array $input): array
    {
        $group = ApprovalGroup::query()->create([
            'name' => $this->name($input['name'] ?? ''),
            'sort' => (int) ($input['sort'] ?? 0),
            'status' => 1,
        ]);

        return $this->presentGroup($group);
    }

    /**
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    public function updateGroup(int $id, array $input): array
    {
        $group = $this->findGroup($id);
        $group->name = $this->name($input['name'] ?? $group->name);
        $group->sort = array_key_exists('sort', $input) ? (int) $input['sort'] : (int) $group->sort;

        if (array_key_exists('status', $input)) {
            $group->status = (int) $input['status'] === 1 ? 1 : 0;
        }

        $group->save();

        return $this->presentGroup($group);
    }

    /**
     * 删除没有流程的分组
     */
    public function destroyGroup(int $id): void
    {
        $group = $this->findGroup($id);

        if (ApprovalDefinition::query()->where('group_id', $group->id)->exists()) {
            throw new BusinessException(ErrorCode::VALIDATION, '分组下还有流程，不能删除', 422);
        }

        $group->delete();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function definitions(): array
    {
        $groups = ApprovalGroup::query()->get()->keyBy('id');

        return ApprovalDefinition::query()->orderBy('id')->get()->map(function (ApprovalDefinition $definition) use ($groups): array {
            $row = $this->presentDefinition($definition);
            $row['group_name'] = (string) ($groups[$definition->group_id]?->name ?? '');
            $published = $this->published($definition);
            $draft = $this->draft($definition);
            $row['published_version_no'] = $published ? (int) $published->version_no : null;
            $row['draft_version_no'] = $draft ? (int) $draft->version_no : null;

            return $row;
        })->all();
    }

    /**
     * 发起页只给出启用且已发布的流程
     *
     * @return list<array<string, mixed>>
     */
    public function catalog(): array
    {
        $groups = ApprovalGroup::query()->where('status', 1)->orderBy('sort')->orderBy('id')->get();
        $definitions = ApprovalDefinition::query()->where('status', 1)->where('current_version_id', '>', 0)->orderBy('id')->get();
        $versions = ApprovalVersion::query()->whereIn('id', $definitions->pluck('current_version_id')->filter()->all())->get()->keyBy('id');
        $bucket = [];

        foreach ($groups as $group) {
            $bucket[(int) $group->id] = [
                'id' => (int) $group->id,
                'name' => (string) $group->name,
                'definitions' => [],
            ];
        }

        $ungrouped = ['id' => 0, 'name' => '未分组', 'definitions' => []];

        foreach ($definitions as $definition) {
            $version = $versions[(int) $definition->current_version_id] ?? null;

            if ($version === null) {
                continue;
            }

            $item = [
                'id' => (int) $definition->id,
                'code' => (string) $definition->code,
                'name' => (string) $definition->name,
                'icon' => (string) $definition->icon,
                'description' => (string) $definition->description,
                'allow_revoke' => (int) $definition->allow_revoke,
                'version_no' => (int) $version->version_no,
                'form_schema' => $version->form_schema,
                'flow_schema' => $version->flow_schema,
            ];
            $groupId = (int) ($definition->group_id ?? 0);

            if (isset($bucket[$groupId])) {
                $bucket[$groupId]['definitions'][] = $item;
            } else {
                $ungrouped['definitions'][] = $item;
            }
        }

        $rows = array_values(array_filter($bucket, fn (array $group): bool => $group['definitions'] !== []));

        if ($ungrouped['definitions'] !== []) {
            $rows[] = $ungrouped;
        }

        return $rows;
    }

    /**
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    public function storeDefinition(array $input): array
    {
        $code = strtolower(trim((string) ($input['code'] ?? '')));

        if (! preg_match('/^[a-z][a-z0-9_]{1,31}$/', $code)) {
            throw new BusinessException(ErrorCode::VALIDATION, '流程编码只能使用小写字母、数字和下划线', 422);
        }

        if (ApprovalDefinition::withTrashed()->where('code', $code)->exists()) {
            throw new BusinessException(ErrorCode::VALIDATION, '流程编码已存在', 422);
        }

        $groupId = $this->groupId($input['group_id'] ?? null);

        return DB::transaction(function () use ($input, $code, $groupId): array {
            $definition = ApprovalDefinition::query()->create([
                'group_id' => $groupId,
                'code' => $code,
                'name' => $this->name($input['name'] ?? ''),
                'icon' => $this->icon($input['icon'] ?? 'user'),
                'description' => mb_substr(trim((string) ($input['description'] ?? '')), 0, 255),
                'status' => 1,
                'allow_revoke' => 1,
                'current_version_id' => null,
            ]);
            ApprovalVersion::query()->create([
                'definition_id' => $definition->id,
                'version_no' => 1,
                'status' => VersionStatus::DRAFT,
                'form_schema' => ['fields' => []],
                'flow_schema' => ProcessGraph::blank(),
                'published_at' => null,
            ]);

            return $this->show((int) $definition->id);
        });
    }

    /**
     * @return array<string, mixed>
     */
    public function show(int $id): array
    {
        $definition = $this->findDefinition($id);
        $versions = ApprovalVersion::query()->where('definition_id', $definition->id)->orderBy('version_no')->get();
        $draft = $versions->firstWhere('status', VersionStatus::DRAFT);
        $published = $versions->firstWhere('status', VersionStatus::PUBLISHED);
        $editing = $draft ?? $published;
        $group = $definition->group_id ? ApprovalGroup::query()->find($definition->group_id) : null;
        $row = $this->presentDefinition($definition);
        $row['group_name'] = (string) ($group->name ?? '');
        $row['editing'] = $editing ? $this->presentVersion($editing) : null;
        $row['versions'] = $versions->map(fn (ApprovalVersion $version): array => [
            'id' => (int) $version->id,
            'version_no' => (int) $version->version_no,
            'status' => (int) $version->status,
            'published_at' => $version->published_at?->toIso8601String(),
        ])->values()->all();

        return $row;
    }

    /**
     * 基础信息立即生效。表单和流程图写入草稿，已发布版本保持不变。
     *
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    public function update(int $id, array $input): array
    {
        $definition = $this->findDefinition($id);

        if (array_key_exists('name', $input)) {
            $definition->name = $this->name($input['name']);
        }

        if (array_key_exists('icon', $input)) {
            $definition->icon = $this->icon($input['icon']);
        }

        if (array_key_exists('description', $input)) {
            $definition->description = mb_substr(trim((string) $input['description']), 0, 255);
        }

        if (array_key_exists('group_id', $input)) {
            $definition->group_id = $this->groupId($input['group_id']);
        }

        if (array_key_exists('status', $input)) {
            $definition->status = (int) $input['status'] === 1 ? 1 : 0;
        }

        if (array_key_exists('allow_revoke', $input)) {
            $definition->allow_revoke = (int) $input['allow_revoke'] === 1 ? 1 : 0;
        }

        $definition->save();

        if (array_key_exists('form_schema', $input) || array_key_exists('flow_schema', $input)) {
            $draft = $this->ensureDraft($definition);
            if (array_key_exists('form_schema', $input)) {
                $draft->form_schema = FormSchema::normalize((array) $input['form_schema']);
            }

            if (array_key_exists('flow_schema', $input)) {
                $draft->flow_schema = ProcessGraph::normalize((array) $input['flow_schema'])->toArray();
            }

            $draft->save();
        }

        return $this->show((int) $definition->id);
    }

    /**
     * 把当前草稿发布为新版本，并归档上一份已发布版本
     *
     * @return array<string, mixed>
     */
    public function publish(int $id): array
    {
        $definition = $this->findDefinition($id);
        $draft = $this->draft($definition);

        if ($draft === null) {
            throw new BusinessException(ErrorCode::VALIDATION, '没有待发布的草稿', 422);
        }

        $form = FormSchema::normalize((array) $draft->form_schema);
        $graph = ProcessGraph::normalize((array) $draft->flow_schema);
        $graph->assertPublishable(fn (int $userId): bool => $this->members->isActive($userId));

        DB::transaction(function () use ($definition, $draft, $form, $graph): void {
            ApprovalVersion::query()
                ->where('definition_id', $definition->id)
                ->where('status', VersionStatus::PUBLISHED)
                ->update(['status' => VersionStatus::ARCHIVED]);
            $draft->form_schema = $form;
            $draft->flow_schema = $graph->toArray();
            $draft->status = VersionStatus::PUBLISHED;
            $draft->published_at = now();
            $draft->save();
            $definition->current_version_id = $draft->id;
            $definition->save();
        });

        return $this->show((int) $definition->id);
    }

    /**
     * @return list<array{user_id: int, name: string, email: string}>
     */
    public function memberOptions(): array
    {
        return $this->members->listActive();
    }

    /**
     * 已发布内容不改。没有草稿时从当前发布版复制一份。
     */
    private function ensureDraft(ApprovalDefinition $definition): ApprovalVersion
    {
        $draft = $this->draft($definition);

        if ($draft !== null) {
            return $draft;
        }

        $source = $this->published($definition);
        $max = (int) ApprovalVersion::query()->where('definition_id', $definition->id)->max('version_no');

        return ApprovalVersion::query()->create([
            'definition_id' => $definition->id,
            'version_no' => $max + 1,
            'status' => VersionStatus::DRAFT,
            'form_schema' => $source?->form_schema ?: ['fields' => []],
            'flow_schema' => $source?->flow_schema ?: ProcessGraph::blank(),
            'published_at' => null,
        ]);
    }

    private function draft(ApprovalDefinition $definition): ?ApprovalVersion
    {
        return ApprovalVersion::query()
            ->where('definition_id', $definition->id)
            ->where('status', VersionStatus::DRAFT)
            ->first();
    }

    private function published(ApprovalDefinition $definition): ?ApprovalVersion
    {
        if (! $definition->current_version_id) {
            return null;
        }

        return ApprovalVersion::query()->find($definition->current_version_id);
    }

    private function findGroup(int $id): ApprovalGroup
    {
        $group = ApprovalGroup::query()->find($id);

        if ($group === null) {
            throw new BusinessException(ErrorCode::NOT_FOUND, '分组不存在', 404);
        }

        return $group;
    }

    private function findDefinition(int $id): ApprovalDefinition
    {
        $definition = ApprovalDefinition::query()->find($id);

        if ($definition === null) {
            throw new BusinessException(ErrorCode::NOT_FOUND, '流程不存在', 404);
        }

        return $definition;
    }

    private function groupId(mixed $value): ?int
    {
        if ($value === null || $value === '' || (int) $value === 0) {
            return null;
        }

        $this->findGroup((int) $value);

        return (int) $value;
    }

    private function name(mixed $value): string
    {
        $name = trim((string) $value);

        if ($name === '') {
            throw new BusinessException(ErrorCode::VALIDATION, '名称不能为空', 422);
        }

        return mb_substr($name, 0, 100);
    }

    private function icon(mixed $value): string
    {
        $icon = trim((string) $value);

        return $icon === '' ? 'user' : mb_substr($icon, 0, 32);
    }

    /**
     * @return array<string, mixed>
     */
    private function presentGroup(ApprovalGroup $group): array
    {
        return [
            'id' => (int) $group->id,
            'name' => (string) $group->name,
            'sort' => (int) $group->sort,
            'status' => (int) $group->status,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function presentDefinition(ApprovalDefinition $definition): array
    {
        return [
            'id' => (int) $definition->id,
            'group_id' => $definition->group_id ? (int) $definition->group_id : null,
            'code' => (string) $definition->code,
            'name' => (string) $definition->name,
            'icon' => (string) $definition->icon,
            'description' => (string) $definition->description,
            'status' => (int) $definition->status,
            'allow_revoke' => (int) $definition->allow_revoke,
            'current_version_id' => $definition->current_version_id ? (int) $definition->current_version_id : null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function presentVersion(ApprovalVersion $version): array
    {
        return [
            'id' => (int) $version->id,
            'version_no' => (int) $version->version_no,
            'status' => (int) $version->status,
            'form_schema' => $version->form_schema,
            'flow_schema' => $version->flow_schema,
            'published_at' => $version->published_at?->toIso8601String(),
        ];
    }
}
