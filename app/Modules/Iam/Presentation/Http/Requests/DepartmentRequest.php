<?php

/**
 * 部门保存校验
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
 * 部门保存校验
 */
class DepartmentRequest extends FormRequest
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
    }

    /**
     * 定义校验规则
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $required = $this->isMethod('POST') ? 'required' : 'sometimes';

        return [
            'name' => [$required, 'string', 'max:100'],
            'parent_id' => ['nullable', 'integer', 'min:1'],
            'sort' => ['nullable', 'integer', 'min:0'],
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
            'name.required' => '部门名称不能为空',
        ];
    }
}

