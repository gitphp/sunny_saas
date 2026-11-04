<?php

/**
 * 租户成员模型
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
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 租户成员模型
 */
class TenantUser extends Model
{
    use BelongsToTenant, SoftDeletes;

    protected $fillable = [
        'user_id',
        'department_id',
        'status',
        'is_owner',
        'joined_at',
    ];

    /**
     * 声明字段类型
     */
    protected function casts(): array
    {
        return [
            'tenant_id' => 'integer',
            'user_id' => 'integer',
            'department_id' => 'integer',
            'status' => 'integer',
            'is_owner' => 'integer',
            'joined_at' => 'datetime',
        ];
    }

    /**
     * 关联全局账号
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 关联所属部门
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * 关联已分配角色
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'tenant_user_roles', 'tenant_user_id', 'role_id')
            ->wherePivotNull('deleted_at');
    }
}

