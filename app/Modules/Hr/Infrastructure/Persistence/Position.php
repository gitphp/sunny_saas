<?php

/**
 * 岗位模型
 *
 * @package     App\Modules\Hr\Infrastructure\Persistence
 * @license     MIT License (MIT)
 * @author      Sunny Yang <githup@163.com> QQ4873473
 * @copyright   Copyright (c) 2024 YourCompany. All rights reserved.
 * @link        http://www.budff.com
 */

namespace App\Modules\Hr\Infrastructure\Persistence;

use App\Shared\Tenancy\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 岗位模型
 */
class Position extends Model
{
    use BelongsToTenant, SoftDeletes;

    protected $table = 'hr_positions';

    protected $fillable = [
        'department_id',
        'name',
        'code',
        'sort',
        'status',
    ];

    /**
     * 声明字段类型
     */
    protected function casts(): array
    {
        return [
            'tenant_id' => 'integer',
            'department_id' => 'integer',
            'sort' => 'integer',
            'status' => 'integer',
        ];
    }
}
