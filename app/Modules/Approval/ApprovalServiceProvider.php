<?php

/**
 * 审批模块服务注册
 *
 * @package     App\Modules\Approval
 * @license     MIT License (MIT)
 * @author      Sunny Yang <githup@163.com> QQ4873473
 * @copyright   Copyright (c) 2024 YourCompany. All rights reserved.
 * @link        http://www.budff.com
 */

namespace App\Modules\Approval;

use App\Modules\Approval\Application\Contracts\ApprovalGateway;
use App\Modules\Approval\Infrastructure\Persistence\EloquentApprovalGateway;
use Illuminate\Support\ServiceProvider;

/**
 * 审批模块服务注册
 */
class ApprovalServiceProvider extends ServiceProvider
{
    /**
     * 注册
     */
    public function register(): void
    {
        $this->app->bind(ApprovalGateway::class, EloquentApprovalGateway::class);
    }

    /**
     * 启动阶段加载本模块迁移
     */
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/Infrastructure/Migrations');
    }
}
