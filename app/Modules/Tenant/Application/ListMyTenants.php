<?php

/**
 * 列出当前用户加入的租户
 *
 * @package     App\Modules\Tenant\Application
 * @license     MIT License (MIT)
 * @author      Sunny Yang <githup@163.com> QQ4873473
 * @copyright   Copyright (c) 2024 YourCompany. All rights reserved.
 * @link        http://www.budff.com
 */

namespace App\Modules\Tenant\Application;

use App\Modules\Iam\Application\Contracts\MembershipGateway;
use App\Modules\Tenant\Infrastructure\Persistence\Tenant;

/**
 * 列出当前用户加入的租户
 */
class ListMyTenants
{
    /**
     * 注入依赖
     */
    public function __construct(private readonly MembershipGateway $members) {}

    /**
     * 处理当前请求
     *
     * @return list<array<string, mixed>>
     */
    public function handle(int $userId): array
    {
        $memberships = collect($this->members->membershipsForUser($userId))->keyBy('tenant_id');

        if ($memberships->isEmpty()) {
            return [];
        }

        return Tenant::query()
            ->whereIn('id', $memberships->keys())
            ->where('status', 1)
            ->orderBy('id')
            ->get()
            ->map(function (Tenant $tenant) use ($memberships): array {
                $membership = $memberships->get($tenant->id);

                return [
                    'id' => (int) $tenant->id,
                    'name' => $tenant->name,
                    'slug' => $tenant->slug,
                    'status' => (int) $tenant->status,
                    'is_owner' => (bool) ($membership['is_owner'] ?? false),
                ];
            })
            ->all();
    }
}

