<?php

/**
 * 应用服务注册
 *
 * @package     App\Providers
 * @license     MIT License (MIT)
 * @author      Sunny Yang <githup@163.com> QQ4873473
 * @copyright   Copyright (c) 2024 YourCompany. All rights reserved.
 * @link        http://www.budff.com
 */

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

/**
 * 应用服务注册
 */
class AppServiceProvider extends ServiceProvider
{
    /**
     * 注册
     */
    public function register(): void
    {
        //
    }

    /**
     * 启动阶段绑定路由、迁移和租户实现
     */
    public function boot(): void
    {
        Gate::define('viewApiDocs', function (mixed $user = null): bool {
            return app()->environment(['local', 'testing']);
        });
    }
}

