<?php

/**
 * 员工档案、入职与在职状态
 *
 * @package     App\Modules\Hr\Application\Employee
 * @license     MIT License (MIT)
 * @author      Sunny Yang <githup@163.com> QQ4873473
 * @copyright   Copyright (c) 2024 YourCompany. All rights reserved.
 * @link        http://www.budff.com
 */

namespace App\Modules\Hr\Application\Employee;

use App\Modules\Hr\Application\Support\HrVisibility;
use App\Modules\Hr\Domain\EmployeeStatus;
use App\Modules\Hr\Infrastructure\Persistence\Employee;
use App\Modules\Hr\Infrastructure\Persistence\Position;
use App\Modules\Iam\Application\Contracts\DepartmentDirectory;
use App\Shared\Exceptions\BusinessException;
use App\Shared\Exceptions\ErrorCode;

/**
 * 员工档案、入职与在职状态
 */
class EmployeeService
{
    /**
     * 注入依赖
     */
    public function __construct(
        private readonly HrVisibility $visibility,
        private readonly DepartmentDirectory $departments,
    ) {}

    /**
     * 按状态列出可见员工
     *
     * @return list<array<string, mixed>>
     */
    public function list(int $userId, ?int $status = null): array
    {
        $query = Employee::query()->with('position')->orderBy('id');
        $this->visibility->limitEmployees($query, $userId);

        if ($status !== null) {
            $query->where('status', $status);
        }

        $names = $this->names($userId);

        return $query->get()->map(fn (Employee $employee): array => $this->present($employee, $names))->all();
    }

    /**
     * 查看一名可见员工，范围外返回不存在
     *
     * @return array<string, mixed>
     */
    public function show(int $id, int $userId): array
    {
        return $this->present($this->findVisible($id, $userId), $this->names($userId));
    }

    /**
     * 建立待入职档案。状态不能由调用方指定。
     *
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    public function store(int $userId, array $input): array
    {
        $departmentId = $this->nullableId($input['department_id'] ?? null);
        $position = $this->positionFor($userId, $this->nullableId($input['position_id'] ?? null), $departmentId);
        $departmentId = $this->alignDepartment($userId, $departmentId, $position);
        $this->assertNumberAvailable((string) $input['employee_no'], null);

        $employee = Employee::query()->create([
            'employee_no' => (string) $input['employee_no'],
            'name' => (string) $input['name'],
            'gender' => $this->gender($input['gender'] ?? 0),
            'mobile' => $this->blankToNull($input['mobile'] ?? null),
            'email' => (string) ($input['email'] ?? ''),
            'department_id' => $departmentId,
            'position_id' => $position?->id,
            'user_id' => $this->nullableId($input['user_id'] ?? null),
            'status' => EmployeeStatus::PENDING,
            'hired_on' => null,
            'left_on' => null,
        ]);

        return $this->present($employee->load('position'), $this->names($userId));
    }

    /**
     * 更新尚未离职的档案
     *
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    public function update(int $id, int $userId, array $input): array
    {
        $employee = $this->findVisible($id, $userId);

        if ((int) $employee->status === EmployeeStatus::LEFT) {
            throw new BusinessException(ErrorCode::VALIDATION, '已离职员工不能修改档案', 422);
        }

        $departmentId = array_key_exists('department_id', $input)
            ? $this->nullableId($input['department_id'])
            : ($employee->department_id ? (int) $employee->department_id : null);
        $positionId = array_key_exists('position_id', $input)
            ? $this->nullableId($input['position_id'])
            : ($employee->position_id ? (int) $employee->position_id : null);
        $position = $this->positionFor($userId, $positionId, $departmentId);
        $employee->department_id = $this->alignDepartment($userId, $departmentId, $position);
        $employee->position_id = $position?->id;

        foreach (['employee_no', 'name', 'email'] as $field) {
            if (isset($input[$field])) {
                $employee->{$field} = (string) $input[$field];
            }
        }

        if (isset($input['employee_no'])) {
            $this->assertNumberAvailable((string) $input['employee_no'], $employee->id);
        }

        if (array_key_exists('gender', $input)) {
            $employee->gender = $this->gender($input['gender']);
        }

        if (array_key_exists('mobile', $input)) {
            $employee->mobile = $this->blankToNull($input['mobile']);
        }

        if (array_key_exists('user_id', $input)) {
            $employee->user_id = $this->nullableId($input['user_id']);
        }

        $employee->save();

        return $this->present($employee->load('position'), $this->names($userId));
    }

    /**
     * 把待入职员工转为在职
     *
     * @return array<string, mixed>
     */
    public function onboard(int $id, int $userId, ?string $hiredOn): array
    {
        $employee = $this->findVisible($id, $userId);

        if ((int) $employee->status !== EmployeeStatus::PENDING) {
            throw new BusinessException(ErrorCode::VALIDATION, '只有待入职员工可以办理入职', 422);
        }

        $employee->status = EmployeeStatus::ACTIVE;
        $employee->hired_on = $hiredOn ?: now()->toDateString();
        $employee->left_on = null;
        $employee->save();

        return $this->present($employee->load('position'), $this->names($userId));
    }

    /**
     * 办结离职时把员工标成离职
     */
    public function markLeft(Employee $employee, string $leftOn): void
    {
        $employee->status = EmployeeStatus::LEFT;
        $employee->left_on = $leftOn;
        $employee->save();
    }

    /**
     * 读取可见且处于指定状态的员工
     */
    public function requireStatus(int $id, int $userId, int $status, string $message): Employee
    {
        $employee = $this->findVisible($id, $userId);

        if ((int) $employee->status !== $status) {
            throw new BusinessException(ErrorCode::VALIDATION, $message, 422);
        }

        return $employee;
    }

    /**
     * 按数据范围查找，范围外与不存在使用同一个 404
     */
    public function findVisible(int $id, int $userId): Employee
    {
        $query = Employee::query()->with('position')->whereKey($id);
        $this->visibility->limitEmployees($query, $userId);
        $employee = $query->first();

        if ($employee === null) {
            throw new BusinessException(ErrorCode::NOT_FOUND, '员工不存在', 404);
        }

        return $employee;
    }

    /**
     * 岗位若绑定了部门，员工部门必须与它一致
     */
    private function positionFor(int $userId, ?int $positionId, ?int $departmentId): ?Position
    {
        if ($positionId === null) {
            return null;
        }

        $position = Position::query()->whereKey($positionId)->where('status', 1)->first();

        if ($position === null) {
            throw new BusinessException(ErrorCode::VALIDATION, '岗位不存在', 422);
        }

        if ($position->department_id) {
            $this->visibility->assertDepartment((int) $position->department_id, $userId);

            if ($departmentId !== null && (int) $position->department_id !== $departmentId) {
                throw new BusinessException(ErrorCode::VALIDATION, '岗位不属于所选部门', 422);
            }
        }

        return $position;
    }

    /**
     * 没选部门但岗位已挂部门时，沿用岗位上的部门编号
     */
    private function alignDepartment(int $userId, ?int $departmentId, ?Position $position): ?int
    {
        if ($departmentId === null && $position?->department_id) {
            $departmentId = (int) $position->department_id;
        }

        $this->visibility->assertDepartment($departmentId, $userId);

        return $departmentId;
    }

    /**
     * 工号在当前企业内唯一，含已删除档案
     */
    private function assertNumberAvailable(string $number, ?int $ignoreId): void
    {
        $query = Employee::withTrashed()->where('employee_no', $number);

        if ($ignoreId !== null) {
            $query->where('id', '!=', $ignoreId);
        }

        if ($query->exists()) {
            throw new BusinessException(ErrorCode::VALIDATION, '工号已存在', 422);
        }
    }

    /**
     * @return array<int, string>
     */
    private function names(int $userId): array
    {
        $map = [];

        foreach ($this->departments->listVisible($userId) as $department) {
            $map[$department['id']] = $department['name'];
        }

        return $map;
    }

    /**
     * @param  array<int, string>  $names
     * @return array<string, mixed>
     */
    private function present(Employee $employee, array $names): array
    {
        $departmentId = $employee->department_id ? (int) $employee->department_id : null;

        return [
            'id' => (int) $employee->id,
            'employee_no' => (string) $employee->employee_no,
            'name' => (string) $employee->name,
            'gender' => (int) $employee->gender,
            'mobile' => $employee->mobile,
            'email' => (string) $employee->email,
            'department_id' => $departmentId,
            'department_name' => $departmentId ? ($names[$departmentId] ?? '') : '',
            'position_id' => $employee->position_id ? (int) $employee->position_id : null,
            'position_name' => $employee->position?->name ?? '',
            'user_id' => $employee->user_id ? (int) $employee->user_id : null,
            'status' => (int) $employee->status,
            'hired_on' => $employee->hired_on?->toDateString(),
            'left_on' => $employee->left_on?->toDateString(),
        ];
    }

    private function nullableId(mixed $value): ?int
    {
        $id = (int) $value;

        return $id > 0 ? $id : null;
    }

    private function blankToNull(mixed $value): ?string
    {
        $text = trim((string) $value);

        return $text === '' ? null : $text;
    }

    private function gender(mixed $value): int
    {
        $gender = (int) $value;

        return in_array($gender, [0, 1, 2], true) ? $gender : 0;
    }
}
