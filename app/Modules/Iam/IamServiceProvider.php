<?php

/**
 * 身份与访问模块服务注册
 *
 * @package     App\Modules\Iam
 * @license     MIT License (MIT)
 * @author      Sunny Yang <githup@163.com> QQ4873473
 * @copyright   Copyright (c) 2024 YourCompany. All rights reserved.
 * @link        http://www.budff.com
 */

namespace App\Modules\Iam;

use App\Modules\Iam\Application\Contracts\DepartmentDirectory;
use App\Modules\Iam\Application\Contracts\MembershipGateway;
use App\Modules\Iam\Application\Contracts\ModuleGrantSync;
use App\Modules\Iam\Application\Contracts\TenantProvisioner;
use App\Modules\Iam\Application\Provisioning\ProvisionTenantOwner;
use App\Modules\Iam\Infrastructure\Persistence\EloquentDepartmentDirectory;
use App\Modules\Iam\Infrastructure\Persistence\EloquentMembershipGateway;
use App\Modules\Iam\Infrastructure\Persistence\EloquentModuleGrantSync;
use Illuminate\Support\ServiceProvider;

/**
 * 身份与访问模块服务注册
 */
class IamServiceProvider extends ServiceProvider
{
    /**
     * 注册
     */
    public function register(): void
    {
        $this->app->bind(MembershipGateway::class, EloquentMembershipGateway::class);
        $this->app->bind(DepartmentDirectory::class, EloquentDepartmentDirectory::class);
        $this->app->bind(TenantProvisioner::class, ProvisionTenantOwner::class);
        $this->app->bind(ModuleGrantSync::class, EloquentModuleGrantSync::class);
    }

    /**
     * 启动阶段绑定路由、迁移和租户实现
     */
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/Infrastructure/Migrations');
    }
}

