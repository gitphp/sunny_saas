<?php

/**
 * 租户模块开通
 *
 * @package     App\Modules\Iam\Application\Module
 * @license     MIT License (MIT)
 * @author      Sunny Yang <githup@163.com> QQ4873473
 * @copyright   Copyright (c) 2024 YourCompany. All rights reserved.
 * @link        http://www.budff.com
 */

namespace App\Modules\Iam\Application\Module;

use App\Modules\Iam\Infrastructure\Persistence\Module;
use App\Modules\Iam\Infrastructure\Persistence\TenantModule;
use App\Modules\Tenant\Application\Contracts\TenantPlanQuery;
use App\Shared\Exceptions\BusinessException;
use App\Shared\Exceptions\ErrorCode;
use App\Shared\Tenancy\TenantContext;

/**
 * 租户模块开通
 */
class ModuleQueryService
{
    /**
     * 注入依赖
     */
    public function __construct(
        private readonly TenantPlanQuery $plans,
        private readonly TenantContext $context,
    ) {}

    /**
     * 列出已注册模块及当前租户的开通状态
     *
     * @return list<array<string, mixed>>
     */
    public function list(): array
    {
        $grants = TenantModule::query()->get()->keyBy('module_code');
        $included = $this->currentPlanModules();

        return Module::query()->orderBy('code')->get()->map(function (Module $module) use ($grants, $included): array {
            /** @var TenantModule|null $grant */
            $grant = $grants->get($module->code);

            return $this->present($module, $grant, in_array($module->code, $included, true));
        })->all();
    }

    /**
     * 更新模块开通状态
     *
     * @return array<string, mixed>
     */
    public function updateStatus(string $code, int $status): array
    {
        $module = Module::query()->where('code', $code)->first();

        if ($module === null) {
            throw new BusinessException(ErrorCode::NOT_FOUND, '模块不存在', 404);
        }

        // 关掉身份与访问后，当前控制台无法再进入。
        if ($module->code === 'iam' && $status !== 1) {
            throw new BusinessException(ErrorCode::VALIDATION, '身份与访问模块不能关闭', 422);
        }

        // 套餐未包含的模块不能开通，避免企业用到未购买的能力。
        if ($status === 1 && ! in_array($module->code, $this->currentPlanModules(), true)) {
            throw new BusinessException(ErrorCode::VALIDATION, '当前套餐不包含该模块', 422);
        }

        $grant = TenantModule::query()->firstOrNew(['module_code' => $module->code]);
        $grant->status = $status;

        if ($grant->enabled_at === null) {
            $grant->enabled_at = now();
        }

        $grant->save();

        return $this->present($module, $grant, in_array($module->code, $this->currentPlanModules(), true));
    }

    /**
     * 当前租户套餐包含的模块编码
     *
     * @return list<string>
     */
    private function currentPlanModules(): array
    {
        return $this->plans->forTenant((int) $this->context->id())['modules'];
    }

    /**
     * 把模型整理成接口数据
     *
     * @return array<string, mixed>
     */
    private function present(Module $module, ?TenantModule $grant, bool $included): array
    {
        return [
            'code' => $module->code,
            'name' => $module->name,
            'description' => $module->description,
            'status' => (int) ($grant?->status ?? 0),
            'enabled_at' => $grant?->enabled_at?->toIso8601String(),
            'required' => $module->code === 'iam',
            'included' => $included,
        ];
    }
}

