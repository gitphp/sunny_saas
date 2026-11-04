<?php

/**
 * 租户成员维护
 *
 * @package     App\Modules\Iam\Application\Member
 * @license     MIT License (MIT)
 * @author      Sunny Yang <githup@163.com> QQ4873473
 * @copyright   Copyright (c) 2024 YourCompany. All rights reserved.
 * @link        http://www.budff.com
 */

namespace App\Modules\Iam\Application\Member;

use App\Modules\Iam\Application\Access\DataScopeResolver;
use App\Modules\Iam\Infrastructure\Persistence\Department;
use App\Modules\Iam\Infrastructure\Persistence\Role;
use App\Modules\Iam\Infrastructure\Persistence\TenantUser;
use App\Modules\Iam\Infrastructure\Persistence\TenantUserRole;
use App\Shared\Exceptions\BusinessException;
use App\Shared\Exceptions\ErrorCode;

/**
 * 租户成员维护
 */
class MemberService
{
    /**
     * 注入依赖
     */
    public function __construct(private readonly DataScopeResolver $scopes) {}

    /**
     * @return list<array<string, mixed>>
     */
    public function list(int $userId): array
    {
        $query = TenantUser::query()->with(['user', 'roles'])->orderBy('id');
        $this->scopes->applyToMembers($query, $userId);

        return $query->get()->map(fn (TenantUser $member): array => $this->present($member))->all();
    }

    /**
     * 重建成员的角色分配
     *
     * @param  list<int>  $roleIds
     * @return array<string, mixed>
     */
    public function syncRoles(int $memberId, int $actorUserId, array $roleIds): array
    {
        $member = $this->findVisible($memberId, $actorUserId);

        $roleIds = array_values(array_unique(array_map('intval', $roleIds)));
        $roles = Role::query()->whereIn('id', $roleIds)->get();

        if ($roles->count() !== count($roleIds)) {
            throw new BusinessException(ErrorCode::VALIDATION, '角色不存在或不属于当前租户', 422);
        }

        TenantUserRole::query()->where('tenant_user_id', $member->id)->forceDelete();

        foreach ($roles as $role) {
            TenantUserRole::query()->create([
                'tenant_user_id' => $member->id,
                'role_id' => $role->id,
            ]);
        }

        return $this->present($member->refresh()->load('user', 'roles'));
    }

    /**
     * 更新
     *
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    public function update(int $memberId, int $actorUserId, array $input): array
    {
        $member = $this->findVisible($memberId, $actorUserId);
        $departmentId = $input['department_id'] ?? null;

        if ($departmentId) {
            $department = Department::query()->find((int) $departmentId);

            if ($department === null || ! $this->scopes->departmentVisible((int) $department->id, $actorUserId)) {
                throw new BusinessException(ErrorCode::VALIDATION, '部门不存在或不属于当前租户', 422);
            }

            $member->department_id = (int) $department->id;
        } else {
            $member->department_id = null;
        }

        $member->save();

        return $this->present($member->refresh()->load('user', 'roles'));
    }

    /**
     * 按数据范围查找可见记录，范围外视为不存在
     */
    private function findVisible(int $memberId, int $actorUserId): TenantUser
    {
        $query = TenantUser::query()->whereKey($memberId);
        $this->scopes->applyToMembers($query, $actorUserId);
        $member = $query->first();

        if ($member === null) {
            throw new BusinessException(ErrorCode::NOT_FOUND, '成员不存在', 404);
        }

        return $member;
    }

    /**
     * 把模型整理成接口数据
     *
     * @return array<string, mixed>
     */
    private function present(TenantUser $member): array
    {
        return [
            'id' => (int) $member->id,
            'user_id' => (int) $member->user_id,
            'name' => $member->user?->name ?? '',
            'email' => $member->user?->email ?? '',
            'department_id' => $member->department_id ? (int) $member->department_id : null,
            'status' => (int) $member->status,
            'is_owner' => (int) $member->is_owner,
            'roles' => $member->roles->map(fn (Role $role): array => [
                'id' => (int) $role->id,
                'code' => $role->code,
                'name' => $role->name,
                'data_scope' => $role->data_scope,
            ])->values()->all(),
        ];
    }
}

