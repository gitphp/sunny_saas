<?php

/**
 * 校验当前用户拥有权限码
 *
 * @package     App\Modules\Iam\Presentation\Http\Middleware
 * @license     MIT License (MIT)
 * @author      Sunny Yang <githup@163.com> QQ4873473
 * @copyright   Copyright (c) 2024 YourCompany. All rights reserved.
 * @link        http://www.budff.com
 */

namespace App\Modules\Iam\Presentation\Http\Middleware;

use App\Modules\Iam\Application\Access\AccessService;
use App\Shared\Exceptions\BusinessException;
use App\Shared\Exceptions\ErrorCode;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * 校验当前用户拥有权限码
 */
class EnsurePermission
{
    /**
     * 注入依赖
     */
    public function __construct(private readonly AccessService $access) {}

    /**
     * 处理当前请求
     */
    public function handle(Request $request, Closure $next, string $code): Response
    {
        $userId = (int) $request->user()->id;

        if (! $this->access->hasPermission($userId, $code)) {
            throw new BusinessException(ErrorCode::PERMISSION_DENIED, '缺少权限', 403);
        }

        return $next($request);
    }
}

