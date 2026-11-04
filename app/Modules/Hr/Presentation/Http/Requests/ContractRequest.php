<?php

/**
 * 签订合同校验
 *
 * @package     App\Modules\Hr\Presentation\Http\Requests
 * @license     MIT License (MIT)
 * @author      Sunny Yang <githup@163.com> QQ4873473
 * @copyright   Copyright (c) 2024 YourCompany. All rights reserved.
 * @link        http://www.budff.com
 */

namespace App\Modules\Hr\Presentation\Http\Requests;

/**
 * 签订合同校验
 */
class ContractRequest extends HrRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'employee_id' => ['required', 'integer', 'min:1'],
            'contract_no' => ['required', 'string', 'max:64'],
            'type' => ['required', 'string', 'in:fixed,open,intern'],
            'start_on' => ['required', 'date'],
            'end_on' => ['nullable', 'date'],
            'signed_on' => ['required', 'date'],
        ];
    }
}
