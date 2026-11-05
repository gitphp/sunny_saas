<?php

/**
 * 流程分组接口
 *
 * @package     App\Modules\Approval\Presentation\Http\Controllers
 * @license     MIT License (MIT)
 * @author      Sunny Yang <githup@163.com> QQ4873473
 * @copyright   Copyright (c) 2024 YourCompany. All rights reserved.
 * @link        http://www.budff.com
 */

namespace App\Modules\Approval\Presentation\Http\Controllers;

use App\Modules\Approval\Application\Definition\DefinitionService;
use App\Modules\Approval\Presentation\Http\Requests\GroupRequest;
use App\Shared\Presentation\Http\ApiResponse;
use Illuminate\Http\JsonResponse;

/**
 * 流程分组接口
 */
class GroupController
{
    /**
     * 注入依赖
     */
    public function __construct(private readonly DefinitionService $definitions) {}

    /**
     * 返回分组
     */
    public function index(): JsonResponse
    {
        return ApiResponse::success($this->definitions->groups());
    }

    /**
     * 新建分组
     */
    public function store(GroupRequest $request): JsonResponse
    {
        return ApiResponse::success($this->definitions->storeGroup($request->validated()), 'ok', [], 201);
    }

    /**
     * 更新分组
     */
    public function update(GroupRequest $request, int $group): JsonResponse
    {
        return ApiResponse::success($this->definitions->updateGroup($group, $request->validated()));
    }

    /**
     * 删除分组
     */
    public function destroy(int $group): JsonResponse
    {
        $this->definitions->destroyGroup($group);

        return ApiResponse::success();
    }
}
