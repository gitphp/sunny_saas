<?php

/**
 * 业务异常
 *
 * @package     App\Shared\Exceptions
 * @license     MIT License (MIT)
 * @author      Sunny Yang <githup@163.com> QQ4873473
 * @copyright   Copyright (c) 2024 YourCompany. All rights reserved.
 * @link        http://www.budff.com
 */

namespace App\Shared\Exceptions;

use RuntimeException;

/**
 * 业务异常
 */
class BusinessException extends RuntimeException
{
    /**
     * 注入依赖
     */
    public function __construct(
        public readonly int $errorCode,
        string $message,
        public readonly int $httpStatus = 400,
        public readonly mixed $data = null,
    ) {
        parent::__construct($message);
    }
}

