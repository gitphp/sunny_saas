<?php

/**
 * 员工档案保存校验
 *
 * @package     App\Modules\Hr\Presentation\Http\Requests
 * @license     MIT License (MIT)
 * @author      Sunny Yang <githup@163.com> QQ4873473
 * @copyright   Copyright (c) 2024 YourCompany. All rights reserved.
 * @link        http://www.budff.com
 */

namespace App\Modules\Hr\Presentation\Http\Requests;

/**
 * 员工档案保存校验
 */
class EmployeeRequest extends HrRequest
{
    /**
     * 空字符串按未填写处理
     */
    protected function prepareForValidation(): void
    {
        parent::prepareForValidation();
        $merge = [];

        foreach (['mobile', 'email', 'department_id', 'position_id', 'user_id'] as $field) {
            if ($this->input($field) === '') {
                $merge[$field] = null;
            }
        }

        if ($merge !== []) {
            $this->merge($merge);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $required = $this->isMethod('POST') ? 'required' : 'sometimes';

        return [
            'employee_no' => [$required, 'string', 'max:32'],
            'name' => [$required, 'string', 'max:100'],
            'gender' => ['nullable', 'integer', 'in:0,1,2'],
            'mobile' => ['nullable', 'string', 'max:32'],
            'email' => ['nullable', 'string', 'max:255'],
            'department_id' => ['nullable', 'integer', 'min:1'],
            'position_id' => ['nullable', 'integer', 'min:1'],
            'user_id' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
