<?php

/**
 * 从请求头解析当前租户
 *
 * @package     App\Modules\Tenant\Infrastructure\Tenancy
 * @license     MIT License (MIT)
 * @author      Sunny Yang <githup@163.com> QQ4873473
 * @copyright   Copyright (c) 2024 YourCompany. All rights reserved.
 * @link        http://www.budff.com
 */

namespace App\Modules\Tenant\Infrastructure\Tenancy;

use App\Modules\Iam\Application\Contracts\MembershipGateway;
use App\Modules\Tenant\Infrastructure\Persistence\Tenant;
use App\Shared\Exceptions\BusinessException;
use App\Shared\Exceptions\ErrorCode;
use App\Shared\Tenancy\ResolvedTenant;
use App\Shared\Tenancy\TenantResolver;

/**
 * 从请求头解析当前租户
 */
class HeaderTenantResolver implements TenantResolver
{
    /**
     * 注入依赖
     */
    public function __construct(private readonly MembershipGateway $members) {}

    /**
     * 按请求头解析租户并核对成员身份
     */
    public function resolve(string $slug, int $userId): ResolvedTenant
    {
        $tenant = Tenant::query()->where('slug', $slug)->first();

        if ($tenant === null || (int) $tenant->status !== 1) {
            throw new BusinessException(ErrorCode::NOT_FOUND, '租户不存在', 404);
        }

        $member = $this->members->findActiveMember((int) $tenant->id, $userId);

        // 租户存在但当前用户不是成员时，按无权限处理，不暴露租户内容。
        if ($member === null) {
            throw new BusinessException(ErrorCode::NOT_MEMBER, '不是该租户成员', 403);
        }

        return new ResolvedTenant(
            (int) $tenant->id,
            (string) $tenant->slug,
            $member['is_owner'],
            $member['member_id'],
        );
    }
}

