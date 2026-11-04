<?php

/**
 * 办理入职校验
 *
 * @package     App\Modules\Hr\Presentation\Http\Requests
 * @license     MIT License (MIT)
 * @author      Sunny Yang <githup@163.com> QQ4873473
 * @copyright   Copyright (c) 2024 YourCompany. All rights reserved.
 * @link        http://www.budff.com
 */

namespace App\Modules\Hr\Presentation\Http\Requests;

/**
 * 办理入职校验
 */
class OnboardRequest extends HrRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'hired_on' => ['nullable', 'date'],
        ];
    }
}
