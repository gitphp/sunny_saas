<?php

/**
 * 当前请求的租户上下文
 *
 * @package     App\Shared\Tenancy
 * @license     MIT License (MIT)
 * @author      Sunny Yang <githup@163.com> QQ4873473
 * @copyright   Copyright (c) 2024 YourCompany. All rights reserved.
 * @link        http://www.budff.com
 */

namespace App\Shared\Tenancy;

/**
 * 当前请求的租户上下文
 */
class TenantContext
{
    private ?int $tenantId = null;

    private string $tenantSlug = '';

    /**
     * 写入当前租户
     */
    public function set(int $tenantId, string $slug): void
    {
        $this->tenantId = $tenantId;
        $this->tenantSlug = $slug;
    }

    /**
     * 读取当前租户主键
     */
    public function id(): ?int
    {
        return $this->tenantId;
    }

    /**
     * 读取当前租户标识
     */
    public function slug(): string
    {
        return $this->tenantSlug;
    }

    /**
     * 判断当前是否已有租户
     */
    public function has(): bool
    {
        return $this->tenantId !== null && $this->tenantId > 0;
    }

    /**
     * 清空当前租户，避免串到下一个请求或任务
     */
    public function clear(): void
    {
        $this->tenantId = null;
        $this->tenantSlug = '';
    }
}

