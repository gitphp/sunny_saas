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
use App\Modules\Tenant\Application\Contracts\TenantPlanQuery;
use App\Shared\Exceptions\BusinessException;
use App\Shared\Exceptions\ErrorCode;
use App\Shared\Tenancy\TenantContext;
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
    public function __construct(
        private readonly AccessService $access,
        private readonly TenantPlanQuery $plans,
        private readonly TenantContext $context,
    ) {}

    /**
     * 处理当前请求
     */
    public function handle(Request $request, Closure $next, string $moduleCode): Response
    {
        $included = in_array($moduleCode, $this->plans->forTenant((int) $this->context->id())['modules'], true);

        // 套餐未包含，或租户没有开通，都不能进入该模块。
        if (! $included || ! $this->access->isModuleEnabled($moduleCode)) {
            throw new BusinessException(ErrorCode::MODULE_DISABLED, '模块未开通', 403);
        }

        return $next($request);
    }
}

