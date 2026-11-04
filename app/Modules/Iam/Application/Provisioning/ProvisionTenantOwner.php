<?php

/**
 * 开通租户所有者与管理员角色
 *
 * @package     App\Modules\Iam\Application\Provisioning
 * @license     MIT License (MIT)
 * @author      Sunny Yang <githup@163.com> QQ4873473
 * @copyright   Copyright (c) 2024 YourCompany. All rights reserved.
 * @link        http://www.budff.com
 */

namespace App\Modules\Iam\Application\Provisioning;

use App\Modules\Iam\Application\Contracts\TenantProvisioner;
use App\Modules\Iam\Domain\DataScope;
use App\Modules\Iam\Infrastructure\Persistence\Permission;
use App\Modules\Iam\Infrastructure\Persistence\Role;
use App\Modules\Iam\Infrastructure\Persistence\RolePermission;
use App\Modules\Iam\Infrastructure\Persistence\TenantModule;
use App\Modules\Iam\Infrastructure\Persistence\TenantUser;
use App\Modules\Iam\Infrastructure\Persistence\TenantUserRole;
use App\Shared\Tenancy\TenantContext;

/**
 * 开通租户所有者与管理员角色
 */
class ProvisionTenantOwner implements TenantProvisioner
{
    /**
     * 注入依赖
     */
    public function __construct(private readonly TenantContext $context) {}

    /**
     * 把创建者设为所有者并授予管理员角色
     */
    public function provisionOwner(int $tenantId, int $userId): void
    {
        $previousId = $this->context->id();
        $previousSlug = $this->context->slug();
        $this->context->set($tenantId, $previousId === $tenantId ? $previousSlug : '');

        try {
            $role = Role::query()->firstOrCreate(
                ['code' => 'admin'],
                ['name' => '管理员', 'data_scope' => DataScope::ALL],
            );

            foreach (Permission::query()->pluck('id') as $permissionId) {
                RolePermission::query()->firstOrCreate([
                    'role_id' => $role->id,
                    'permission_id' => $permissionId,
                ]);
            }

            $member = TenantUser::query()->firstOrCreate(
                ['user_id' => $userId],
                [
                    'department_id' => null,
                    'status' => 1,
                    'is_owner' => 1,
                    'joined_at' => now(),
                ],
            );

            if ((int) $member->is_owner !== 1 || (int) $member->status !== 1) {
                $member->is_owner = 1;
                $member->status = 1;
                $member->save();
            }

            TenantUserRole::query()->firstOrCreate([
                'tenant_user_id' => $member->id,
                'role_id' => $role->id,
            ]);

            TenantModule::query()->updateOrCreate(
                ['module_code' => 'iam'],
                ['status' => 1, 'enabled_at' => now()],
            );
        } finally {
            // 开通逻辑临时切换租户，结束后恢复调用方原来的上下文。
            if ($previousId === null) {
                $this->context->clear();
            } else {
                $this->context->set($previousId, $previousSlug);
            }
        }
    }
}

