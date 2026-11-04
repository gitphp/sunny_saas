<?php

/**
 * 部门模型
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
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 部门模型
 */
class Department extends Model
{
    use BelongsToTenant, SoftDeletes;

    protected $fillable = [
        'parent_id',
        'name',
        'path',
        'sort',
    ];

    /**
     * 声明字段类型
     */
    protected function casts(): array
    {
        return [
            'tenant_id' => 'integer',
            'parent_id' => 'integer',
            'sort' => 'integer',
        ];
    }

    /**
     * 关联上级权限
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * 关联下级权限
     */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }
}

