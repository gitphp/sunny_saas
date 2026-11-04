<?php

/**
 * 考勤登记
 *
 * @package     App\Modules\Hr\Application\Attendance
 * @license     MIT License (MIT)
 * @author      Sunny Yang <githup@163.com> QQ4873473
 * @copyright   Copyright (c) 2024 YourCompany. All rights reserved.
 * @link        http://www.budff.com
 */

namespace App\Modules\Hr\Application\Attendance;

use App\Modules\Hr\Application\Employee\EmployeeService;
use App\Modules\Hr\Application\Support\HrVisibility;
use App\Modules\Hr\Domain\EmployeeStatus;
use App\Modules\Hr\Infrastructure\Persistence\AttendanceRecord;
use App\Shared\Exceptions\BusinessException;
use App\Shared\Exceptions\ErrorCode;

/**
 * 考勤登记
 */
class AttendanceService
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
        $query = AttendanceRecord::query()->with('employee')->orderByDesc('work_on')->orderByDesc('id');
        $query->whereIn('employee_id', $this->visibility->visibleEmployeeIds($userId));

        return $query->get()->map(fn (AttendanceRecord $record): array => $this->present($record))->all();
    }

    /**
     * 登记或覆盖某一天的考勤。只接受在职员工。
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
            '只有在职员工可以登记考勤',
        );
        $checkIn = $this->blankToNull($input['check_in'] ?? null);
        $checkOut = $this->blankToNull($input['check_out'] ?? null);

        if ($checkIn !== null && $checkOut !== null && $checkOut < $checkIn) {
            throw new BusinessException(ErrorCode::VALIDATION, '下班时间不能早于上班时间', 422);
        }

        $record = AttendanceRecord::withTrashed()
            ->where('employee_id', $employee->id)
            ->whereDate('work_on', $input['work_on'])
            ->first();

        if ($record === null) {
            $record = new AttendanceRecord([
                'employee_id' => $employee->id,
                'work_on' => $input['work_on'],
            ]);
        } elseif ($record->trashed()) {
            $record->restore();
        }

        $record->fill([
            'check_in' => $checkIn,
            'check_out' => $checkOut,
            'status' => (string) $input['status'],
        ]);
        $record->save();

        return $this->present($record->load('employee'));
    }

    /**
     * @return array<string, mixed>
     */
    private function present(AttendanceRecord $record): array
    {
        return [
            'id' => (int) $record->id,
            'employee_id' => (int) $record->employee_id,
            'employee_name' => $record->employee?->name ?? '',
            'work_on' => $record->work_on?->toDateString(),
            'check_in' => $record->check_in ? substr((string) $record->check_in, 0, 5) : null,
            'check_out' => $record->check_out ? substr((string) $record->check_out, 0, 5) : null,
            'status' => (string) $record->status,
        ];
    }

    private function blankToNull(mixed $value): ?string
    {
        $text = trim((string) $value);

        return $text === '' ? null : $text;
    }
}
