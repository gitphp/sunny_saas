<?php

/**
 * 发起审批校验
 *
 * @package     App\Modules\Approval\Presentation\Http\Requests
 * @license     MIT License (MIT)
 * @author      Sunny Yang <githup@163.com> QQ4873473
 * @copyright   Copyright (c) 2024 YourCompany. All rights reserved.
 * @link        http://www.budff.com
 */

namespace App\Modules\Approval\Presentation\Http\Requests;

/**
 * 发起审批校验
 */
class SubmitInstanceRequest extends ApprovalRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'definition_code' => ['required', 'string', 'max:32'],
            'title' => ['nullable', 'string', 'max:200'],
            'form_data' => ['required', 'array'],
            'source_module' => ['nullable', 'string', 'max:32'],
            'source_id' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
