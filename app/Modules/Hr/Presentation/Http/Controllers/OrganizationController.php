<?php

/**
 * 组织架构接口
 *
 * @package     App\Modules\Hr\Presentation\Http\Controllers
 * @license     MIT License (MIT)
 * @author      Sunny Yang <githup@163.com> QQ4873473
 * @copyright   Copyright (c) 2024 YourCompany. All rights reserved.
 * @link        http://www.budff.com
 */

namespace App\Modules\Hr\Presentation\Http\Controllers;

use App\Modules\Hr\Application\Organization\OrganizationService;
use App\Shared\Presentation\Http\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * 组织架构接口
 */
class OrganizationController
{
    /**
     * 注入依赖
     */
    public function __construct(private readonly OrganizationService $organization) {}

    /**
     * 返回部门、岗位和在册人数
     */
    public function index(Request $request): JsonResponse
    {
        return ApiResponse::success($this->organization->tree((int) $request->user()->id));
    }
}
