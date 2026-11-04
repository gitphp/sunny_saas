<?php

/**
 * 队列任务中的租户上下文
 *
 * @package     App\Shared\Tenancy
 * @license     MIT License (MIT)
 * @author      Sunny Yang <githup@163.com> QQ4873473
 * @copyright   Copyright (c) 2024 YourCompany. All rights reserved.
 * @link        http://www.budff.com
 */

namespace App\Shared\Tenancy;

use Closure;

/**
 * 队列任务中的租户上下文
 */
class TenantQueueMiddleware
{
    /**
     * 处理当前请求
     */
    public function handle(object $job, Closure $next): mixed
    {
        $payload = [];

        if (isset($job->job) && is_object($job->job) && method_exists($job->job, 'payload')) {
            $payload = $job->job->payload();
        }

        $tenantId = (int) ($payload['tenant_id'] ?? 0);
        $context = app(TenantContext::class);

        if ($tenantId > 0) {
            $context->set($tenantId, '');
        }

        try {
            return $next($job);
        } finally {
            // 任务结束后清掉租户，避免 worker 里的下一个任务串上下文。
            $context->clear();
        }
    }
}

