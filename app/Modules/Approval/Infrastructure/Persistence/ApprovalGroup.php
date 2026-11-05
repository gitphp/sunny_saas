<?php

/**
 * 流程分组
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
 * 流程分组
 */
class ApprovalGroup extends Model
{
    use BelongsToTenant, SoftDeletes;

    protected $table = 'approval_groups';

    protected $fillable = ['name', 'sort', 'status'];

    /**
     * 声明字段类型
     */
    protected function casts(): array
    {
        return [
            'tenant_id' => 'integer',
            'sort' => 'integer',
            'status' => 'integer',
        ];
    }
}
