<?php

/**
 * 审批意见校验
 *
 * @package     App\Modules\Approval\Presentation\Http\Requests
 * @license     MIT License (MIT)
 * @author      Sunny Yang <githup@163.com> QQ4873473
 * @copyright   Copyright (c) 2024 YourCompany. All rights reserved.
 * @link        http://www.budff.com
 */

namespace App\Modules\Approval\Presentation\Http\Requests;

/**
 * 审批意见校验
 */
class ActionRequest extends ApprovalRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $required = $this->is('*/comment') ? 'required' : 'nullable';

        return [
            'comment' => [$required, 'string', 'max:500'],
        ];
    }
}
