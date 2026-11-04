<?php

/**
 * 更换当前租户的套餐
 *
 * @package     App\Modules\Tenant\Application
 * @license     MIT License (MIT)
 * @author      Sunny Yang <githup@163.com> QQ4873473
 * @copyright   Copyright (c) 2024 YourCompany. All rights reserved.
 * @link        http://www.budff.com
 */

namespace App\Modules\Tenant\Application;

use App\Modules\Iam\Application\Contracts\MembershipGateway;
use App\Modules\Iam\Application\Contracts\ModuleGrantSync;
use App\Modules\Tenant\Application\Contracts\TenantPlanQuery;
use App\Modules\Tenant\Infrastructure\Persistence\Plan;
use App\Modules\Tenant\Infrastructure\Persistence\Tenant;
use App\Shared\Exceptions\BusinessException;
use App\Shared\Exceptions\ErrorCode;
use App\Shared\Tenancy\TenantContext;

/**
 * 更换当前租户的套餐
 */
class ChangeTenantPlan
{
    /**
     * 注入依赖
     */
    public function __construct(
        private readonly TenantContext $context,
        private readonly MembershipGateway $members,
        private readonly ModuleGrantSync $grants,
        private readonly TenantPlanQuery $plans,
    ) {}

    /**
     * 换成指定套餐，并关掉套餐外已经开通的模块
     *
     * @return array{code: string, name: string, description: string, member_limit: int, modules: list<string>}
     */
    public function handle(string $code): array
    {
        $plan = Plan::query()->with('modules')->where('code', $code)->where('status', 1)->first();

        if ($plan === null) {
            throw new BusinessException(ErrorCode::NOT_FOUND, '套餐不存在', 404);
        }

        $tenantId = (int) $this->context->id();
        $activeMembers = $this->members->activeMemberCount($tenantId);

        // 人数已经超过目标套餐时不允许降级，避免换完后现有成员无法解释。
        if ((int) $plan->member_limit > 0 && $activeMembers > (int) $plan->member_limit) {
            throw new BusinessException(ErrorCode::VALIDATION, '当前有效成员数超过该套餐上限', 422);
        }

        $tenant = Tenant::query()->find($tenantId);

        if ($tenant === null) {
            throw new BusinessException(ErrorCode::NOT_FOUND, '租户不存在', 404);
        }

        $tenant->plan_id = (int) $plan->id;
        $tenant->save();

        $modules = $plan->modules
            ->pluck('module_code')
            ->map(fn ($moduleCode): string => (string) $moduleCode)
            ->all();

        // 换套餐后立刻收掉不再包含的模块，避免套餐外功能继续可用。
        $this->grants->retainOnly($modules);

        return $this->plans->forTenant($tenantId);
    }
}
