<?php

/**
 * 用于验证队列租户上下文的探测任务
 *
 * @package     Tests\Fixtures
 * @license     MIT License (MIT)
 * @author      Sunny Yang <githup@163.com> QQ4873473
 * @copyright   Copyright (c) 2024 YourCompany. All rights reserved.
 * @link        http://www.budff.com
 */

namespace Tests\Fixtures;

use App\Shared\Tenancy\TenantContext;
use App\Shared\Tenancy\TenantQueueMiddleware;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * 用于验证队列租户上下文的探测任务
 */
class ProbeTenantJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public static ?int $seen = null;

    public static mixed $payloadTenantId = null;

    /**
     * 声明任务执行前要恢复租户上下文
     */
    public function middleware(): array
    {
        return [new TenantQueueMiddleware];
    }

    /**
     * 处理当前请求
     */
    public function handle(): void
    {
        self::$payloadTenantId = $this->job?->payload()['tenant_id'] ?? null;
        self::$seen = app(TenantContext::class)->id();
    }
}

