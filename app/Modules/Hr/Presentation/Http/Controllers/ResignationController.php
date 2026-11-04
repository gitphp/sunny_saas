<?php

/**
 * 离职接口
 *
 * @package     App\Modules\Hr\Presentation\Http\Controllers
 * @license     MIT License (MIT)
 * @author      Sunny Yang <githup@163.com> QQ4873473
 * @copyright   Copyright (c) 2024 YourCompany. All rights reserved.
 * @link        http://www.budff.com
 */

namespace App\Modules\Hr\Presentation\Http\Controllers;

use App\Modules\Hr\Application\Resignation\ResignationService;
use App\Modules\Hr\Presentation\Http\Requests\ResignationRequest;
use App\Shared\Presentation\Http\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * 离职接口
 */
class ResignationController
{
    /**
     * 注入依赖
     */
    public function __construct(private readonly ResignationService $resignations) {}

    /**
     * 返回列表
     */
    public function index(Request $request): JsonResponse
    {
        return ApiResponse::success($this->resignations->list((int) $request->user()->id));
    }

    /**
     * 发起
     */
    public function store(ResignationRequest $request): JsonResponse
    {
        return ApiResponse::success($this->resignations->store((int) $request->user()->id, $request->validated()), 'ok', [], 201);
    }

    /**
     * 批准
     */
    public function approve(Request $request, int $resignation): JsonResponse
    {
        return ApiResponse::success($this->resignations->review($resignation, (int) $request->user()->id, 1));
    }

    /**
     * 驳回
     */
    public function reject(Request $request, int $resignation): JsonResponse
    {
        return ApiResponse::success($this->resignations->review($resignation, (int) $request->user()->id, 2));
    }

    /**
     * 办结
     */
    public function complete(Request $request, int $resignation): JsonResponse
    {
        return ApiResponse::success($this->resignations->complete($resignation, (int) $request->user()->id));
    }
}
