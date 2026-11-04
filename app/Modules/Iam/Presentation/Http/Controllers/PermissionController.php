<?php

/**
 * 权限目录接口
 *
 * @package     App\Modules\Iam\Presentation\Http\Controllers
 * @license     MIT License (MIT)
 * @author      Sunny Yang <githup@163.com> QQ4873473
 * @copyright   Copyright (c) 2024 YourCompany. All rights reserved.
 * @link        http://www.budff.com
 */

namespace App\Modules\Iam\Presentation\Http\Controllers;

use App\Modules\Iam\Application\Permission\PermissionQueryService;
use App\Shared\Presentation\Http\ApiResponse;
use Illuminate\Http\JsonResponse;

/**
 * 权限目录接口
 */
class PermissionController
{
    /**
     * 注入依赖
     */
    public function __construct(private readonly PermissionQueryService $permissions) {}

    /**
     * 返回列表
     */
    public function index(): JsonResponse
    {
        return ApiResponse::success($this->permissions->list());
    }
}

