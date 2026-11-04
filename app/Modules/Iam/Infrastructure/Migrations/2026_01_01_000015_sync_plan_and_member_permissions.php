<?php

/**
 * 补齐成员维护和套餐权限并授予管理员
 *
 * @package     app\Modules\Iam\Infrastructure\Migrations
 * @license     MIT License (MIT)
 * @author      Sunny Yang <githup@163.com> QQ4873473
 * @copyright   Copyright (c) 2024 YourCompany. All rights reserved.
 * @link        http://www.budff.com
 */

use App\Modules\Iam\Infrastructure\Catalog\IamCatalog;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * 创建或变更表结构
     */
    public function up(): void
    {
        $ids = [];
        $now = now();

        foreach (IamCatalog::permissions() as $permission) {
            $existing = DB::table('permissions')->where('code', $permission['code'])->whereNull('deleted_at')->value('id');

            if ($existing) {
                $ids[$permission['code']] = (int) $existing;

                continue;
            }

            $parentId = $permission['parent'] === null ? null : ($ids[$permission['parent']] ?? null);

            $ids[$permission['code']] = (int) DB::table('permissions')->insertGetId([
                'parent_id' => $parentId,
                'type' => $permission['type'],
                'code' => $permission['code'],
                'name' => $permission['name'],
                'route' => $permission['route'],
                'method' => $permission['method'],
                'icon' => $permission['icon'],
                'sort' => $permission['sort'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $adminRoles = DB::table('roles')->where('code', 'admin')->whereNull('deleted_at')->get();

        foreach ($adminRoles as $role) {
            foreach ($ids as $permissionId) {
                $exists = DB::table('role_permissions')
                    ->where('role_id', $role->id)
                    ->where('permission_id', $permissionId)
                    ->exists();

                if ($exists) {
                    continue;
                }

                DB::table('role_permissions')->insert([
                    'tenant_id' => $role->tenant_id,
                    'role_id' => $role->id,
                    'permission_id' => $permissionId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    /**
     * 回滚本次迁移
     */
    public function down(): void
    {
        DB::table('permissions')->whereIn('code', [
            'iam.plans.menu',
            'iam.plans.show',
            'iam.plans.update',
            'iam.members.create',
            'iam.members.store',
            'iam.members.destroy',
        ])->delete();
    }
};
