<?php

/**
 * 套餐查询的持久化实现
 *
 * @package     App\Modules\Tenant\Infrastructure\Persistence
 * @license     MIT License (MIT)
 * @author      Sunny Yang <githup@163.com> QQ4873473
 * @copyright   Copyright (c) 2024 YourCompany. All rights reserved.
 * @link        http://www.budff.com
 */

namespace App\Modules\Tenant\Infrastructure\Persistence;

use App\Modules\Tenant\Application\Contracts\TenantPlanQuery;
use App\Shared\Exceptions\BusinessException;
use App\Shared\Exceptions\ErrorCode;

/**
 * 套餐查询的持久化实现
 */
class EloquentTenantPlanQuery implements TenantPlanQuery
{
    /**
     * 列出可售套餐
     */
    public function catalog(): array
    {
        return Plan::query()
            ->with('modules')
            ->where('status', 1)
            ->orderBy('sort')
            ->orderBy('id')
            ->get()
            ->map(fn (Plan $plan): array => $this->present($plan))
            ->all();
    }

    /**
     * 读取指定租户当前套餐
     */
    public function forTenant(int $tenantId): array
    {
        $tenant = Tenant::query()->find($tenantId);

        if ($tenant === null) {
            throw new BusinessException(ErrorCode::NOT_FOUND, '租户不存在', 404);
        }

        $plan = Plan::query()->with('modules')->find($tenant->plan_id);

        if ($plan === null || (int) $plan->status !== 1) {
            throw new BusinessException(ErrorCode::VALIDATION, '租户套餐不可用', 422);
        }

        return $this->present($plan);
    }

    /**
     * 把套餐整理成接口数据
     *
     * @return array{code: string, name: string, description: string, member_limit: int, modules: list<string>}
     */
    private function present(Plan $plan): array
    {
        return [
            'code' => (string) $plan->code,
            'name' => (string) $plan->name,
            'description' => (string) $plan->description,
            'member_limit' => (int) $plan->member_limit,
            'modules' => $plan->modules
                ->pluck('module_code')
                ->map(fn ($code): string => (string) $code)
                ->values()
                ->all(),
        ];
    }
}
