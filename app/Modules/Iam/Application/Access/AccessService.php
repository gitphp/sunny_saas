<?php

/**
 * 租户内权限判断
 *
 * @package     App\Modules\Iam\Application\Access
 * @license     MIT License (MIT)
 * @author      Sunny Yang <githup@163.com> QQ4873473
 * @copyright   Copyright (c) 2024 YourCompany. All rights reserved.
 * @link        http://www.budff.com
 */

namespace App\Modules\Iam\Application\Access;

use App\Modules\Iam\Infrastructure\Persistence\Permission;
use App\Modules\Iam\Infrastructure\Persistence\RolePermission;
use App\Modules\Iam\Infrastructure\Persistence\TenantModule;
use App\Modules\Iam\Infrastructure\Persistence\TenantUser;
use App\Modules\Iam\Infrastructure\Persistence\TenantUserRole;

/**
 * 租户内权限判断
 */
class AccessService
{
    /**
     * 判断当前租户是否开通了该模块
     */
    public function isModuleEnabled(string $moduleCode): bool
    {
        return TenantModule::query()
            ->where('module_code', $moduleCode)
            ->where('status', 1)
            ->exists();
    }

    /**
     * 判断当前用户是不是租户所有者
     */
    public function isOwner(int $userId): bool
    {
        $member = $this->member($userId);

        return $member !== null && (int) $member->is_owner === 1;
    }

    /**
     * 判断当前用户是否拥有指定权限码
     */
    public function hasPermission(int $userId, string $code): bool
    {
        // 所有者绕过权限码，但不绕过租户边界和模块开通。
        if ($this->isOwner($userId)) {
            return true;
        }

        $member = $this->member($userId);

        if ($member === null) {
            return false;
        }

        $permissionId = Permission::query()->where('code', $code)->value('id');

        if ($permissionId === null) {
            return false;
        }

        $roleIds = TenantUserRole::query()
            ->where('tenant_user_id', $member->id)
            ->pluck('role_id');

        if ($roleIds->isEmpty()) {
            return false;
        }

        return RolePermission::query()
            ->whereIn('role_id', $roleIds)
            ->where('permission_id', $permissionId)
            ->exists();
    }

    /**
     * 返回当前用户在本租户的权限码
     *
     * @return list<string>
     */
    public function permissionCodes(int $userId): array
    {
        if ($this->isOwner($userId)) {
            return Permission::query()->orderBy('sort')->orderBy('id')->pluck('code')->all();
        }

        $member = $this->member($userId);

        if ($member === null) {
            return [];
        }

        $roleIds = TenantUserRole::query()->where('tenant_user_id', $member->id)->pluck('role_id');

        if ($roleIds->isEmpty()) {
            return [];
        }

        $permissionIds = RolePermission::query()->whereIn('role_id', $roleIds)->pluck('permission_id');

        return Permission::query()
            ->whereIn('id', $permissionIds)
            ->orderBy('sort')
            ->orderBy('id')
            ->pluck('code')
            ->all();
    }

    /**
     * 读取当前用户的有效成员身份
     */
    private function member(int $userId): ?TenantUser
    {
        return TenantUser::query()->where('user_id', $userId)->where('status', 1)->first();
    }
}

