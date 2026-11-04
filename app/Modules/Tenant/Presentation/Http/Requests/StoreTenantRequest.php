<?php

/**
 * 创建租户参数校验
 *
 * @package     App\Modules\Tenant\Presentation\Http\Requests
 * @license     MIT License (MIT)
 * @author      Sunny Yang <githup@163.com> QQ4873473
 * @copyright   Copyright (c) 2024 YourCompany. All rights reserved.
 * @link        http://www.budff.com
 */

namespace App\Modules\Tenant\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * 创建租户参数校验
 */
class StoreTenantRequest extends FormRequest
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

        if (is_string($this->input('slug'))) {
            $this->merge(['slug' => strtolower(trim((string) $this->input('slug')))]);
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
            'slug' => ['required', 'string', 'max:64', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', 'unique:tenants,slug'],
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
            'name.required' => '租户名称不能为空',
            'slug.required' => '租户标识不能为空',
            'slug.regex' => '租户标识只能使用小写字母、数字和中划线',
            'slug.unique' => '租户标识已被使用',
        ];
    }
}

