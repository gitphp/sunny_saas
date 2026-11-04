<?php

/**
 * 缺少租户上下文异常
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
 * 缺少租户上下文异常
 */
class MissingTenantContextException extends RuntimeException
{
    /**
     * 注入依赖
     */
    public function __construct(string $message = '缺少租户上下文，已拒绝查询')
    {
        parent::__construct($message);
    }
}

