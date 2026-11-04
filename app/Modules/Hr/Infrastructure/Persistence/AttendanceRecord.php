<?php

/**
 * 考勤记录模型
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
 * 考勤记录模型
 */
class AttendanceRecord extends Model
{
    use BelongsToTenant, SoftDeletes;

    protected $table = 'hr_attendance_records';

    protected $fillable = [
        'employee_id',
        'work_on',
        'check_in',
        'check_out',
        'status',
    ];

    /**
     * 声明字段类型
     */
    protected function casts(): array
    {
        return [
            'tenant_id' => 'integer',
            'employee_id' => 'integer',
            'work_on' => 'date:Y-m-d',
        ];
    }

    /**
     * 关联员工
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
