<?php

/**
 * 认证接口
 *
 * @package     App\Modules\Iam\Presentation\Http\Controllers
 * @license     MIT License (MIT)
 * @author      Sunny Yang <githup@163.com> QQ4873473
 * @copyright   Copyright (c) 2024 YourCompany. All rights reserved.
 * @link        http://www.budff.com
 */

namespace App\Modules\Iam\Presentation\Http\Controllers;

use App\Modules\Iam\Application\Auth\AuthService;
use App\Modules\Iam\Infrastructure\Persistence\User;
use App\Modules\Iam\Presentation\Http\Requests\LoginRequest;
use App\Modules\Iam\Presentation\Http\Requests\RegisterRequest;
use App\Shared\Presentation\Http\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * 认证接口
 */
class AuthController
{
    /**
     * 注入依赖
     */
    public function __construct(private readonly AuthService $auth) {}

    /**
     * 注册
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        return ApiResponse::success($this->auth->register($request->validated(), $request), 'ok', [], 201);
    }

    /**
     * 登录
     */
    public function login(LoginRequest $request): JsonResponse
    {
        return ApiResponse::success($this->auth->login($request->validated(), $request));
    }

    /**
     * 退出登录
     */
    public function logout(Request $request): JsonResponse
    {
        $this->auth->logout($request);

        return ApiResponse::success();
    }

    /**
     * 返回当前用户或成员
     */
    public function me(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        return ApiResponse::success($this->auth->present($user));
    }
}

