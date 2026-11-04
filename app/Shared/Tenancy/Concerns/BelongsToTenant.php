<?php

/**
 * 业务模型的租户隔离
 *
 * @package     App\Shared\Tenancy\Concerns
 * @license     MIT License (MIT)
 * @author      Sunny Yang <githup@163.com> QQ4873473
 * @copyright   Copyright (c) 2024 YourCompany. All rights reserved.
 * @link        http://www.budff.com
 */

namespace App\Shared\Tenancy\Concerns;

use App\Shared\Exceptions\MissingTenantContextException;
use App\Shared\Tenancy\TenantContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * 业务模型的租户隔离
 */
trait BelongsToTenant
{
    /**
     * 注册租户全局作用域，并在写入时强制使用上下文中的租户
     */
    public static function bootBelongsToTenant(): void
    {
        static::addGlobalScope('tenant', function (Builder $builder): void {
            $tenantId = app(TenantContext::class)->id();

            // 没有租户时禁止退回成全表查询。
            if ($tenantId === null || $tenantId <= 0) {
                throw new MissingTenantContextException(
                    $builder->getModel()->getTable().' 的查询缺少租户上下文'
                );
            }

            $builder->where($builder->getModel()->getTable().'.tenant_id', $tenantId);
        });

        static::creating(function (Model $model): void {
            $tenantId = app(TenantContext::class)->id();

            if ($tenantId === null || $tenantId <= 0) {
                throw new MissingTenantContextException(
                    $model->getTable().' 的写入缺少租户上下文'
                );
            }

            // 租户主键只取上下文，忽略调用方传入的值。
            $model->setAttribute('tenant_id', $tenantId);
        });

        static::updating(function (Model $model): void {
            $tenantId = app(TenantContext::class)->id();

            if ($tenantId === null || $tenantId <= 0) {
                throw new MissingTenantContextException(
                    $model->getTable().' 的更新缺少租户上下文'
                );
            }

            // 已存在的记录不允许被改到另一个租户。
            if ($model->isDirty('tenant_id')) {
                $model->setAttribute('tenant_id', $model->getOriginal('tenant_id'));
            }
        });
    }
}

