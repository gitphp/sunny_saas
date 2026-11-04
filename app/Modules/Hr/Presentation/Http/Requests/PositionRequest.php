<?php

/**
 * 岗位保存校验
 *
 * @package     App\Modules\Hr\Presentation\Http\Requests
 * @license     MIT License (MIT)
 * @author      Sunny Yang <githup@163.com> QQ4873473
 * @copyright   Copyright (c) 2024 YourCompany. All rights reserved.
 * @link        http://www.budff.com
 */

namespace App\Modules\Hr\Presentation\Http\Requests;

/**
 * 岗位保存校验
 */
class PositionRequest extends HrRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $required = $this->isMethod('POST') ? 'required' : 'sometimes';

        return [
            'name' => [$required, 'string', 'max:100'],
            'code' => [$required, 'string', 'max:64'],
            'department_id' => ['nullable', 'integer', 'min:1'],
            'sort' => ['nullable', 'integer', 'min:0'],
            'status' => ['sometimes', 'integer', 'in:0,1'],
        ];
    }
}
