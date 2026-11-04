<?php

/**
 * 成员身份查询端口
 *
 * @package     App\Modules\Iam\Application\Contracts
 * @license     MIT License (MIT)
 * @author      Sunny Yang <githup@163.com> QQ4873473
 * @copyright   Copyright (c) 2024 YourCompany. All rights reserved.
 * @link        http://www.budff.com
 */

namespace App\Modules\Iam\Application\Contracts;

/**
 * 成员身份查询端口
 */
interface MembershipGateway
{
    /**
     * 查找用户在租户内的有效成员
     *
     * @return array{member_id: int, user_id: int, is_owner: bool}|null
     */
    public function findActiveMember(int $tenantId, int $userId): ?array;

    /**
     * 列出用户加入的全部租户
     *
     * @return list<array{tenant_id: int, is_owner: bool, status: int}>
     */
    public function membershipsForUser(int $userId): array;
}

