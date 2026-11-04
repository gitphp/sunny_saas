<?php

/**
 * 成员角色关联模型
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
 * 成员角色关联模型
 */
class TenantUserRole extends Model
{
    use BelongsToTenant, SoftDeletes;

    protected $fillable = [
        'tenant_user_id',
        'role_id',
    ];
}

