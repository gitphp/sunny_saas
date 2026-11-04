<?php

/**
 * 人事模块服务注册
 *
 * @package     App\Modules\Hr
 * @license     MIT License (MIT)
 * @author      Sunny Yang <githup@163.com> QQ4873473
 * @copyright   Copyright (c) 2024 YourCompany. All rights reserved.
 * @link        http://www.budff.com
 */

namespace App\Modules\Hr;

use Illuminate\Support\ServiceProvider;

/**
 * 人事模块服务注册
 */
class HrServiceProvider extends ServiceProvider
{
    /**
     * 启动阶段加载本模块迁移
     */
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/Infrastructure/Migrations');
    }
}
