<?php

/**
 * 注册登录与当前用户
 *
 * @package     App\Modules\Iam\Application\Auth
 * @license     MIT License (MIT)
 * @author      Sunny Yang <githup@163.com> QQ4873473
 * @copyright   Copyright (c) 2024 YourCompany. All rights reserved.
 * @link        http://www.budff.com
 */

namespace App\Modules\Iam\Application\Auth;

use App\Modules\Iam\Infrastructure\Persistence\User;
use App\Shared\Exceptions\BusinessException;
use App\Shared\Exceptions\ErrorCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

/**
 * 注册登录与当前用户
 */
class AuthService
{
    /**
     * 注册
     *
     * @param  array{name: string, email: string, password: string, mobile?: string|null}  $input
     * @return array<string, mixed>
     */
    public function register(array $input, Request $request): array
    {
        $user = User::query()->create([
            'name' => $input['name'],
            'email' => $input['email'],
            'mobile' => $input['mobile'] ?? null,
            'password' => $input['password'],
            'status' => 1,
        ]);

        Auth::guard('web')->login($user);

        if ($request->hasSession()) {
            $request->session()->regenerate();
        }

        return ['user' => $this->present($user)];
    }

    /**
     * 登录
     *
     * @param  array{email: string, password: string, device_name?: string|null}  $input
     * @return array<string, mixed>
     */
    public function login(array $input, Request $request): array
    {
        $ok = Auth::guard('web')->attempt([
            'email' => $input['email'],
            'password' => $input['password'],
        ]);

        if (! $ok) {
            throw new BusinessException(ErrorCode::INVALID_CREDENTIALS, '账号或密码错误', 401);
        }

        /** @var User $user */
        $user = Auth::guard('web')->user();

        if ((int) $user->status !== 1) {
            Auth::guard('web')->logout();

            throw new BusinessException(ErrorCode::INVALID_CREDENTIALS, '账号已停用', 401);
        }

        if ($request->hasSession()) {
            $request->session()->regenerate();
        }

        $token = null;

        if (! empty($input['device_name'])) {
            $token = $user->createToken((string) $input['device_name'])->plainTextToken;
        }

        return [
            'user' => $this->present($user),
            'token' => $token,
        ];
    }

    /**
     * 退出登录
     */
    public function logout(Request $request): void
    {
        $user = $request->user();
        $token = $user?->currentAccessToken();

        if ($token !== null && method_exists($token, 'delete')) {
            $token->delete();
        }

        Auth::guard('web')->logout();

        if ($request->hasSession()) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }
    }

    /**
     * 把模型整理成接口数据
     *
     * @return array<string, mixed>
     */
    public function present(User $user): array
    {
        return [
            'id' => (int) $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'mobile' => $user->mobile,
            'status' => (int) $user->status,
        ];
    }

    /**
     * 保证邮箱尚未被占用
     */
    public function assertUniqueEmail(string $email): void
    {
        if (User::withTrashed()->where('email', $email)->exists()) {
            throw ValidationException::withMessages([
                'email' => ['邮箱已被使用'],
            ]);
        }
    }
}

