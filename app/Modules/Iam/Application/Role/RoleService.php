<?php

/**
 * 角色、权限与数据范围维护
 *
 * @package     App\Modules\Iam\Application\Role
 * @license     MIT License (MIT)
 * @author      Sunny Yang <githup@163.com> QQ4873473
 * @copyright   Copyright (c) 2024 YourCompany. All rights reserved.
 * @link        http://www.budff.com
 */

namespace App\Modules\Iam\Application\Role;

use App\Modules\Iam\Domain\DataScope;
use App\Modules\Iam\Infrastructure\Persistence\Department;
use App\Modules\Iam\Infrastructure\Persistence\Permission;
use App\Modules\Iam\Infrastructure\Persistence\Role;
use App\Modules\Iam\Infrastructure\Persistence\RoleDepartment;
use App\Modules\Iam\Infrastructure\Persistence\RolePermission;
use App\Modules\Iam\Infrastructure\Persistence\TenantUserRole;
use App\Shared\Exceptions\BusinessException;
use App\Shared\Exceptions\ErrorCode;

/**
 * 角色、权限与数据范围维护
 */
class RoleService
{
    /**
     * @return list<array<string, mixed>>
     */
    public function list(): array
    {
        return Role::query()->orderBy('id')->get()->map(fn (Role $role): array => $this->present($role))->all();
    }

    /**
     * 创建
     *
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    public function create(array $input): array
    {
        unset($input['tenant_id']);
        $this->assertScope((string) $input['data_scope']);

        $role = Role::query()->create([
            'code' => $input['code'],
            'name' => $input['name'],
            'data_scope' => $input['data_scope'],
        ]);

        $this->syncPermissions($role, $input['permission_ids'] ?? []);
        $this->syncDepartments($role, $input['department_ids'] ?? []);

        return $this->present($role);
    }

    /**
     * 查看
     *
     * @return array<string, mixed>
     */
    public function show(int $id): array
    {
        return $this->present($this->find($id));
    }

    /**
     * 更新
     *
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    public function update(int $id, array $input): array
    {
        unset($input['tenant_id']);
        $role = $this->find($id);

        if (isset($input['data_scope'])) {
            $this->assertScope((string) $input['data_scope']);
            $role->data_scope = $input['data_scope'];
        }

        if (isset($input['name'])) {
            $role->name = $input['name'];
        }

        if (isset($input['code'])) {
            $role->code = $input['code'];
        }

        $role->save();

        if (array_key_exists('permission_ids', $input)) {
            $this->syncPermissions($role, $input['permission_ids'] ?? []);
        }

        if (array_key_exists('department_ids', $input)) {
            $this->syncDepartments($role, $input['department_ids'] ?? []);
        }

        return $this->present($role);
    }

    /**
     * 删除
     */
    public function delete(int $id): void
    {
        $role = $this->find($id);

        if (TenantUserRole::query()->where('role_id', $role->id)->exists()) {
            throw new BusinessException(ErrorCode::VALIDATION, '角色仍分配给成员', 422);
        }

        RolePermission::query()->where('role_id', $role->id)->forceDelete();
        RoleDepartment::query()->where('role_id', $role->id)->forceDelete();
        $role->delete();
    }

    /**
     * 按主键查找当前租户内的记录
     */
    private function find(int $id): Role
    {
        $role = Role::query()->find($id);

        if ($role === null) {
            throw new BusinessException(ErrorCode::NOT_FOUND, '角色不存在', 404);
        }

        return $role;
    }

    /**
     * 校验数据范围取值
     */
    private function assertScope(string $scope): void
    {
        if (! in_array($scope, DataScope::all(), true)) {
            throw new BusinessException(ErrorCode::VALIDATION, '数据范围无效', 422);
        }
    }

    /**
     * 按权限主键重建角色授权
     *
     * @param  list<int>  $permissionIds
     */
    private function syncPermissions(Role $role, array $permissionIds): void
    {
        $permissionIds = array_values(array_unique(array_map('intval', $permissionIds)));
        $found = Permission::query()->whereIn('id', $permissionIds)->pluck('id')->map(fn ($id): int => (int) $id)->all();

        if (count($found) !== count($permissionIds)) {
            throw new BusinessException(ErrorCode::VALIDATION, '权限不存在', 422);
        }

        RolePermission::query()->where('role_id', $role->id)->forceDelete();

        foreach ($found as $permissionId) {
            RolePermission::query()->create([
                'role_id' => $role->id,
                'permission_id' => $permissionId,
            ]);
        }
    }

    /**
     * 按部门主键重建自定义数据范围
     *
     * @param  list<int>  $departmentIds
     */
    private function syncDepartments(Role $role, array $departmentIds): void
    {
        $departmentIds = array_values(array_unique(array_map('intval', $departmentIds)));
        $found = Department::query()->whereIn('id', $departmentIds)->pluck('id')->map(fn ($id): int => (int) $id)->all();

        if (count($found) !== count($departmentIds)) {
            throw new BusinessException(ErrorCode::VALIDATION, '部门不存在或不属于当前租户', 422);
        }

        RoleDepartment::query()->where('role_id', $role->id)->forceDelete();

        foreach ($found as $departmentId) {
            RoleDepartment::query()->create([
                'role_id' => $role->id,
                'department_id' => $departmentId,
            ]);
        }
    }

    /**
     * 把模型整理成接口数据
     *
     * @return array<string, mixed>
     */
    private function present(Role $role): array
    {
        $role->loadMissing(['permissions:id,code,name', 'departments:id,name']);

        return [
            'id' => (int) $role->id,
            'tenant_id' => (int) $role->tenant_id,
            'code' => $role->code,
            'name' => $role->name,
            'data_scope' => $role->data_scope,
            'permission_ids' => $role->permissions->pluck('id')->map(fn ($id): int => (int) $id)->all(),
            'department_ids' => $role->departments->pluck('id')->map(fn ($id): int => (int) $id)->all(),
        ];
    }
}

