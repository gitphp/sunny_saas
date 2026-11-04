<?php

/**
 * 登录参数校验
 *
 * @package     App\Modules\Iam\Presentation\Http\Requests
 * @license     MIT License (MIT)
 * @author      Sunny Yang <githup@163.com> QQ4873473
 * @copyright   Copyright (c) 2024 YourCompany. All rights reserved.
 * @link        http://www.budff.com
 */

namespace App\Modules\Iam\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * 登录参数校验
 */
class LoginRequest extends FormRequest
{
    /**
     * 判断请求是否允许继续
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * 定义校验规则
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string', 'max:255'],
            'device_name' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * 定义校验失败时的中文提示
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'email.required' => '邮箱不能为空',
            'password.required' => '密码不能为空',
        ];
    }
}

