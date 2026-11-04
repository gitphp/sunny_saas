<?php

/**
 * 岗位接口
 *
 * @package     App\Modules\Hr\Presentation\Http\Controllers
 * @license     MIT License (MIT)
 * @author      Sunny Yang <githup@163.com> QQ4873473
 * @copyright   Copyright (c) 2024 YourCompany. All rights reserved.
 * @link        http://www.budff.com
 */

namespace App\Modules\Hr\Presentation\Http\Controllers;

use App\Modules\Hr\Application\Position\PositionService;
use App\Modules\Hr\Presentation\Http\Requests\PositionRequest;
use App\Shared\Presentation\Http\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * 岗位接口
 */
class PositionController
{
    /**
     * 注入依赖
     */
    public function __construct(private readonly PositionService $positions) {}

    /**
     * 返回列表
     */
    public function index(Request $request): JsonResponse
    {
        return ApiResponse::success($this->positions->list((int) $request->user()->id));
    }

    /**
     * 新建
     */
    public function store(PositionRequest $request): JsonResponse
    {
        return ApiResponse::success($this->positions->store((int) $request->user()->id, $request->validated()), 'ok', [], 201);
    }

    /**
     * 更新
     */
    public function update(PositionRequest $request, int $position): JsonResponse
    {
        return ApiResponse::success($this->positions->update($position, (int) $request->user()->id, $request->validated()));
    }

    /**
     * 删除
     */
    public function destroy(int $position): JsonResponse
    {
        $this->positions->destroy($position);

        return ApiResponse::success();
    }
}
