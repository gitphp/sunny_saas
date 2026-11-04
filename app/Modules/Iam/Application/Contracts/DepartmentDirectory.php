<?php

/**
 * 向其他模块提供部门目录和数据范围
 *
 * @package     App\Modules\Iam\Application\Contracts
 * @license     MIT License (MIT)
 * @author      Sunny Yang <githup@163.com> QQ4873473
 * @copyright   Copyright (c) 2024 YourCompany. All rights reserved.
 * @link        http://www.budff.com
 */

namespace App\Modules\Iam\Application\Contracts;

/**
 * 向其他模块提供部门目录和数据范围
 */
interface DepartmentDirectory
{
    /**
     * 列出当前用户可见的部门
     *
     * @return list<array{id: int, parent_id: int|null, name: string, path: string, sort: int}>
     */
    public function listVisible(int $userId): array;

    /**
     * 部门属于当前租户，并且落在该用户的数据范围内
     */
    public function assignable(int $userId, int $departmentId): bool;

    /**
     * 当前用户合并后的数据范围
     *
     * @return array{unrestricted: bool, department_ids: list<int>, self_user_id: int|null}
     */
    public function scopeFor(int $userId): array;
}
