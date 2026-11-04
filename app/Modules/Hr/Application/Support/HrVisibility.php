<?php

/**
 * 按部门数据范围限制人事查询
 *
 * @package     App\Modules\Hr\Application\Support
 * @license     MIT License (MIT)
 * @author      Sunny Yang <githup@163.com> QQ4873473
 * @copyright   Copyright (c) 2024 YourCompany. All rights reserved.
 * @link        http://www.budff.com
 */

namespace App\Modules\Hr\Application\Support;

use App\Modules\Hr\Infrastructure\Persistence\Employee;
use App\Modules\Iam\Application\Contracts\DepartmentDirectory;
use App\Shared\Exceptions\BusinessException;
use App\Shared\Exceptions\ErrorCode;
use Illuminate\Database\Eloquent\Builder;

/**
 * 按部门数据范围限制人事查询
 */
class HrVisibility
{
    /**
     * 注入依赖
     */
    public function __construct(private readonly DepartmentDirectory $departments) {}

    /**
     * 只保留当前用户可见部门里的员工，以及范围包含本人时绑定到该账号的员工
     */
    public function limitEmployees(Builder $query, int $userId): void
    {
        $scope = $this->departments->scopeFor($userId);

        if ($scope['unrestricted']) {
            return;
        }

        // 范围来自身份模块契约。空范围时查询必须零行。
        $query->where(function (Builder $inner) use ($scope): void {
            $applied = false;

            if ($scope['department_ids'] !== []) {
                $inner->whereIn('department_id', $scope['department_ids']);
                $applied = true;
            }

            if ($scope['self_user_id'] !== null) {
                $applied
                    ? $inner->orWhere('user_id', $scope['self_user_id'])
                    : $inner->where('user_id', $scope['self_user_id']);
                $applied = true;
            }

            if (! $applied) {
                $inner->whereRaw('1 = 0');
            }
        });
    }

    /**
     * 当前用户能看到的员工主键。空数组表示一个都看不到。
     *
     * @return list<int>
     */
    public function visibleEmployeeIds(int $userId): array
    {
        $query = Employee::query();
        $this->limitEmployees($query, $userId);

        return $query->pluck('id')->map(fn ($id): int => (int) $id)->all();
    }

    /**
     * 部门编号必须能在当前租户的可见范围内找到
     */
    public function assertDepartment(?int $departmentId, int $userId): void
    {
        if ($departmentId === null || $departmentId <= 0) {
            return;
        }

        if (! $this->departments->assignable($userId, $departmentId)) {
            throw new BusinessException(ErrorCode::VALIDATION, '部门不存在或不在可见范围', 422);
        }
    }
}
