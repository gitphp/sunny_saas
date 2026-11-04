<?php

/**
 * 角色接口
 *
 * @package     App\Modules\Iam\Presentation\Http\Controllers
 * @license     MIT License (MIT)
 * @author      Sunny Yang <githup@163.com> QQ4873473
 * @copyright   Copyright (c) 2024 YourCompany. All rights reserved.
 * @link        http://www.budff.com
 */

namespace App\Modules\Iam\Presentation\Http\Controllers;

use App\Modules\Iam\Application\Role\RoleService;
use App\Modules\Iam\Presentation\Http\Requests\RoleRequest;
use App\Shared\Presentation\Http\ApiResponse;
use Illuminate\Http\JsonResponse;

/**
 * 角色接口
 */
class RoleController
{
    /**
     * 注入依赖
     */
    public function __construct(private readonly RoleService $roles) {}

    /**
     * 返回列表
     */
    public function index(): JsonResponse
    {
        return ApiResponse::success($this->roles->list());
    }

    /**
     * 新建
     */
    public function store(RoleRequest $request): JsonResponse
    {
        return ApiResponse::success($this->roles->create($request->validated()), 'ok', [], 201);
    }

    /**
     * 查看
     */
    public function show(int $role): JsonResponse
    {
        return ApiResponse::success($this->roles->show($role));
    }

    /**
     * 更新
     */
    public function update(RoleRequest $request, int $role): JsonResponse
    {
        return ApiResponse::success($this->roles->update($role, $request->validated()));
    }

    /**
     * 删除
     */
    public function destroy(int $role): JsonResponse
    {
        $this->roles->delete($role);

        return ApiResponse::success();
    }
}

