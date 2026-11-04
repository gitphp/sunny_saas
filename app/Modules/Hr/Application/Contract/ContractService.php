<?php

/**
 * 劳动合同
 *
 * @package     App\Modules\Hr\Application\Contract
 * @license     MIT License (MIT)
 * @author      Sunny Yang <githup@163.com> QQ4873473
 * @copyright   Copyright (c) 2024 YourCompany. All rights reserved.
 * @link        http://www.budff.com
 */

namespace App\Modules\Hr\Application\Contract;

use App\Modules\Hr\Application\Employee\EmployeeService;
use App\Modules\Hr\Application\Support\HrVisibility;
use App\Modules\Hr\Domain\EmployeeStatus;
use App\Modules\Hr\Infrastructure\Persistence\Contract;
use App\Shared\Exceptions\BusinessException;
use App\Shared\Exceptions\ErrorCode;

/**
 * 劳动合同
 */
class ContractService
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
        $query = Contract::query()->with('employee')->orderByDesc('id');
        $query->whereIn('employee_id', $this->visibility->visibleEmployeeIds($userId));

        return $query->get()->map(fn (Contract $contract): array => $this->present($contract))->all();
    }

    /**
     * 为待入职或在职员工签订合同
     *
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    public function store(int $userId, array $input): array
    {
        $employee = $this->employees->findVisible((int) $input['employee_id'], $userId);

        if (! in_array((int) $employee->status, [EmployeeStatus::PENDING, EmployeeStatus::ACTIVE], true)) {
            throw new BusinessException(ErrorCode::VALIDATION, '已离职员工不能签订合同', 422);
        }

        $type = (string) $input['type'];
        $endOn = $input['end_on'] ?? null;

        if ($type !== 'open' && ($endOn === null || $endOn === '')) {
            throw new BusinessException(ErrorCode::VALIDATION, '固定期限合同需要结束日期', 422);
        }

        if ($endOn && $endOn < $input['start_on']) {
            throw new BusinessException(ErrorCode::VALIDATION, '合同结束日期不能早于开始日期', 422);
        }

        if (Contract::withTrashed()->where('contract_no', $input['contract_no'])->exists()) {
            throw new BusinessException(ErrorCode::VALIDATION, '合同编号已存在', 422);
        }

        $contract = Contract::query()->create([
            'employee_id' => $employee->id,
            'contract_no' => (string) $input['contract_no'],
            'type' => $type,
            'start_on' => $input['start_on'],
            'end_on' => $endOn ?: null,
            'status' => 1,
            'signed_on' => $input['signed_on'],
        ]);

        return $this->present($contract->load('employee'));
    }

    /**
     * 终止仍在履行的合同
     *
     * @return array<string, mixed>
     */
    public function terminate(int $id, int $userId): array
    {
        $contract = $this->findVisible($id, $userId);

        if ((int) $contract->status !== 1) {
            throw new BusinessException(ErrorCode::VALIDATION, '合同已经终止', 422);
        }

        $contract->status = 2;
        $contract->save();

        return $this->present($contract->load('employee'));
    }

    /**
     * 合同是否可见，取决于员工是否在数据范围内
     */
    private function findVisible(int $id, int $userId): Contract
    {
        $query = Contract::query()->whereKey($id)->whereIn('employee_id', $this->visibility->visibleEmployeeIds($userId));
        $contract = $query->first();

        if ($contract === null) {
            throw new BusinessException(ErrorCode::NOT_FOUND, '合同不存在', 404);
        }

        return $contract;
    }

    /**
     * @return array<string, mixed>
     */
    private function present(Contract $contract): array
    {
        return [
            'id' => (int) $contract->id,
            'employee_id' => (int) $contract->employee_id,
            'employee_name' => $contract->employee?->name ?? '',
            'contract_no' => (string) $contract->contract_no,
            'type' => (string) $contract->type,
            'start_on' => $contract->start_on?->toDateString(),
            'end_on' => $contract->end_on?->toDateString(),
            'status' => (int) $contract->status,
            'signed_on' => $contract->signed_on?->toDateString(),
        ];
    }
}
