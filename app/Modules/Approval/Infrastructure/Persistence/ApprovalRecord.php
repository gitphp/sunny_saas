<?php

/**
 * 审批记录
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
 * 审批记录
 */
class ApprovalRecord extends Model
{
    use BelongsToTenant, SoftDeletes;

    protected $table = 'approval_records';

    protected $fillable = [
        'instance_id',
        'task_id',
        'node_key',
        'actor_user_id',
        'action',
        'comment',
    ];

    /**
     * 声明字段类型
     */
    protected function casts(): array
    {
        return [
            'tenant_id' => 'integer',
            'instance_id' => 'integer',
            'task_id' => 'integer',
            'actor_user_id' => 'integer',
        ];
    }
}
