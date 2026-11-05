<?php

/**
 * 审批任务与抄送
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
 * 审批任务与抄送
 */
class ApprovalTask extends Model
{
    use BelongsToTenant, SoftDeletes;

    protected $table = 'approval_tasks';

    protected $fillable = [
        'instance_id',
        'node_key',
        'node_name',
        'assignee_user_id',
        'task_type',
        'status',
        'acted_at',
    ];

    /**
     * 声明字段类型
     */
    protected function casts(): array
    {
        return [
            'tenant_id' => 'integer',
            'instance_id' => 'integer',
            'assignee_user_id' => 'integer',
            'status' => 'integer',
            'acted_at' => 'datetime',
        ];
    }
}
