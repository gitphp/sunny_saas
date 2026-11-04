<?php

/**
 * 请假接口
 *
 * @package     App\Modules\Hr\Presentation\Http\Controllers
 * @license     MIT License (MIT)
 * @author      Sunny Yang <githup@163.com> QQ4873473
 * @copyright   Copyright (c) 2024 YourCompany. All rights reserved.
 * @link        http://www.budff.com
 */

namespace App\Modules\Hr\Presentation\Http\Controllers;

use App\Modules\Hr\Application\Leave\LeaveService;
use App\Modules\Hr\Presentation\Http\Requests\LeaveRequest;
use App\Shared\Presentation\Http\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * 请假接口
 */
class LeaveController
{
    /**
     * 注入依赖
     */
    public function __construct(private readonly LeaveService $leaves) {}

    /**
     * 返回列表
     */
    public function index(Request $request): JsonResponse
    {
        return ApiResponse::success($this->leaves->list((int) $request->user()->id));
    }

    /**
     * 提交
     */
    public function store(LeaveRequest $request): JsonResponse
    {
        return ApiResponse::success($this->leaves->store((int) $request->user()->id, $request->validated()), 'ok', [], 201);
    }

    /**
     * 批准
     */
    public function approve(Request $request, int $leave): JsonResponse
    {
        return ApiResponse::success($this->leaves->review($leave, (int) $request->user()->id, 1));
    }

    /**
     * 驳回
     */
    public function reject(Request $request, int $leave): JsonResponse
    {
        return ApiResponse::success($this->leaves->review($leave, (int) $request->user()->id, 2));
    }
}
