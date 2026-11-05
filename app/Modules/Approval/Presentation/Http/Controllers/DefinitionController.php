<?php

/**
 * 流程定义与版本接口
 *
 * @package     App\Modules\Approval\Presentation\Http\Controllers
 * @license     MIT License (MIT)
 * @author      Sunny Yang <githup@163.com> QQ4873473
 * @copyright   Copyright (c) 2024 YourCompany. All rights reserved.
 * @link        http://www.budff.com
 */

namespace App\Modules\Approval\Presentation\Http\Controllers;

use App\Modules\Approval\Application\Definition\DefinitionService;
use App\Modules\Approval\Presentation\Http\Requests\DefinitionRequest;
use App\Shared\Presentation\Http\ApiResponse;
use Illuminate\Http\JsonResponse;

/**
 * 流程定义与版本接口
 */
class DefinitionController
{
    /**
     * 注入依赖
     */
    public function __construct(private readonly DefinitionService $definitions) {}

    /**
     * 发起页目录
     */
    public function catalog(): JsonResponse
    {
        return ApiResponse::success($this->definitions->catalog());
    }

    /**
     * 可选审批人，来自当前企业的有效成员
     */
    public function members(): JsonResponse
    {
        return ApiResponse::success($this->definitions->memberOptions());
    }

    /**
     * 流程列表
     */
    public function index(): JsonResponse
    {
        return ApiResponse::success($this->definitions->definitions());
    }

    /**
     * 新建流程，同时生成第一份草稿
     */
    public function store(DefinitionRequest $request): JsonResponse
    {
        return ApiResponse::success($this->definitions->storeDefinition($request->validated()), 'ok', [], 201);
    }

    /**
     * 流程详情，含版本列表和正在编辑的草稿
     */
    public function show(int $definition): JsonResponse
    {
        return ApiResponse::success($this->definitions->show($definition));
    }

    /**
     * 保存基础信息、表单和流程图
     */
    public function update(DefinitionRequest $request, int $definition): JsonResponse
    {
        return ApiResponse::success($this->definitions->update($definition, $request->validated()));
    }

    /**
     * 发布当前草稿
     */
    public function publish(int $definition): JsonResponse
    {
        return ApiResponse::success($this->definitions->publish($definition));
    }
}
