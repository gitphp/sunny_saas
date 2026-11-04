<?php

/**
 * 带租户前缀的缓存
 *
 * @package     App\Shared\Tenancy
 * @license     MIT License (MIT)
 * @author      Sunny Yang <githup@163.com> QQ4873473
 * @copyright   Copyright (c) 2024 YourCompany. All rights reserved.
 * @link        http://www.budff.com
 */

namespace App\Shared\Tenancy;

use App\Shared\Exceptions\MissingTenantContextException;
use Illuminate\Contracts\Cache\Repository;

/**
 * 带租户前缀的缓存
 */
class TenantCache
{
    /**
     * 注入依赖
     */
    public function __construct(
        private readonly TenantContext $context,
        private readonly Repository $cache,
    ) {}

    /**
     * 生成带租户前缀的缓存键
     */
    public function key(string $logicalKey): string
    {
        $tenantId = $this->context->id();

        if ($tenantId === null || $tenantId <= 0) {
            throw new MissingTenantContextException('缓存缺少租户上下文');
        }

        // 相同逻辑键在不同租户下不能命中，隔离靠前缀而不是切换 Redis 库。
        return 't:'.$tenantId.':'.$logicalKey;
    }

    /**
     * 按逻辑键写入，实际键带有租户前缀
     */
    public function put(string $logicalKey, mixed $value, int $ttlSeconds = 3600): void
    {
        $this->cache->put($this->key($logicalKey), $value, $ttlSeconds);
    }

    /**
     * 按逻辑键读取，实际键带有租户前缀
     */
    public function get(string $logicalKey, mixed $default = null): mixed
    {
        return $this->cache->get($this->key($logicalKey), $default);
    }

    /**
     * 删除当前租户下的缓存键
     */
    public function forget(string $logicalKey): void
    {
        $this->cache->forget($this->key($logicalKey));
    }
}

