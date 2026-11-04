<?php

/**
 * 部门接口
 *
 * @package     App\Modules\Iam\Presentation\Http\Controllers
 * @license     MIT License (MIT)
 * @author      Sunny Yang <githup@163.com> QQ4873473
 * @copyright   Copyright (c) 2024 YourCompany. All rights reserved.
 * @link        http://www.budff.com
 */

namespace App\Modules\Iam\Presentation\Http\Controllers;

use App\Modules\Iam\Application\Department\DepartmentService;
use App\Modules\Iam\Presentation\Http\Requests\DepartmentRequest;
use App\Shared\Presentation\Http\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * 部门接口
 */
class DepartmentController
{
    /**
     * 注入依赖
     */
    public function __construct(private readonly DepartmentService $departments) {}

    /**
     * 返回列表
     */
    public function index(Request $request): JsonResponse
    {
        return ApiResponse::success($this->departments->list((int) $request->user()->id));
    }

    /**
     * 新建
     */
    public function store(DepartmentRequest $request): JsonResponse
    {
        return ApiResponse::success(
            $this->departments->create($request->validated()),
            'ok',
            [],
            201,
        );
    }

    /**
     * 查看
     */
    public function show(Request $request, int $department): JsonResponse
    {
        return ApiResponse::success($this->departments->show($department, (int) $request->user()->id));
    }

    /**
     * 更新
     */
    public function update(DepartmentRequest $request, int $department): JsonResponse
    {
        return ApiResponse::success(
            $this->departments->update($department, (int) $request->user()->id, $request->validated()),
        );
    }

    /**
     * 删除
     */
    public function destroy(Request $request, int $department): JsonResponse
    {
        $this->departments->delete($department, (int) $request->user()->id);

        return ApiResponse::success();
    }
}

