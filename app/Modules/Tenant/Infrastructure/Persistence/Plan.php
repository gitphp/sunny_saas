<?php

/**
 * 全局套餐模型
 *
 * @package     App\Modules\Tenant\Infrastructure\Persistence
 * @license     MIT License (MIT)
 * @author      Sunny Yang <githup@163.com> QQ4873473
 * @copyright   Copyright (c) 2024 YourCompany. All rights reserved.
 * @link        http://www.budff.com
 */

namespace App\Modules\Tenant\Infrastructure\Persistence;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 全局套餐模型
 */
class Plan extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'description',
        'member_limit',
        'status',
        'sort',
    ];

    /**
     * 声明字段类型
     */
    protected function casts(): array
    {
        return [
            'member_limit' => 'integer',
            'status' => 'integer',
            'sort' => 'integer',
        ];
    }

    /**
     * 套餐包含的模块
     */
    public function modules(): HasMany
    {
        return $this->hasMany(PlanModule::class);
    }
}
