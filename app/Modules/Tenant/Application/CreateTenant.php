<?php

/**
 * 自助创建租户
 *
 * @package     App\Modules\Tenant\Application
 * @license     MIT License (MIT)
 * @author      Sunny Yang <githup@163.com> QQ4873473
 * @copyright   Copyright (c) 2024 YourCompany. All rights reserved.
 * @link        http://www.budff.com
 */

namespace App\Modules\Tenant\Application;

use App\Modules\Iam\Application\Contracts\TenantProvisioner;
use App\Modules\Tenant\Infrastructure\Persistence\Tenant;
use App\Shared\Tenancy\TenantContext;

/**
 * 自助创建租户
 */
class CreateTenant
{
    /**
     * 注入依赖
     */
    public function __construct(
        private readonly TenantProvisioner $provisioner,
        private readonly TenantContext $context,
    ) {}

    /**
     * 处理当前请求
     *
     * @return array{id: int, name: string, slug: string, status: int}
     */
    public function handle(int $userId, string $name, string $slug): array
    {
        $tenant = Tenant::query()->create([
            'name' => $name,
            'slug' => $slug,
            'status' => 1,
            'settings' => [],
        ]);

        $this->context->set((int) $tenant->id, (string) $tenant->slug);

        try {
            $this->provisioner->provisionOwner((int) $tenant->id, $userId);
        } finally {
            // 建租户是跨租户写操作，用完临时上下文后必须清掉。
            $this->context->clear();
        }

        return [
            'id' => (int) $tenant->id,
            'name' => (string) $tenant->name,
            'slug' => (string) $tenant->slug,
            'status' => (int) $tenant->status,
        ];
    }
}

