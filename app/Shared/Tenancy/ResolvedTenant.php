<?php

/**
 * 已解析的租户
 *
 * @package     App\Shared\Tenancy
 * @license     MIT License (MIT)
 * @author      Sunny Yang <githup@163.com> QQ4873473
 * @copyright   Copyright (c) 2024 YourCompany. All rights reserved.
 * @link        http://www.budff.com
 */

namespace App\Shared\Tenancy;

/**
 * 已解析的租户
 */
final class ResolvedTenant
{
    /**
     * 注入依赖
     */
    public function __construct(
        public readonly int $id,
        public readonly string $slug,
        public readonly bool $isOwner,
        public readonly int $memberId,
    ) {}
}

