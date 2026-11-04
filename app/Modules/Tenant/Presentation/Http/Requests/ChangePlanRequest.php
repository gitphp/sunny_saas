<?php

/**
 * 更换套餐参数校验
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
 * 更换套餐参数校验
 */
class ChangePlanRequest extends FormRequest
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

        if (is_string($this->input('code'))) {
            $this->merge(['code' => strtolower(trim((string) $this->input('code')))]);
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
            'code' => ['required', 'string', 'max:32'],
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
            'code.required' => '套餐编码不能为空',
        ];
    }
}
