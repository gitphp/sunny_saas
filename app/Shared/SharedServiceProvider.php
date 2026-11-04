<?php

/**
 * 共享内核服务注册
 *
 * @package     App\Shared
 * @license     MIT License (MIT)
 * @author      Sunny Yang <githup@163.com> QQ4873473
 * @copyright   Copyright (c) 2024 YourCompany. All rights reserved.
 * @link        http://www.budff.com
 */

namespace App\Shared;

use App\Shared\Tenancy\TenantContext;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\ServiceProvider;

/**
 * 共享内核服务注册
 */
class SharedServiceProvider extends ServiceProvider
{
    /**
     * 注册
     */
    public function register(): void
    {
        $this->app->singleton(TenantContext::class);
    }

    /**
     * 启动阶段绑定路由、迁移和租户实现
     */
    public function boot(): void
    {
        Queue::createPayloadUsing(null);
        Queue::createPayloadUsing(function (): array {
            return [
                // 入队时记下租户，worker 取出后再恢复，没有租户时记 0。
                'tenant_id' => app(TenantContext::class)->id() ?? 0,
            ];
        });
    }
}

