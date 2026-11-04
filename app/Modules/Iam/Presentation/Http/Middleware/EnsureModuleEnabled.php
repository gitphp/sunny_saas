<?php

/**
 * 校验租户已开通指定模块
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
 * 校验租户已开通指定模块
 */
class EnsureModuleEnabled
{
    /**
     * 注入依赖
     */
    public function __construct(private readonly AccessService $access) {}

    /**
     * 处理当前请求
     */
    public function handle(Request $request, Closure $next, string $moduleCode): Response
    {
        if (! $this->access->isModuleEnabled($moduleCode)) {
            throw new BusinessException(ErrorCode::MODULE_DISABLED, '模块未开通', 403);
        }

        return $next($request);
    }
}

