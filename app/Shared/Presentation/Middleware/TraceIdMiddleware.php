<?php

/**
 * 为每个请求分配追踪号
 *
 * @package     App\Shared\Presentation\Middleware
 * @license     MIT License (MIT)
 * @author      Sunny Yang <githup@163.com> QQ4873473
 * @copyright   Copyright (c) 2024 YourCompany. All rights reserved.
 * @link        http://www.budff.com
 */

namespace App\Shared\Presentation\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

/**
 * 为每个请求分配追踪号
 */
class TraceIdMiddleware
{
    /**
     * 处理当前请求
     */
    public function handle(Request $request, Closure $next): Response
    {
        $incoming = trim((string) $request->header('X-Request-Id', ''));
        $traceId = $this->isUuid($incoming) ? strtolower($incoming) : (string) Str::uuid();

        $request->attributes->set('trace_id', $traceId);

        Log::shareContext([
            'trace_id' => $traceId,
            'tenant_id' => 0,
            'user_id' => 0,
        ]);

        $response = $next($request);
        $response->headers->set('X-Request-Id', $traceId);

        return $response;
    }

    /**
     * 判断字符串是不是合法的追踪号
     */
    private function isUuid(string $value): bool
    {
        return (bool) preg_match(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[1-8][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
            $value
        );
    }
}

