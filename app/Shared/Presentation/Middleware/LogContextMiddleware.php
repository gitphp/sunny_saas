<?php

/**
 * 把追踪号和租户写入日志上下文
 *
 * @package     App\Shared\Presentation\Middleware
 * @license     MIT License (MIT)
 * @author      Sunny Yang <githup@163.com> QQ4873473
 * @copyright   Copyright (c) 2024 YourCompany. All rights reserved.
 * @link        http://www.budff.com
 */

namespace App\Shared\Presentation\Middleware;

use App\Shared\Tenancy\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * 把追踪号和租户写入日志上下文
 */
class LogContextMiddleware
{
    /**
     * 注入依赖
     */
    public function __construct(private readonly TenantContext $context) {}

    /**
     * 处理当前请求
     */
    public function handle(Request $request, Closure $next): Response
    {
        Log::shareContext([
            'trace_id' => (string) $request->attributes->get('trace_id', ''),
            'tenant_id' => $this->context->id() ?? 0,
            'user_id' => (int) ($request->user()?->id ?? 0),
        ]);

        return $next($request);
    }
}

