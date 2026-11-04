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
use App\Modules\Iam\Infrastructure\Persistence\User;
use App\Modules\Tenant\Application\Contracts\TenantPlanQuery;
use App\Shared\Exceptions\BusinessException;
use App\Shared\Exceptions\ErrorCode;
use App\Shared\Tenancy\TenantContext;

/**
 * 租户成员维护
 */
class MemberService
{
    /**
     * 注入依赖
     */
    public function __construct(
        private readonly DataScopeResolver $scopes,
        private readonly TenantPlanQuery $plans,
        private readonly TenantContext $context,
    ) {}

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
     * 把已有账号加入当前企业；邮箱不存在时创建全局账号
     *
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    public function store(int $actorUserId, array $input): array
    {
        $tenantId = (int) $this->context->id();
        $plan = $this->plans->forTenant($tenantId);
        $activeMembers = TenantUser::query()->where('status', 1)->count();

        // 上限按当前租户的有效成员计算，满员后不能再加入。
        if ((int) $plan['member_limit'] > 0 && $activeMembers >= (int) $plan['member_limit']) {
            throw new BusinessException(ErrorCode::VALIDATION, '已达到套餐成员上限', 422);
        }

        $email = strtolower(trim((string) $input['email']));
        $user = User::query()->where('email', $email)->first();

        // 邮箱已有全局账号时只加入本企业，不改对方的姓名和密码。
        $password = $input['password'] ?? null;

        if ($user === null) {
            if (! is_string($password) || $password === '') {
                throw new BusinessException(ErrorCode::VALIDATION, '新账号必须设置密码', 422);
            }

            $user = User::query()->create([
                'name' => (string) $input['name'],
                'email' => $email,
                'password' => $password,
                'status' => 1,
            ]);
        } elseif ((int) $user->status !== 1) {
            throw new BusinessException(ErrorCode::VALIDATION, '该账号已停用', 422);
        }

        $member = TenantUser::withTrashed()->where('user_id', $user->id)->first();

        if ($member !== null && ! $member->trashed()) {
            throw new BusinessException(ErrorCode::VALIDATION, '该用户已是当前企业成员', 422);
        }

        $departmentId = $this->visibleDepartmentId($input['department_id'] ?? null, $actorUserId);

        if ($member === null) {
            $member = TenantUser::query()->create([
                'user_id' => $user->id,
                'department_id' => $departmentId,
                'status' => 1,
                'is_owner' => 0,
                'joined_at' => now(),
            ]);
        } else {
            $member->restore();
            $member->fill([
                'department_id' => $departmentId,
                'status' => 1,
                'is_owner' => 0,
                'joined_at' => now(),
            ]);
            $member->save();
        }

        $this->assignRoles($member, $input['role_ids'] ?? []);

        return $this->present($member->refresh()->load('user', 'roles'));
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
        $this->assignRoles($member, $roleIds);

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
        $member->department_id = $this->visibleDepartmentId($input['department_id'] ?? null, $actorUserId);

        if (array_key_exists('status', $input)) {
            $status = (int) $input['status'] === 1 ? 1 : 0;

            if ((int) $member->is_owner === 1 && $status !== 1) {
                throw new BusinessException(ErrorCode::VALIDATION, '不能停用企业所有者', 422);
            }

            $member->status = $status;
        }

        $member->save();

        return $this->present($member->refresh()->load('user', 'roles'));
    }

    /**
     * 移除企业成员。所有者不能移除。
     */
    public function destroy(int $memberId, int $actorUserId): void
    {
        $member = $this->findVisible($memberId, $actorUserId);

        if ((int) $member->is_owner === 1) {
            throw new BusinessException(ErrorCode::VALIDATION, '不能移除企业所有者', 422);
        }

        TenantUserRole::query()->where('tenant_user_id', $member->id)->forceDelete();
        $member->delete();
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
     * 部门必须能被当前操作者看见，避免把成员挂到别的企业或范围外的部门
     */
    private function visibleDepartmentId(mixed $departmentId, int $actorUserId): ?int
    {
        if (! $departmentId) {
            return null;
        }

        $department = Department::query()->find((int) $departmentId);

        if ($department === null || ! $this->scopes->departmentVisible((int) $department->id, $actorUserId)) {
            throw new BusinessException(ErrorCode::VALIDATION, '部门不存在或不属于当前租户', 422);
        }

        return (int) $department->id;
    }

    /**
     * 角色必须属于当前租户，作用域外的主键直接拒绝
     *
     * @param  list<int>  $roleIds
     */
    private function assignRoles(TenantUser $member, array $roleIds): void
    {
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

