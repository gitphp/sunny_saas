<?php

/**
 * 成员角色分配校验
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
 * 成员角色分配校验
 */
class SyncMemberRolesRequest extends FormRequest
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
        return [
            'role_ids' => ['present', 'array'],
            'role_ids.*' => ['integer', 'min:1'],
        ];
    }
}

