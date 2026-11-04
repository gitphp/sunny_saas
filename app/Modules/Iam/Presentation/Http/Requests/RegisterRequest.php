<?php

/**
 * 注册参数校验
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
 * 注册参数校验
 */
class RegisterRequest extends FormRequest
{
    /**
     * 判断请求是否允许继续
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * 校验前去掉请求体中的租户主键，租户只来自上下文
     */
    protected function prepareForValidation(): void
    {
        if ($this->input('mobile') === '') {
            $this->merge(['mobile' => null]);
        }
    }

    /**
     * 定义校验规则
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'mobile' => ['nullable', 'string', 'max:32', 'unique:users,mobile'],
            'password' => ['required', 'string', 'min:8', 'max:255', 'confirmed'],
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
            'name.required' => '姓名不能为空',
            'email.required' => '邮箱不能为空',
            'email.email' => '邮箱格式不正确',
            'email.unique' => '邮箱已被使用',
            'password.required' => '密码不能为空',
            'password.min' => '密码至少 8 位',
            'password.confirmed' => '两次密码不一致',
        ];
    }
}

