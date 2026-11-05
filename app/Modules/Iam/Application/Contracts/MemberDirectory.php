<?php

/**
 * 当前企业的有效成员目录
 *
 * @package     App\Modules\Iam\Application\Contracts
 * @license     MIT License (MIT)
 * @author      Sunny Yang <githup@163.com> QQ4873473
 * @copyright   Copyright (c) 2024 YourCompany. All rights reserved.
 * @link        http://www.budff.com
 */

namespace App\Modules\Iam\Application\Contracts;

/**
 * 当前企业的有效成员目录
 *
 * 审批选人只依赖这份契约，不引用用户或成员模型。
 */
interface MemberDirectory
{
    /**
     * 列出当前租户里状态有效的成员
     *
     * @return list<array{user_id: int, name: string, email: string}>
     */
    public function listActive(): array;

    /**
     * 判断用户是不是当前租户的有效成员
     */
    public function isActive(int $userId): bool;
}
