<?php

/**
 * 审批任务状态
 *
 * @package     App\Modules\Approval\Domain
 * @license     MIT License (MIT)
 * @author      Sunny Yang <githup@163.com> QQ4873473
 * @copyright   Copyright (c) 2024 YourCompany. All rights reserved.
 * @link        http://www.budff.com
 */

namespace App\Modules\Approval\Domain;

/**
 * 审批任务状态
 */
final class TaskStatus
{
    public const PENDING = 0;

    public const DONE = 1;

    public const SKIPPED = 2;
}
