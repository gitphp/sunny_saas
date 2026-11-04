<?php

/**
 * 租户模块开通模型
 *
 * @package     App\Modules\Iam\Infrastructure\Persistence
 * @license     MIT License (MIT)
 * @author      Sunny Yang <githup@163.com> QQ4873473
 * @copyright   Copyright (c) 2024 YourCompany. All rights reserved.
 * @link        http://www.budff.com
 */

namespace App\Modules\Iam\Infrastructure\Persistence;

use App\Shared\Tenancy\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 租户模块开通模型
 */
class TenantModule extends Model
{
    use BelongsToTenant, SoftDeletes;

    protected $fillable = [
        'module_code',
        'status',
        'enabled_at',
    ];

    /**
     * 声明字段类型
     */
    protected function casts(): array
    {
        return [
            'tenant_id' => 'integer',
            'status' => 'integer',
            'enabled_at' => 'datetime',
        ];
    }
}

