<?php

/**
 * 审批单状态
 *
 * @package     App\Modules\Approval\Domain
 * @license     MIT License (MIT)
 * @author      Sunny Yang <githup@163.com> QQ4873473
 * @copyright   Copyright (c) 2024 YourCompany. All rights reserved.
 * @link        http://www.budff.com
 */

namespace App\Modules\Approval\Domain;

/**
 * 审批单状态
 */
final class InstanceStatus
{
    public const APPROVING = 1;

    public const APPROVED = 2;

    public const REJECTED = 3;

    public const REVOKED = 4;
}
