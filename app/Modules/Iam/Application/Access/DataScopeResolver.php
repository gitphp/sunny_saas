<?php

/**
 * 部门数据范围计算
 *
 * @package     App\Modules\Iam\Application\Access
 * @license     MIT License (MIT)
 * @author      Sunny Yang <githup@163.com> QQ4873473
 * @copyright   Copyright (c) 2024 YourCompany. All rights reserved.
 * @link        http://www.budff.com
 */

namespace App\Modules\Iam\Application\Access;

use App\Modules\Iam\Domain\DataScope;
use App\Modules\Iam\Infrastructure\Persistence\Department;
use App\Modules\Iam\Infrastructure\Persistence\Role;
use App\Modules\Iam\Infrastructure\Persistence\RoleDepartment;
use App\Modules\Iam\Infrastructure\Persistence\TenantUser;
use App\Modules\Iam\Infrastructure\Persistence\TenantUserRole;
use Illuminate\Database\Eloquent\Builder;

/**
 * 部门数据范围计算
 */
class DataScopeResolver
{
    /**
     * 计算用户合并后的数据范围
     *
     * @return array{unrestricted: bool, department_ids: list<int>, self_user_id: int|null}
     */
    public function forUser(int $userId): array
    {
        $member = TenantUser::query()->where('user_id', $userId)->where('status', 1)->first();

        if ($member === null) {
            return ['unrestricted' => false, 'department_ids' => [], 'self_user_id' => $userId];
        }

        if ((int) $member->is_owner === 1) {
            return ['unrestricted' => true, 'department_ids' => [], 'self_user_id' => null];
        }

        $roleIds = TenantUserRole::query()->where('tenant_user_id', $member->id)->pluck('role_id');
        $roles = Role::query()->whereIn('id', $roleIds)->get();

        if ($roles->contains(fn (Role $role): bool => $role->data_scope === DataScope::ALL)) {
            return ['unrestricted' => true, 'department_ids' => [], 'self_user_id' => null];
        }

        $departmentIds = [];
        $includeSelf = $roles->isEmpty();

        foreach ($roles as $role) {
            if ($role->data_scope === DataScope::DEPARTMENT && $member->department_id) {
                $departmentIds[] = (int) $member->department_id;
            } elseif ($role->data_scope === DataScope::DEPARTMENT_AND_CHILDREN && $member->department_id) {
                $departmentIds = array_merge($departmentIds, $this->subtreeIds((int) $member->department_id));
            } elseif ($role->data_scope === DataScope::CUSTOM) {
                $departmentIds = array_merge(
                    $departmentIds,
                    RoleDepartment::query()->where('role_id', $role->id)->pluck('department_id')->map(fn ($id): int => (int) $id)->all()
                );
            } else {
                $includeSelf = true;
            }
        }

        return [
            'unrestricted' => false,
            'department_ids' => array_values(array_unique($departmentIds)),
            'self_user_id' => $includeSelf ? $userId : null,
        ];
    }

    /**
     * 把数据范围套到成员查询上
     */
    public function applyToMembers(Builder $query, int $userId): void
    {
        $scope = $this->forUser($userId);

        if ($scope['unrestricted']) {
            return;
        }

        $query->where(function (Builder $inner) use ($scope): void {
            $applied = false;

            if ($scope['department_ids'] !== []) {
                $inner->whereIn('department_id', $scope['department_ids']);
                $applied = true;
            }

            if ($scope['self_user_id'] !== null) {
                $applied
                    ? $inner->orWhere('user_id', $scope['self_user_id'])
                    : $inner->where('user_id', $scope['self_user_id']);
                $applied = true;
            }

            // 范围算出来是空时，查询必须零行，不能放开成全表。
            if (! $applied) {
                $inner->whereRaw('1 = 0');
            }
        });
    }

    /**
     * 把数据范围套到部门查询上
     */
    public function applyToDepartments(Builder $query, int $userId): void
    {
        $scope = $this->forUser($userId);

        if ($scope['unrestricted']) {
            return;
        }

        $ids = $scope['department_ids'];

        if ($scope['self_user_id'] !== null) {
            $member = TenantUser::query()->where('user_id', $scope['self_user_id'])->first();

            if ($member?->department_id) {
                $ids[] = (int) $member->department_id;
            }
        }

        $ids = array_values(array_unique($ids));

        if ($ids === []) {
            // 没有任何可见部门时返回空集。
            $query->whereRaw('1 = 0');

            return;
        }

        $query->whereIn('id', $ids);
    }

    /**
     * 判断部门是否落在当前用户的数据范围内
     */
    public function departmentVisible(int $departmentId, int $userId): bool
    {
        $scope = $this->forUser($userId);

        if ($scope['unrestricted']) {
            return true;
        }

        if (in_array($departmentId, $scope['department_ids'], true)) {
            return true;
        }

        if ($scope['self_user_id'] !== null) {
            $member = TenantUser::query()->where('user_id', $scope['self_user_id'])->first();

            return $member !== null && (int) $member->department_id === $departmentId;
        }

        return false;
    }

    /**
     * 取出部门自身及其全部下级主键
     *
     * @return list<int>
     */
    private function subtreeIds(int $departmentId): array
    {
        $department = Department::query()->find($departmentId);

        if ($department === null || $department->path === '') {
            return [$departmentId];
        }

        return Department::query()
            ->where(function (Builder $query) use ($department): void {
                $query->where('id', $department->id)
                    ->orWhere('path', 'like', $department->path.'/%');
            })
            ->pluck('id')
            ->map(fn ($id): int => (int) $id)
            ->all();
    }
}

