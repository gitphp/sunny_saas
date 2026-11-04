<?php

/**
 * 统一业务错误码
 *
 * @package     App\Shared\Exceptions
 * @license     MIT License (MIT)
 * @author      Sunny Yang <githup@163.com> QQ4873473
 * @copyright   Copyright (c) 2024 YourCompany. All rights reserved.
 * @link        http://www.budff.com
 */

namespace App\Shared\Exceptions;

/**
 * 统一业务错误码
 */
final class ErrorCode
{
    public const OK = 0;

    public const BAD_REQUEST = 40000;

    public const TENANT_REQUIRED = 40001;

    public const UNAUTHENTICATED = 40100;

    public const INVALID_CREDENTIALS = 40101;

    public const FORBIDDEN = 40300;

    public const NOT_MEMBER = 40301;

    public const MODULE_DISABLED = 40302;

    public const PERMISSION_DENIED = 40303;

    public const NOT_FOUND = 40400;

    public const VALIDATION = 42200;

    public const SERVER = 50000;
}

