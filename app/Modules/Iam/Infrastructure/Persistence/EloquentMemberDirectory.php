<?php

/**
 * 成员目录契约的持久化实现
 *
 * @package     App\Modules\Iam\Infrastructure\Persistence
 * @license     MIT License (MIT)
 * @author      Sunny Yang <githup@163.com> QQ4873473
 * @copyright   Copyright (c) 2024 YourCompany. All rights reserved.
 * @link        http://www.budff.com
 */

namespace App\Modules\Iam\Infrastructure\Persistence;

use App\Modules\Iam\Application\Contracts\MemberDirectory;

/**
 * 成员目录契约的持久化实现
 */
class EloquentMemberDirectory implements MemberDirectory
{
    /**
     * 列出当前租户里状态有效的成员
     *
     * 成员模型自带租户范围，这里不再手写 tenant_id。
     *
     * @return list<array{user_id: int, name: string, email: string}>
     */
    public function listActive(): array
    {
        return TenantUser::query()
            ->with('user')
            ->where('status', 1)
            ->orderBy('id')
            ->get()
            ->map(fn (TenantUser $member): array => [
                'user_id' => (int) $member->user_id,
                'name' => (string) ($member->user?->name ?? ''),
                'email' => (string) ($member->user?->email ?? ''),
            ])
            ->values()
            ->all();
    }

    /**
     * 判断用户是不是当前租户的有效成员
     */
    public function isActive(int $userId): bool
    {
        return TenantUser::query()->where('user_id', $userId)->where('status', 1)->exists();
    }
}
