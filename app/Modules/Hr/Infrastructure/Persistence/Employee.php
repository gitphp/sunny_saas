<?php

/**
 * 员工档案模型
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
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 员工档案模型
 */
class Employee extends Model
{
    use BelongsToTenant, SoftDeletes;

    protected $table = 'hr_employees';

    protected $fillable = [
        'employee_no',
        'name',
        'gender',
        'mobile',
        'email',
        'department_id',
        'position_id',
        'user_id',
        'status',
        'hired_on',
        'left_on',
    ];

    /**
     * 声明字段类型
     */
    protected function casts(): array
    {
        return [
            'tenant_id' => 'integer',
            'gender' => 'integer',
            'department_id' => 'integer',
            'position_id' => 'integer',
            'user_id' => 'integer',
            'status' => 'integer',
            'hired_on' => 'date:Y-m-d',
            'left_on' => 'date:Y-m-d',
        ];
    }

    /**
     * 关联岗位
     */
    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }
}
