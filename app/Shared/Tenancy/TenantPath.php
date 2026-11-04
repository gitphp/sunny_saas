<?php

/**
 * 带租户前缀的文件路径
 *
 * @package     App\Shared\Tenancy
 * @license     MIT License (MIT)
 * @author      Sunny Yang <githup@163.com> QQ4873473
 * @copyright   Copyright (c) 2024 YourCompany. All rights reserved.
 * @link        http://www.budff.com
 */

namespace App\Shared\Tenancy;

use InvalidArgumentException;

/**
 * 带租户前缀的文件路径
 */
class TenantPath
{
    /**
     * 拼出带租户编号的相对路径
     */
    public function make(int $tenantId, string ...$segments): string
    {
        if ($tenantId <= 0) {
            throw new InvalidArgumentException('租户编号无效');
        }

        $parts = [];

        foreach ($segments as $segment) {
            $normalized = str_replace('\\', '/', trim($segment));

            if ($normalized === '') {
                throw new InvalidArgumentException('路径段不能为空');
            }

            foreach (explode('/', $normalized) as $part) {
                // 拒绝空段和 ..，避免写出租户目录。
                if ($part === '' || $part === '.' || $part === '..') {
                    throw new InvalidArgumentException('路径段非法');
                }

                $parts[] = $part;
            }
        }

        if ($parts === []) {
            return (string) $tenantId;
        }

        return $tenantId.'/'.implode('/', $parts);
    }
}

