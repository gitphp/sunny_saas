<?php

/**
 * 流程分组保存校验
 *
 * @package     App\Modules\Approval\Presentation\Http\Requests
 * @license     MIT License (MIT)
 * @author      Sunny Yang <githup@163.com> QQ4873473
 * @copyright   Copyright (c) 2024 YourCompany. All rights reserved.
 * @link        http://www.budff.com
 */

namespace App\Modules\Approval\Presentation\Http\Requests;

/**
 * 流程分组保存校验
 */
class GroupRequest extends ApprovalRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $required = $this->isMethod('POST') ? 'required' : 'sometimes';

        return [
            'name' => [$required, 'string', 'max:100'],
            'sort' => ['nullable', 'integer', 'min:0'],
            'status' => ['sometimes', 'integer', 'in:0,1'],
        ];
    }
}
