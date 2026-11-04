<?php

/**
 * 租户模块服务注册
 *
 * @package     App\Modules\Tenant
 * @license     MIT License (MIT)
 * @author      Sunny Yang <githup@163.com> QQ4873473
 * @copyright   Copyright (c) 2024 YourCompany. All rights reserved.
 * @link        http://www.budff.com
 */

namespace App\Modules\Tenant;

use App\Modules\Tenant\Infrastructure\Tenancy\HeaderTenantResolver;
use App\Shared\Tenancy\TenantResolver;
use Illuminate\Support\ServiceProvider;

/**
 * 租户模块服务注册
 */
class TenantServiceProvider extends ServiceProvider
{
    /**
     * 注册
     */
    public function register(): void
    {
        $this->app->bind(TenantResolver::class, HeaderTenantResolver::class);
    }

    /**
     * 启动阶段绑定路由、迁移和租户实现
     */
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/Infrastructure/Migrations');
    }
}

