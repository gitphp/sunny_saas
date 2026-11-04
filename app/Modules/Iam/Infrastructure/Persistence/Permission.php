<?php

/**
 * 全局权限模型
 *
 * @package     App\Modules\Iam\Infrastructure\Persistence
 * @license     MIT License (MIT)
 * @author      Sunny Yang <githup@163.com> QQ4873473
 * @copyright   Copyright (c) 2024 YourCompany. All rights reserved.
 * @link        http://www.budff.com
 */

namespace App\Modules\Iam\Infrastructure\Persistence;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 全局权限模型
 */
class Permission extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'parent_id',
        'type',
        'code',
        'name',
        'route',
        'method',
        'icon',
        'sort',
    ];

    /**
     * 声明字段类型
     */
    protected function casts(): array
    {
        return [
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

