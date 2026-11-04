<?php

/**
 * 套餐接口
 *
 * @package     App\Modules\Tenant\Presentation\Http\Controllers
 * @license     MIT License (MIT)
 * @author      Sunny Yang <githup@163.com> QQ4873473
 * @copyright   Copyright (c) 2024 YourCompany. All rights reserved.
 * @link        http://www.budff.com
 */

namespace App\Modules\Tenant\Presentation\Http\Controllers;

use App\Modules\Tenant\Application\ChangeTenantPlan;
use App\Modules\Tenant\Application\Contracts\TenantPlanQuery;
use App\Modules\Tenant\Presentation\Http\Requests\ChangePlanRequest;
use App\Shared\Presentation\Http\ApiResponse;
use App\Shared\Tenancy\TenantContext;
use Illuminate\Http\JsonResponse;

/**
 * 套餐接口
 */
class PlanController
{
    /**
     * 注入依赖
     */
    public function __construct(
        private readonly TenantPlanQuery $plans,
        private readonly ChangeTenantPlan $changeTenantPlan,
        private readonly TenantContext $context,
    ) {}

    /**
     * 返回可售套餐
     */
    public function index(): JsonResponse
    {
        return ApiResponse::success($this->plans->catalog());
    }

    /**
     * 返回当前租户套餐
     */
    public function show(): JsonResponse
    {
        return ApiResponse::success($this->plans->forTenant((int) $this->context->id()));
    }

    /**
     * 更换当前租户套餐
     */
    public function update(ChangePlanRequest $request): JsonResponse
    {
        return ApiResponse::success($this->changeTenantPlan->handle((string) $request->validated('code')));
    }
}
