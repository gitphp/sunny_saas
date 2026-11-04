<?php

/**
 * 租户接口
 *
 * @package     App\Modules\Tenant\Presentation\Http\Controllers
 * @license     MIT License (MIT)
 * @author      Sunny Yang <githup@163.com> QQ4873473
 * @copyright   Copyright (c) 2024 YourCompany. All rights reserved.
 * @link        http://www.budff.com
 */

namespace App\Modules\Tenant\Presentation\Http\Controllers;

use App\Modules\Tenant\Application\CreateTenant;
use App\Modules\Tenant\Application\ListMyTenants;
use App\Modules\Tenant\Presentation\Http\Requests\StoreTenantRequest;
use App\Shared\Presentation\Http\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * 租户接口
 */
class TenantController
{
    /**
     * 注入依赖
     */
    public function __construct(
        private readonly CreateTenant $createTenant,
        private readonly ListMyTenants $listMyTenants,
    ) {}

    /**
     * 返回我加入的租户
     */
    public function mine(Request $request): JsonResponse
    {
        return ApiResponse::success($this->listMyTenants->handle((int) $request->user()->id));
    }

    /**
     * 新建
     */
    public function store(StoreTenantRequest $request): JsonResponse
    {
        $tenant = $this->createTenant->handle(
            (int) $request->user()->id,
            (string) $request->validated('name'),
            (string) $request->validated('slug'),
        );

        return ApiResponse::success($tenant, 'ok', [], 201);
    }
}

