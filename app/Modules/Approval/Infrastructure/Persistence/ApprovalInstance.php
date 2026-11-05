<?php

/**
 * 审批单
 *
 * @package     App\Modules\Approval\Infrastructure\Persistence
 * @license     MIT License (MIT)
 * @author      Sunny Yang <githup@163.com> QQ4873473
 * @copyright   Copyright (c) 2024 YourCompany. All rights reserved.
 * @link        http://www.budff.com
 */

namespace App\Modules\Approval\Infrastructure\Persistence;

use App\Shared\Tenancy\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 审批单
 */
class ApprovalInstance extends Model
{
    use BelongsToTenant, SoftDeletes;

    protected $table = 'approval_instances';

    protected $fillable = [
        'definition_id',
        'version_id',
        'serial_no',
        'title',
        'applicant_user_id',
        'status',
        'form_data',
        'current_node_key',
        'source_module',
        'source_id',
        'submitted_at',
        'finished_at',
    ];

    /**
     * 声明字段类型
     */
    protected function casts(): array
    {
        return [
            'tenant_id' => 'integer',
            'definition_id' => 'integer',
            'version_id' => 'integer',
            'applicant_user_id' => 'integer',
            'status' => 'integer',
            'form_data' => 'array',
            'source_id' => 'integer',
            'submitted_at' => 'datetime',
            'finished_at' => 'datetime',
        ];
    }
}
