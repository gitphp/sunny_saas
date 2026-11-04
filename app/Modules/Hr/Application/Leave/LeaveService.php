<?php

/**
 * 请假申请与审批
 *
 * @package     App\Modules\Hr\Application\Leave
 * @license     MIT License (MIT)
 * @author      Sunny Yang <githup@163.com> QQ4873473
 * @copyright   Copyright (c) 2024 YourCompany. All rights reserved.
 * @link        http://www.budff.com
 */

namespace App\Modules\Hr\Application\Leave;

use App\Modules\Hr\Application\Employee\EmployeeService;
use App\Modules\Hr\Application\Support\HrVisibility;
use App\Modules\Hr\Domain\EmployeeStatus;
use App\Modules\Hr\Infrastructure\Persistence\LeaveRequest;
use App\Shared\Exceptions\BusinessException;
use App\Shared\Exceptions\ErrorCode;

/**
 * 请假申请与审批
 */
class LeaveService
{
    /**
     * 注入依赖
     */
    public function __construct(
        private readonly EmployeeService $employees,
        private readonly HrVisibility $visibility,
    ) {}

    /**
     * @return list<array<string, mixed>>
     */
    public function list(int $userId): array
    {
        $query = LeaveRequest::query()->with('employee')->orderByDesc('id');
        $query->whereIn('employee_id', $this->visibility->visibleEmployeeIds($userId));

        return $query->get()->map(fn (LeaveRequest $leave): array => $this->present($leave))->all();
    }

    /**
     * 在职员工提交请假
     *
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    public function store(int $userId, array $input): array
    {
        $employee = $this->employees->requireStatus(
            (int) $input['employee_id'],
            $userId,
            EmployeeStatus::ACTIVE,
            '只有在职员工可以请假',
        );

        if ($input['end_on'] < $input['start_on']) {
            throw new BusinessException(ErrorCode::VALIDATION, '请假结束日期不能早于开始日期', 422);
        }

        $leave = LeaveRequest::query()->create([
            'employee_id' => $employee->id,
            'leave_type' => (string) $input['leave_type'],
            'start_on' => $input['start_on'],
            'end_on' => $input['end_on'],
            'reason' => (string) ($input['reason'] ?? ''),
            'status' => 0,
            'reviewer_user_id' => 0,
            'reviewed_at' => null,
        ]);

        return $this->present($leave->load('employee'));
    }

    /**
     * 批准或驳回待审批请假
     *
     * @return array<string, mixed>
     */
    public function review(int $id, int $userId, int $status): array
    {
        $leave = $this->findVisible($id, $userId);

        if ((int) $leave->status !== 0) {
            throw new BusinessException(ErrorCode::VALIDATION, '该请假已审批', 422);
        }

        $leave->status = $status;
        $leave->reviewer_user_id = $userId;
        $leave->reviewed_at = now();
        $leave->save();

        return $this->present($leave->load('employee'));
    }

    private function findVisible(int $id, int $userId): LeaveRequest
    {
        $query = LeaveRequest::query()->whereKey($id)->whereIn('employee_id', $this->visibility->visibleEmployeeIds($userId));
        $leave = $query->first();

        if ($leave === null) {
            throw new BusinessException(ErrorCode::NOT_FOUND, '请假不存在', 404);
        }

        return $leave;
    }

    /**
     * @return array<string, mixed>
     */
    private function present(LeaveRequest $leave): array
    {
        return [
            'id' => (int) $leave->id,
            'employee_id' => (int) $leave->employee_id,
            'employee_name' => $leave->employee?->name ?? '',
            'leave_type' => (string) $leave->leave_type,
            'start_on' => $leave->start_on?->toDateString(),
            'end_on' => $leave->end_on?->toDateString(),
            'reason' => (string) $leave->reason,
            'status' => (int) $leave->status,
            'reviewed_at' => $leave->reviewed_at?->toIso8601String(),
        ];
    }
}
