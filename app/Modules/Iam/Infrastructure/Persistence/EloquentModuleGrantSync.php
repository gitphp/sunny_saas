<?php

/**
 * 按套餐关闭多余的模块开通
 *
 * @package     App\Modules\Iam\Infrastructure\Persistence
 * @license     MIT License (MIT)
 * @author      Sunny Yang <githup@163.com> QQ4873473
 * @copyright   Copyright (c) 2024 YourCompany. All rights reserved.
 * @link        http://www.budff.com
 */

namespace App\Modules\Iam\Infrastructure\Persistence;

use App\Modules\Iam\Application\Contracts\ModuleGrantSync;

/**
 * 按套餐关闭多余的模块开通
 */
class EloquentModuleGrantSync implements ModuleGrantSync
{
    /**
     * 关闭当前租户里不在清单中的已开通模块
     */
    public function retainOnly(array $moduleCodes): void
    {
        $query = TenantModule::query()->where('status', 1);

        if ($moduleCodes === []) {
            $query->update(['status' => 0]);

            return;
        }

        // 只动当前租户作用域内的开通记录。
        $query->whereNotIn('module_code', $moduleCodes)->update(['status' => 0]);
    }
}
