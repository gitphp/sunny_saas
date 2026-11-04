<?php

/**
 * 按套餐收拢模块开通记录
 *
 * @package     App\Modules\Iam\Application\Contracts
 * @license     MIT License (MIT)
 * @author      Sunny Yang <githup@163.com> QQ4873473
 * @copyright   Copyright (c) 2024 YourCompany. All rights reserved.
 * @link        http://www.budff.com
 */

namespace App\Modules\Iam\Application\Contracts;

/**
 * 按套餐收拢模块开通记录
 */
interface ModuleGrantSync
{
    /**
     * 关闭当前租户里不在清单中的已开通模块
     *
     * @param  list<string>  $moduleCodes
     */
    public function retainOnly(array $moduleCodes): void;
}
