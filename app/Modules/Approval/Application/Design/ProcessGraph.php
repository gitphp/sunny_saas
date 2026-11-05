<?php

/**
 * 审批流程图
 *
 * @package     App\Modules\Approval\Application\Design
 * @license     MIT License (MIT)
 * @author      Sunny Yang <githup@163.com> QQ4873473
 * @copyright   Copyright (c) 2024 YourCompany. All rights reserved.
 * @link        http://www.budff.com
 */

namespace App\Modules\Approval\Application\Design;

use App\Shared\Exceptions\BusinessException;
use App\Shared\Exceptions\ErrorCode;

/**
 * 审批流程图
 *
 * 节点有开始、审批、抄送和结束。条件写在连线上，按优先级匹配，空条件是默认分支。
 */
final class ProcessGraph
{
    /**
     * @param  array{nodes: list<array<string, mixed>>, edges: list<array<string, mixed>>}  $schema
     */
    private function __construct(private readonly array $schema) {}

    /**
     * 空白流程只有开始和结束
     *
     * @return array{nodes: list<array<string, mixed>>, edges: list<array<string, mixed>>}
     */
    public static function blank(): array
    {
        return [
            'nodes' => [
                ['key' => 'start', 'type' => 'start', 'name' => '开始', 'assignee_type' => '', 'user_ids' => [], 'sign' => 'any'],
                ['key' => 'end', 'type' => 'end', 'name' => '结束', 'assignee_type' => '', 'user_ids' => [], 'sign' => 'any'],
            ],
            'edges' => [
                ['from' => 'start', 'to' => 'end', 'priority' => 1, 'when' => []],
            ],
        ];
    }

    /**
     * 整理流程图
     *
     * @param  array<string, mixed>  $schema
     */
    public static function normalize(array $schema): self
    {
        $nodes = $schema['nodes'] ?? null;
        $edges = $schema['edges'] ?? null;

        if (! is_array($nodes) || ! is_array($edges)) {
            throw new BusinessException(ErrorCode::VALIDATION, '流程图格式不正确', 422);
        }

        $normalizedNodes = [];
        $seen = [];

        foreach (array_values($nodes) as $node) {
            if (! is_array($node)) {
                throw new BusinessException(ErrorCode::VALIDATION, '流程节点格式不正确', 422);
            }

            $key = (string) ($node['key'] ?? '');
            $type = (string) ($node['type'] ?? '');

            if (! preg_match('/^[a-z][a-z0-9_]{0,31}$/', $key)) {
                throw new BusinessException(ErrorCode::VALIDATION, '流程节点编码不正确', 422);
            }

            if (isset($seen[$key])) {
                throw new BusinessException(ErrorCode::VALIDATION, '流程节点编码不能重复', 422);
            }

            if (! in_array($type, ['start', 'approve', 'cc', 'end'], true)) {
                throw new BusinessException(ErrorCode::VALIDATION, '流程节点类型不正确', 422);
            }

            $seen[$key] = true;
            $assigneeType = (string) ($node['assignee_type'] ?? '');

            if (! in_array($assigneeType, ['', 'user', 'initiator'], true)) {
                throw new BusinessException(ErrorCode::VALIDATION, '审批人类型不正确', 422);
            }

            $sign = (string) ($node['sign'] ?? 'any');

            if (! in_array($sign, ['any', 'all'], true)) {
                throw new BusinessException(ErrorCode::VALIDATION, '审批方式只能是或签或会签', 422);
            }

            $userIds = [];

            foreach ((array) ($node['user_ids'] ?? []) as $userId) {
                $id = (int) $userId;

                if ($id > 0 && ! in_array($id, $userIds, true)) {
                    $userIds[] = $id;
                }
            }

            $normalizedNodes[] = [
                'key' => $key,
                'type' => $type,
                'name' => trim((string) ($node['name'] ?? '')) ?: match ($type) {
                    'start' => '开始',
                    'end' => '结束',
                    'cc' => '抄送',
                    default => '审批',
                },
                'assignee_type' => $type === 'approve' ? ($assigneeType !== '' ? $assigneeType : 'user') : '',
                'user_ids' => in_array($type, ['approve', 'cc'], true) ? $userIds : [],
                'sign' => $type === 'approve' ? $sign : 'any',
            ];
        }

        $normalizedEdges = [];

        foreach (array_values($edges) as $edge) {
            if (! is_array($edge)) {
                throw new BusinessException(ErrorCode::VALIDATION, '流程连线格式不正确', 422);
            }

            $when = [];

            foreach ((array) ($edge['when'] ?? []) as $clause) {
                if (! is_array($clause)) {
                    throw new BusinessException(ErrorCode::VALIDATION, '流程条件格式不正确', 422);
                }

                $op = (string) ($clause['op'] ?? '');

                if (! in_array($op, ['eq', 'neq', 'gt', 'gte', 'lt', 'lte', 'contains'], true)) {
                    throw new BusinessException(ErrorCode::VALIDATION, '流程条件比较方式不正确', 422);
                }

                $field = (string) ($clause['field'] ?? '');

                if ($field === '') {
                    throw new BusinessException(ErrorCode::VALIDATION, '流程条件字段不能为空', 422);
                }

                $when[] = [
                    'field' => $field,
                    'op' => $op,
                    'value' => $clause['value'] ?? '',
                ];
            }

            $normalizedEdges[] = [
                'from' => (string) ($edge['from'] ?? ''),
                'to' => (string) ($edge['to'] ?? ''),
                'priority' => max(1, (int) ($edge['priority'] ?? 1)),
                'when' => $when,
            ];
        }

        return new self(['nodes' => $normalizedNodes, 'edges' => $normalizedEdges]);
    }

    /**
     * @return array{nodes: list<array<string, mixed>>, edges: list<array<string, mixed>>}
     */
    public function toArray(): array
    {
        return $this->schema;
    }

    /**
     * 发布前确认图能走通，并且审批人、抄送人都是当前企业成员
     *
     * @param  callable(int): bool  $active
     */
    public function assertPublishable(callable $active): void
    {
        $nodes = $this->byKey();
        $starts = array_values(array_filter($this->schema['nodes'], fn (array $node): bool => $node['type'] === 'start'));
        $ends = array_values(array_filter($this->schema['nodes'], fn (array $node): bool => $node['type'] === 'end'));
        $approves = array_values(array_filter($this->schema['nodes'], fn (array $node): bool => $node['type'] === 'approve'));

        if (count($starts) !== 1 || count($ends) !== 1) {
            throw new BusinessException(ErrorCode::VALIDATION, '流程必须有且只有一个开始节点和一个结束节点', 422);
        }

        if ($approves === []) {
            throw new BusinessException(ErrorCode::VALIDATION, '流程至少要有一个审批节点', 422);
        }

        foreach ($this->schema['edges'] as $edge) {
            if (! isset($nodes[$edge['from']], $nodes[$edge['to']])) {
                throw new BusinessException(ErrorCode::VALIDATION, '流程连线指向了不存在的节点', 422);
            }

            if ($nodes[$edge['to']]['type'] === 'start') {
                throw new BusinessException(ErrorCode::VALIDATION, '流程不能回到开始节点', 422);
            }
        }

        foreach ($this->schema['nodes'] as $node) {
            if ($node['type'] !== 'end' && $this->outgoing($node['key']) === []) {
                throw new BusinessException(ErrorCode::VALIDATION, '节点「'.$node['name'].'」还没有后继', 422);
            }

            if ($node['type'] === 'approve' && $node['assignee_type'] === 'user' && $node['user_ids'] === []) {
                throw new BusinessException(ErrorCode::VALIDATION, '审批节点「'.$node['name'].'」还没有审批人', 422);
            }

            if ($node['type'] === 'cc' && $node['user_ids'] === []) {
                throw new BusinessException(ErrorCode::VALIDATION, '抄送节点「'.$node['name'].'」还没有抄送人', 422);
            }

            foreach ($node['user_ids'] as $userId) {
                if (! $active($userId)) {
                    throw new BusinessException(ErrorCode::VALIDATION, '节点「'.$node['name'].'」里有不是本企业成员的人员', 422);
                }
            }
        }

        if ($this->reachesEnd($starts[0]['key']) === false) {
            throw new BusinessException(ErrorCode::VALIDATION, '从开始节点走不到结束', 422);
        }

        if ($this->hasCycle($starts[0]['key'])) {
            throw new BusinessException(ErrorCode::VALIDATION, '流程图不能有环', 422);
        }
    }

    /**
     * 按条件选出下一个节点
     *
     * @param  array<string, mixed>  $formData
     * @return array<string, mixed>
     */
    public function next(string $fromKey, array $formData): array
    {
        $edges = $this->outgoing($fromKey);
        usort($edges, fn (array $left, array $right): int => $left['priority'] <=> $right['priority']);
        $default = null;

        foreach ($edges as $edge) {
            if ($edge['when'] === []) {
                $default ??= $edge;

                continue;
            }

            if ($this->matches($edge['when'], $formData)) {
                return $this->node($edge['to']);
            }
        }

        if ($default === null) {
            throw new BusinessException(ErrorCode::VALIDATION, '没有匹配的流程分支', 422);
        }

        return $this->node($default['to']);
    }

    /**
     * @return array<string, mixed>
     */
    public function node(string $key): array
    {
        return $this->byKey()[$key] ?? throw new BusinessException(ErrorCode::VALIDATION, '流程节点不存在', 422);
    }

    /**
     * 解析这一步的处理人
     *
     * @param  array<string, mixed>  $node
     * @return list<int>
     */
    public function assignees(array $node, int $applicantUserId): array
    {
        if ($node['type'] === 'approve' && $node['assignee_type'] === 'initiator') {
            return [$applicantUserId];
        }

        return array_values(array_map('intval', $node['user_ids']));
    }

    /**
     * @param  list<array<string, mixed>>  $when
     * @param  array<string, mixed>  $formData
     */
    private function matches(array $when, array $formData): bool
    {
        foreach ($when as $clause) {
            $left = $formData[$clause['field']] ?? null;
            $right = $clause['value'];

            $ok = match ($clause['op']) {
                'eq' => $this->same($left, $right),
                'neq' => ! $this->same($left, $right),
                'contains' => $left !== null && str_contains((string) $left, (string) $right),
                'gt', 'gte', 'lt', 'lte' => $this->compareNumber($left, $right, $clause['op']),
                default => false,
            };

            if (! $ok) {
                return false;
            }
        }

        return true;
    }

    private function same(mixed $left, mixed $right): bool
    {
        if (is_numeric($left) && is_numeric($right)) {
            return (float) $left === (float) $right;
        }

        return (string) $left === (string) $right;
    }

    private function compareNumber(mixed $left, mixed $right, string $op): bool
    {
        if (! is_numeric($left) || ! is_numeric($right)) {
            return false;
        }

        $left = (float) $left;
        $right = (float) $right;

        return match ($op) {
            'gt' => $left > $right,
            'gte' => $left >= $right,
            'lt' => $left < $right,
            'lte' => $left <= $right,
            default => false,
        };
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function byKey(): array
    {
        $nodes = [];

        foreach ($this->schema['nodes'] as $node) {
            $nodes[$node['key']] = $node;
        }

        return $nodes;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function outgoing(string $fromKey): array
    {
        return array_values(array_filter(
            $this->schema['edges'],
            fn (array $edge): bool => $edge['from'] === $fromKey,
        ));
    }

    private function reachesEnd(string $startKey): bool
    {
        $seen = [];
        $stack = [$startKey];

        while ($stack !== []) {
            $key = array_pop($stack);

            if (isset($seen[$key])) {
                continue;
            }

            $seen[$key] = true;

            if (($this->byKey()[$key]['type'] ?? '') === 'end') {
                return true;
            }

            foreach ($this->outgoing($key) as $edge) {
                $stack[] = $edge['to'];
            }
        }

        return false;
    }

    private function hasCycle(string $startKey): bool
    {
        $color = [];

        $visit = function (string $key) use (&$visit, &$color): bool {
            $color[$key] = 1;

            foreach ($this->outgoing($key) as $edge) {
                $next = $edge['to'];
                $state = $color[$next] ?? 0;

                if ($state === 1 || ($state === 0 && $visit($next))) {
                    return true;
                }
            }

            $color[$key] = 2;

            return false;
        };

        return $visit($startKey);
    }
}
