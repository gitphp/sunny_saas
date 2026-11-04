<?php

/**
 * 成员身份的持久化实现
 *
 * @package     App\Modules\Iam\Infrastructure\Persistence
 * @license     MIT License (MIT)
 * @author      Sunny Yang <githup@163.com> QQ4873473
 * @copyright   Copyright (c) 2024 YourCompany. All rights reserved.
 * @link        http://www.budff.com
 */

namespace App\Modules\Iam\Infrastructure\Persistence;

use App\Modules\Iam\Application\Contracts\MembershipGateway;

/**
 * 成员身份的持久化实现
 */
class EloquentMembershipGateway implements MembershipGateway
{
    /**
     * 查找用户在租户内的有效成员
     */
    public function findActiveMember(int $tenantId, int $userId): ?array
    {
        $row = TenantUser::withoutGlobalScope('tenant')
            ->where('tenant_id', $tenantId)
            ->where('user_id', $userId)
            ->where('status', 1)
            ->first();

        if ($row === null) {
            return null;
        }

        return [
            'member_id' => (int) $row->id,
            'user_id' => (int) $row->user_id,
            'is_owner' => (int) $row->is_owner === 1,
        ];
    }

    /**
     * 列出用户加入的全部租户
     */
    public function membershipsForUser(int $userId): array
    {
        return TenantUser::withoutGlobalScope('tenant')
            ->where('user_id', $userId)
            ->where('status', 1)
            ->orderBy('id')
            ->get(['tenant_id', 'is_owner', 'status'])
            ->map(fn (TenantUser $row): array => [
                'tenant_id' => (int) $row->tenant_id,
                'is_owner' => (int) $row->is_owner === 1,
                'status' => (int) $row->status,
            ])
            ->all();
    }

    /**
     * 统计指定租户的有效成员数
     */
    public function activeMemberCount(int $tenantId): int
    {
        // 调用方给出租户主键，这里不用当前上下文，避免换套餐时数到别的企业。
        return TenantUser::withoutGlobalScope('tenant')
            ->where('tenant_id', $tenantId)
            ->where('status', 1)
            ->count();
    }
}

