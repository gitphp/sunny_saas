<?php

/**
 * 流程定义
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
 * 流程定义
 */
class ApprovalDefinition extends Model
{
    use BelongsToTenant, SoftDeletes;

    protected $table = 'approval_definitions';

    protected $fillable = [
        'group_id',
        'code',
        'name',
        'icon',
        'description',
        'status',
        'allow_revoke',
        'current_version_id',
    ];

    /**
     * 声明字段类型
     */
    protected function casts(): array
    {
        return [
            'tenant_id' => 'integer',
            'group_id' => 'integer',
            'status' => 'integer',
            'allow_revoke' => 'integer',
            'current_version_id' => 'integer',
        ];
    }
}
