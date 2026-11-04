<?php

/**
 * 套餐查询端口，供其他模块判断成员上限和可开通模块
 *
 * @package     App\Modules\Tenant\Application\Contracts
 * @license     MIT License (MIT)
 * @author      Sunny Yang <githup@163.com> QQ4873473
 * @copyright   Copyright (c) 2024 YourCompany. All rights reserved.
 * @link        http://www.budff.com
 */

namespace App\Modules\Tenant\Application\Contracts;

/**
 * 套餐查询端口，供其他模块判断成员上限和可开通模块
 */
interface TenantPlanQuery
{
    /**
     * 列出可售套餐
     *
     * @return list<array{code: string, name: string, description: string, member_limit: int, modules: list<string>}>
     */
    public function catalog(): array;

    /**
     * 读取指定租户当前套餐
     *
     * @return array{code: string, name: string, description: string, member_limit: int, modules: list<string>}
     */
    public function forTenant(int $tenantId): array;
}
