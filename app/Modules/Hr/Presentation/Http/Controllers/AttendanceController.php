<?php

/**
 * 考勤接口
 *
 * @package     App\Modules\Hr\Presentation\Http\Controllers
 * @license     MIT License (MIT)
 * @author      Sunny Yang <githup@163.com> QQ4873473
 * @copyright   Copyright (c) 2024 YourCompany. All rights reserved.
 * @link        http://www.budff.com
 */

namespace App\Modules\Hr\Presentation\Http\Controllers;

use App\Modules\Hr\Application\Attendance\AttendanceService;
use App\Modules\Hr\Presentation\Http\Requests\AttendanceRequest;
use App\Shared\Presentation\Http\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * 考勤接口
 */
class AttendanceController
{
    /**
     * 注入依赖
     */
    public function __construct(private readonly AttendanceService $attendance) {}

    /**
     * 返回列表
     */
    public function index(Request $request): JsonResponse
    {
        return ApiResponse::success($this->attendance->list((int) $request->user()->id));
    }

    /**
     * 登记
     */
    public function store(AttendanceRequest $request): JsonResponse
    {
        return ApiResponse::success($this->attendance->store((int) $request->user()->id, $request->validated()), 'ok', [], 201);
    }
}
