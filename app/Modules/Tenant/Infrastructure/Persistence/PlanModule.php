<?php

/**
 * 套餐包含的模块
 *
 * @package     App\Modules\Tenant\Infrastructure\Persistence
 * @license     MIT License (MIT)
 * @author      Sunny Yang <githup@163.com> QQ4873473
 * @copyright   Copyright (c) 2024 YourCompany. All rights reserved.
 * @link        http://www.budff.com
 */

namespace App\Modules\Tenant\Infrastructure\Persistence;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 套餐包含的模块
 */
class PlanModule extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'plan_id',
        'module_code',
    ];

    /**
     * 声明字段类型
     */
    protected function casts(): array
    {
        return [
            'plan_id' => 'integer',
        ];
    }
}
