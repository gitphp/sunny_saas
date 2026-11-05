<?php

/**
 * 流程定义保存校验
 *
 * @package     App\Modules\Approval\Presentation\Http\Requests
 * @license     MIT License (MIT)
 * @author      Sunny Yang <githup@163.com> QQ4873473
 * @copyright   Copyright (c) 2024 YourCompany. All rights reserved.
 * @link        http://www.budff.com
 */

namespace App\Modules\Approval\Presentation\Http\Requests;

/**
 * 流程定义保存校验
 */
class DefinitionRequest extends ApprovalRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $required = $this->isMethod('POST') ? 'required' : 'sometimes';

        return [
            'code' => [$required, 'string', 'max:32'],
            'name' => [$required, 'string', 'max:100'],
            'icon' => ['nullable', 'string', 'max:32'],
            'description' => ['nullable', 'string', 'max:255'],
            'group_id' => ['nullable', 'integer', 'min:1'],
            'status' => ['sometimes', 'integer', 'in:0,1'],
            'allow_revoke' => ['sometimes', 'integer', 'in:0,1'],
            'form_schema' => ['sometimes', 'array'],
            'flow_schema' => ['sometimes', 'array'],
        ];
    }
}
