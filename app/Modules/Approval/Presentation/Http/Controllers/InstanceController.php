<?php

/**
 * 审批单、待办和数据接口
 *
 * @package     App\Modules\Approval\Presentation\Http\Controllers
 * @license     MIT License (MIT)
 * @author      Sunny Yang <githup@163.com> QQ4873473
 * @copyright   Copyright (c) 2024 YourCompany. All rights reserved.
 * @link        http://www.budff.com
 */

namespace App\Modules\Approval\Presentation\Http\Controllers;

use App\Modules\Approval\Application\Runtime\InstanceService;
use App\Modules\Approval\Presentation\Http\Requests\ActionRequest;
use App\Modules\Approval\Presentation\Http\Requests\SubmitInstanceRequest;
use App\Shared\Presentation\Http\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * 审批单、待办和数据接口
 */
class InstanceController
{
    /**
     * 注入依赖
     */
    public function __construct(private readonly InstanceService $instances) {}

    /**
     * 我的申请
     */
    public function mine(Request $request): JsonResponse
    {
        return ApiResponse::success($this->instances->mine((int) $request->user()->id));
    }

    /**
     * 企业内的审批数据
     */
    public function data(): JsonResponse
    {
        return ApiResponse::success($this->instances->data());
    }

    /**
     * 待办、已办或抄送
     */
    public function tasks(Request $request): JsonResponse
    {
        return ApiResponse::success($this->instances->tasks((int) $request->user()->id, (string) $request->query('box', 'todo')));
    }

    /**
     * 发起审批
     */
    public function store(SubmitInstanceRequest $request): JsonResponse
    {
        $input = $request->validated();

        return ApiResponse::success($this->instances->start(
            (string) $input['definition_code'],
            (int) $request->user()->id,
            (string) ($input['title'] ?? ''),
            (array) $input['form_data'],
            (string) ($input['source_module'] ?? ''),
            (int) ($input['source_id'] ?? 0),
        ), 'ok', [], 201);
    }

    /**
     * 审批详情
     */
    public function show(Request $request, int $instance): JsonResponse
    {
        return ApiResponse::success($this->instances->show($instance, (int) $request->user()->id));
    }

    /**
     * 同意
     */
    public function approve(ActionRequest $request, int $instance): JsonResponse
    {
        return ApiResponse::success($this->instances->approve($instance, (int) $request->user()->id, (string) $request->validated('comment', '')));
    }

    /**
     * 驳回
     */
    public function reject(ActionRequest $request, int $instance): JsonResponse
    {
        return ApiResponse::success($this->instances->reject($instance, (int) $request->user()->id, (string) $request->validated('comment', '')));
    }

    /**
     * 撤销
     */
    public function revoke(ActionRequest $request, int $instance): JsonResponse
    {
        return ApiResponse::success($this->instances->revoke($instance, (int) $request->user()->id, (string) $request->validated('comment', '')));
    }

    /**
     * 评论
     */
    public function comment(ActionRequest $request, int $instance): JsonResponse
    {
        return ApiResponse::success($this->instances->comment($instance, (int) $request->user()->id, (string) $request->validated('comment')));
    }
}
