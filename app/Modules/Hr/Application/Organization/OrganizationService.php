<?php

/**
 * 企业组织架构
 *
 * @package     App\Modules\Hr\Application\Organization
 * @license     MIT License (MIT)
 * @author      Sunny Yang <githup@163.com> QQ4873473
 * @copyright   Copyright (c) 2024 YourCompany. All rights reserved.
 * @link        http://www.budff.com
 */

namespace App\Modules\Hr\Application\Organization;

use App\Modules\Hr\Application\Support\HrVisibility;
use App\Modules\Hr\Domain\EmployeeStatus;
use App\Modules\Hr\Infrastructure\Persistence\Employee;
use App\Modules\Hr\Infrastructure\Persistence\Position;
use App\Modules\Iam\Application\Contracts\DepartmentDirectory;

/**
 * 企业组织架构
 */
class OrganizationService
{
    /**
     * 注入依赖
     */
    public function __construct(
        private readonly DepartmentDirectory $departments,
        private readonly HrVisibility $visibility,
    ) {}

    /**
     * 把可见部门、岗位和在册人数组装成树
     *
     * @return array{departments: list<array<string, mixed>>, unassigned_positions: list<array<string, mixed>>}
     */
    public function tree(int $userId): array
    {
        $departments = $this->departments->listVisible($userId);
        $nodes = [];

        foreach ($departments as $department) {
            $nodes[$department['id']] = [
                'id' => $department['id'],
                'parent_id' => $department['parent_id'],
                'name' => $department['name'],
                'employee_count' => 0,
                'positions' => [],
                'children' => [],
            ];
        }

        $query = Employee::query()->where('status', '!=', EmployeeStatus::LEFT);
        $this->visibility->limitEmployees($query, $userId);

        foreach ($query->get(['department_id']) as $employee) {
            $departmentId = (int) ($employee->department_id ?? 0);

            if (isset($nodes[$departmentId])) {
                $nodes[$departmentId]['employee_count']++;
            }
        }

        $unassigned = [];

        foreach (Position::query()->where('status', 1)->orderBy('sort')->orderBy('id')->get() as $position) {
            $item = [
                'id' => (int) $position->id,
                'name' => (string) $position->name,
                'code' => (string) $position->code,
            ];
            $departmentId = $position->department_id ? (int) $position->department_id : 0;

            if ($departmentId > 0 && isset($nodes[$departmentId])) {
                $nodes[$departmentId]['positions'][] = $item;
            } elseif ($departmentId === 0) {
                $unassigned[] = $item;
            }
        }

        $childrenOf = [];

        foreach ($nodes as $id => $node) {
            $parent = ($node['parent_id'] && isset($nodes[$node['parent_id']])) ? (int) $node['parent_id'] : 0;
            $childrenOf[$parent][] = $id;
        }

        return [
            'departments' => $this->nest($childrenOf, $nodes, 0),
            'unassigned_positions' => $unassigned,
        ];
    }

    /**
     * @param  array<int, list<int>>  $childrenOf
     * @param  array<int, array<string, mixed>>  $nodes
     * @return list<array<string, mixed>>
     */
    private function nest(array $childrenOf, array $nodes, int $parentId): array
    {
        $list = [];

        foreach ($childrenOf[$parentId] ?? [] as $id) {
            $node = $nodes[$id];
            $node['children'] = $this->nest($childrenOf, $nodes, $id);
            $list[] = $node;
        }

        return $list;
    }
}
