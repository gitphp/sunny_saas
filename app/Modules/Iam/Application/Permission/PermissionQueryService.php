<?php

/**
 * 全局权限目录查询
 *
 * @package     App\Modules\Iam\Application\Permission
 * @license     MIT License (MIT)
 * @author      Sunny Yang <githup@163.com> QQ4873473
 * @copyright   Copyright (c) 2024 YourCompany. All rights reserved.
 * @link        http://www.budff.com
 */

namespace App\Modules\Iam\Application\Permission;

use App\Modules\Iam\Infrastructure\Persistence\Permission;

/**
 * 全局权限目录查询
 */
class PermissionQueryService
{
    /**
     * @return list<array<string, mixed>>
     */
    public function list(): array
    {
        return Permission::query()
            ->orderBy('sort')
            ->orderBy('id')
            ->get()
            ->map(fn (Permission $permission): array => [
                'id' => (int) $permission->id,
                'parent_id' => $permission->parent_id ? (int) $permission->parent_id : null,
                'type' => $permission->type,
                'code' => $permission->code,
                'name' => $permission->name,
                'route' => $permission->route,
                'method' => $permission->method,
                'sort' => (int) $permission->sort,
            ])
            ->all();
    }
}

