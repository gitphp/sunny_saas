<?php

/**
 * 离职申请、审批和办结
 *
 * @package     App\Modules\Hr\Application\Resignation
 * @license     MIT License (MIT)
 * @author      Sunny Yang <githup@163.com> QQ4873473
 * @copyright   Copyright (c) 2024 YourCompany. All rights reserved.
 * @link        http://www.budff.com
 */

namespace App\Modules\Hr\Application\Resignation;

use App\Modules\Hr\Application\Employee\EmployeeService;
use App\Modules\Hr\Application\Support\HrVisibility;
use App\Modules\Hr\Domain\EmployeeStatus;
use App\Modules\Hr\Infrastructure\Persistence\Resignation;
use App\Shared\Exceptions\BusinessException;
use App\Shared\Exceptions\ErrorCode;

/**
 * 离职申请、审批和办结
 */
class ResignationService
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
        $query = Resignation::query()->with('employee')->orderByDesc('id');
        $query->whereIn('employee_id', $this->visibility->visibleEmployeeIds($userId));

        return $query->get()->map(fn (Resignation $resignation): array => $this->present($resignation))->all();
    }

    /**
     * 为在职员工发起离职。同一人不能有两张未办结申请。
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
            '只有在职员工可以办理离职',
        );

        if ($input['last_on'] < $input['apply_on']) {
            throw new BusinessException(ErrorCode::VALIDATION, '最后工作日不能早于申请日期', 422);
        }

        $open = Resignation::query()
            ->where('employee_id', $employee->id)
            ->whereIn('status', [0, 1])
            ->exists();

        if ($open) {
            throw new BusinessException(ErrorCode::VALIDATION, '该员工已有未办结的离职申请', 422);
        }

        $resignation = Resignation::query()->create([
            'employee_id' => $employee->id,
            'reason' => (string) ($input['reason'] ?? ''),
            'apply_on' => $input['apply_on'],
            'last_on' => $input['last_on'],
            'status' => 0,
            'reviewer_user_id' => 0,
            'reviewed_at' => null,
        ]);

        return $this->present($resignation->load('employee'));
    }

    /**
     * 批准或驳回待审批离职
     *
     * @return array<string, mixed>
     */
    public function review(int $id, int $userId, int $status): array
    {
        $resignation = $this->findVisible($id, $userId);

        if ((int) $resignation->status !== 0) {
            throw new BusinessException(ErrorCode::VALIDATION, '该离职申请已审批', 422);
        }

        $resignation->status = $status;
        $resignation->reviewer_user_id = $userId;
        $resignation->reviewed_at = now();
        $resignation->save();

        return $this->present($resignation->load('employee'));
    }

    /**
     * 办结已批准的离职，并把员工档案改为离职
     *
     * @return array<string, mixed>
     */
    public function complete(int $id, int $userId): array
    {
        $resignation = $this->findVisible($id, $userId);

        if ((int) $resignation->status !== 1) {
            throw new BusinessException(ErrorCode::VALIDATION, '只有已批准的离职可以办结', 422);
        }

        $employee = $this->employees->findVisible((int) $resignation->employee_id, $userId);
        $this->employees->markLeft($employee, $resignation->last_on?->toDateString() ?? now()->toDateString());
        $resignation->status = 3;
        $resignation->save();

        return $this->present($resignation->load('employee'));
    }

    private function findVisible(int $id, int $userId): Resignation
    {
        $query = Resignation::query()->whereKey($id)->whereIn('employee_id', $this->visibility->visibleEmployeeIds($userId));
        $resignation = $query->first();

        if ($resignation === null) {
            throw new BusinessException(ErrorCode::NOT_FOUND, '离职申请不存在', 404);
        }

        return $resignation;
    }

    /**
     * @return array<string, mixed>
     */
    private function present(Resignation $resignation): array
    {
        return [
            'id' => (int) $resignation->id,
            'employee_id' => (int) $resignation->employee_id,
            'employee_name' => $resignation->employee?->name ?? '',
            'reason' => (string) $resignation->reason,
            'apply_on' => $resignation->apply_on?->toDateString(),
            'last_on' => $resignation->last_on?->toDateString(),
            'status' => (int) $resignation->status,
            'reviewed_at' => $resignation->reviewed_at?->toIso8601String(),
        ];
    }
}
