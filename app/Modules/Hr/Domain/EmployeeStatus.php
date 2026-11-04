<?php

/**
 * 员工在职状态
 *
 * @package     App\Modules\Hr\Domain
 * @license     MIT License (MIT)
 * @author      Sunny Yang <githup@163.com> QQ4873473
 * @copyright   Copyright (c) 2024 YourCompany. All rights reserved.
 * @link        http://www.budff.com
 */

namespace App\Modules\Hr\Domain;

/**
 * 员工在职状态
 */
final class EmployeeStatus
{
    public const PENDING = 0;

    public const ACTIVE = 1;

    public const LEFT = 2;
}
