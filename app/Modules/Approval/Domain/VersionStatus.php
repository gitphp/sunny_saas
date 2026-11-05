<?php

/**
 * 流程版本状态
 *
 * @package     App\Modules\Approval\Domain
 * @license     MIT License (MIT)
 * @author      Sunny Yang <githup@163.com> QQ4873473
 * @copyright   Copyright (c) 2024 YourCompany. All rights reserved.
 * @link        http://www.budff.com
 */

namespace App\Modules\Approval\Domain;

/**
 * 流程版本状态
 */
final class VersionStatus
{
    public const DRAFT = 0;

    public const PUBLISHED = 1;

    public const ARCHIVED = 2;
}
