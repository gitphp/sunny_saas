<?php

/**
 * 流程版本
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
 * 流程版本
 *
 * 已发布的版本保持不变。进行中的审批单记录自己的 version_id。
 */
class ApprovalVersion extends Model
{
    use BelongsToTenant, SoftDeletes;

    protected $table = 'approval_versions';

    protected $fillable = [
        'definition_id',
        'version_no',
        'status',
        'form_schema',
        'flow_schema',
        'published_at',
    ];

    /**
     * 声明字段类型
     */
    protected function casts(): array
    {
        return [
            'tenant_id' => 'integer',
            'definition_id' => 'integer',
            'version_no' => 'integer',
            'status' => 'integer',
            'form_schema' => 'array',
            'flow_schema' => 'array',
            'published_at' => 'datetime',
        ];
    }
}
