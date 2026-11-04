<?php

/**
 * 解析并写入当前租户
 *
 * @package     App\Shared\Presentation\Middleware
 * @license     MIT License (MIT)
 * @author      Sunny Yang <githup@163.com> QQ4873473
 * @copyright   Copyright (c) 2024 YourCompany. All rights reserved.
 * @link        http://www.budff.com
 */

namespace App\Shared\Presentation\Middleware;

use App\Shared\Exceptions\BusinessException;
use App\Shared\Exceptions\ErrorCode;
use App\Shared\Tenancy\TenantContext;
use App\Shared\Tenancy\TenantResolver;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * 解析并写入当前租户
 */
class TenantMiddleware
{
    /**
     * 注入依赖
     */
    public function __construct(
        private readonly TenantResolver $resolver,
        private readonly TenantContext $context,
    ) {}

    /**
     * 处理当前请求
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null) {
            throw new BusinessException(ErrorCode::UNAUTHENTICATED, '未登录', 401);
        }

        $slug = trim((string) $request->header('X-Tenant', ''));

        if ($slug === '') {
            throw new BusinessException(ErrorCode::TENANT_REQUIRED, '缺少租户标识', 400);
        }

        $resolved = $this->resolver->resolve($slug, (int) $user->id);
        $this->context->set($resolved->id, $resolved->slug);

        Log::shareContext([
            'trace_id' => (string) $request->attributes->get('trace_id', ''),
            'tenant_id' => $resolved->id,
            'user_id' => (int) $user->id,
        ]);

        try {
            return $next($request);
        } finally {
            // 请求结束后清掉租户，避免下一次请求串上下文。
            $this->context->clear();
        }
    }
}

