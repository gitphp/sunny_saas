<?php

/**
 * 模块开通接口
 *
 * @package     App\Modules\Iam\Presentation\Http\Controllers
 * @license     MIT License (MIT)
 * @author      Sunny Yang <githup@163.com> QQ4873473
 * @copyright   Copyright (c) 2024 YourCompany. All rights reserved.
 * @link        http://www.budff.com
 */

namespace App\Modules\Iam\Presentation\Http\Controllers;

use App\Modules\Iam\Application\Module\ModuleQueryService;
use App\Modules\Iam\Presentation\Http\Requests\UpdateModuleRequest;
use App\Shared\Presentation\Http\ApiResponse;
use Illuminate\Http\JsonResponse;

/**
 * 模块开通接口
 */
class ModuleController
{
    /**
     * 注入依赖
     */
    public function __construct(private readonly ModuleQueryService $modules) {}

    /**
     * 返回列表
     */
    public function index(): JsonResponse
    {
        return ApiResponse::success($this->modules->list());
    }

    /**
     * 更新
     */
    public function update(UpdateModuleRequest $request, string $moduleCode): JsonResponse
    {
        return ApiResponse::success(
            $this->modules->updateStatus($moduleCode, (int) $request->validated('status')),
        );
    }
}

