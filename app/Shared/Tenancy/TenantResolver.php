<?php

/**
 * 租户解析端口
 *
 * @package     App\Shared\Tenancy
 * @license     MIT License (MIT)
 * @author      Sunny Yang <githup@163.com> QQ4873473
 * @copyright   Copyright (c) 2024 YourCompany. All rights reserved.
 * @link        http://www.budff.com
 */

namespace App\Shared\Tenancy;

/**
 * 租户解析端口
 */
interface TenantResolver
{
    /**
     * 按请求头解析租户并核对成员身份
     */
    public function resolve(string $slug, int $userId): ResolvedTenant;
}

