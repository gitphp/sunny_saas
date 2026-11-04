<?php

/**
 * 租户角色模型
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
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 租户角色模型
 */
class Role extends Model
{
    use BelongsToTenant, SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'data_scope',
    ];

    /**
     * 返回需要写入权限表的目录
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'role_permissions', 'role_id', 'permission_id')
            ->wherePivotNull('deleted_at');
    }

    /**
     * 关联自定义数据范围的部门
     */
    public function departments(): BelongsToMany
    {
        return $this->belongsToMany(Department::class, 'role_departments', 'role_id', 'department_id')
            ->wherePivotNull('deleted_at');
    }
}

