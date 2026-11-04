<?php

/**
 * 人事请求的公共校验
 *
 * @package     App\Modules\Hr\Presentation\Http\Requests
 * @license     MIT License (MIT)
 * @author      Sunny Yang <githup@163.com> QQ4873473
 * @copyright   Copyright (c) 2024 YourCompany. All rights reserved.
 * @link        http://www.budff.com
 */

namespace App\Modules\Hr\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * 人事请求的公共校验
 */
abstract class HrRequest extends FormRequest
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
}
