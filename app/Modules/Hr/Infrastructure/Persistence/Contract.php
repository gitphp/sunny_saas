<?php

/**
 * 劳动合同模型
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
 * 劳动合同模型
 */
class Contract extends Model
{
    use BelongsToTenant, SoftDeletes;

    protected $table = 'hr_contracts';

    protected $fillable = [
        'employee_id',
        'contract_no',
        'type',
        'start_on',
        'end_on',
        'status',
        'signed_on',
    ];

    /**
     * 声明字段类型
     */
    protected function casts(): array
    {
        return [
            'tenant_id' => 'integer',
            'employee_id' => 'integer',
            'status' => 'integer',
            'start_on' => 'date:Y-m-d',
            'end_on' => 'date:Y-m-d',
            'signed_on' => 'date:Y-m-d',
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
