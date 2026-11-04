<?php

/**
 * 员工档案与入职接口
 *
 * @package     App\Modules\Hr\Presentation\Http\Controllers
 * @license     MIT License (MIT)
 * @author      Sunny Yang <githup@163.com> QQ4873473
 * @copyright   Copyright (c) 2024 YourCompany. All rights reserved.
 * @link        http://www.budff.com
 */

namespace App\Modules\Hr\Presentation\Http\Controllers;

use App\Modules\Hr\Application\Employee\EmployeeService;
use App\Modules\Hr\Presentation\Http\Requests\EmployeeRequest;
use App\Modules\Hr\Presentation\Http\Requests\OnboardRequest;
use App\Shared\Presentation\Http\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * 员工档案与入职接口
 */
class EmployeeController
{
    /**
     * 注入依赖
     */
    public function __construct(private readonly EmployeeService $employees) {}

    /**
     * 返回列表，可用 status 区分待入职、在职和离职
     */
    public function index(Request $request): JsonResponse
    {
        $status = $request->query('status');

        return ApiResponse::success($this->employees->list(
            (int) $request->user()->id,
            $status === null || $status === '' ? null : (int) $status,
        ));
    }

    /**
     * 新建档案
     */
    public function store(EmployeeRequest $request): JsonResponse
    {
        return ApiResponse::success($this->employees->store((int) $request->user()->id, $request->validated()), 'ok', [], 201);
    }

    /**
     * 查看
     */
    public function show(Request $request, int $employee): JsonResponse
    {
        return ApiResponse::success($this->employees->show($employee, (int) $request->user()->id));
    }

    /**
     * 更新
     */
    public function update(EmployeeRequest $request, int $employee): JsonResponse
    {
        return ApiResponse::success($this->employees->update($employee, (int) $request->user()->id, $request->validated()));
    }

    /**
     * 办理入职
     */
    public function onboard(OnboardRequest $request, int $employee): JsonResponse
    {
        $hiredOn = $request->validated('hired_on');

        return ApiResponse::success($this->employees->onboard(
            $employee,
            (int) $request->user()->id,
            is_string($hiredOn) ? $hiredOn : null,
        ));
    }
}
