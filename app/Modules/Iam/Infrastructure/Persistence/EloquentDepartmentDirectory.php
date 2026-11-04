<?php

/**
 * 部门目录契约的持久化实现
 *
 * @package     App\Modules\Iam\Infrastructure\Persistence
 * @license     MIT License (MIT)
 * @author      Sunny Yang <githup@163.com> QQ4873473
 * @copyright   Copyright (c) 2024 YourCompany. All rights reserved.
 * @link        http://www.budff.com
 */

namespace App\Modules\Iam\Infrastructure\Persistence;

use App\Modules\Iam\Application\Access\DataScopeResolver;
use App\Modules\Iam\Application\Contracts\DepartmentDirectory;

/**
 * 部门目录契约的持久化实现
 */
class EloquentDepartmentDirectory implements DepartmentDirectory
{
    /**
     * 注入依赖
     */
    public function __construct(private readonly DataScopeResolver $scopes) {}

    /**
     * 列出当前用户可见的部门
     */
    public function listVisible(int $userId): array
    {
        $query = Department::query()->orderBy('sort')->orderBy('id');
        $this->scopes->applyToDepartments($query, $userId);

        return $query->get()->map(fn (Department $department): array => [
            'id' => (int) $department->id,
            'parent_id' => $department->parent_id ? (int) $department->parent_id : null,
            'name' => (string) $department->name,
            'path' => (string) $department->path,
            'sort' => (int) $department->sort,
        ])->all();
    }

    /**
     * 部门属于当前租户，并且落在该用户的数据范围内
     */
    public function assignable(int $userId, int $departmentId): bool
    {
        // 全局作用域已经限定当前租户，其他企业的部门在这里不存在。
        if (! Department::query()->whereKey($departmentId)->exists()) {
            return false;
        }

        return $this->scopes->departmentVisible($departmentId, $userId);
    }

    /**
     * 当前用户合并后的数据范围
     */
    public function scopeFor(int $userId): array
    {
        return $this->scopes->forUser($userId);
    }
}
