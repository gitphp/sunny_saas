<?php

/**
 * 添加企业成员参数校验
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
 * 添加企业成员参数校验
 */
class StoreMemberRequest extends FormRequest
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
        $this->replace($this->except(['tenant_id']));

        if (is_string($this->input('email'))) {
            $this->merge(['email' => strtolower(trim((string) $this->input('email')))]);
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
            'email' => ['required', 'email', 'max:255'],
            'password' => ['nullable', 'string', 'min:8', 'max:255'],
            'department_id' => ['nullable', 'integer', 'min:1'],
            'role_ids' => ['array'],
            'role_ids.*' => ['integer', 'min:1'],
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
            'password.min' => '密码至少 8 位',
        ];
    }
}
