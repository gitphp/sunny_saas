<?php

/**
 * 岗位维护
 *
 * @package     App\Modules\Hr\Application\Position
 * @license     MIT License (MIT)
 * @author      Sunny Yang <githup@163.com> QQ4873473
 * @copyright   Copyright (c) 2024 YourCompany. All rights reserved.
 * @link        http://www.budff.com
 */

namespace App\Modules\Hr\Application\Position;

use App\Modules\Hr\Application\Support\HrVisibility;
use App\Modules\Hr\Infrastructure\Persistence\Employee;
use App\Modules\Hr\Infrastructure\Persistence\Position;
use App\Modules\Iam\Application\Contracts\DepartmentDirectory;
use App\Shared\Exceptions\BusinessException;
use App\Shared\Exceptions\ErrorCode;

/**
 * 岗位维护
 */
class PositionService
{
    /**
     * 注入依赖
     */
    public function __construct(
        private readonly HrVisibility $visibility,
        private readonly DepartmentDirectory $departments,
    ) {}

    /**
     * 返回可见岗位
     *
     * @return list<array<string, mixed>>
     */
    public function list(int $userId): array
    {
        $visibleIds = array_column($this->departments->listVisible($userId), 'id');
        $scope = $this->departments->scopeFor($userId);
        $query = Position::query()->orderBy('sort')->orderBy('id');

        if (! $scope['unrestricted']) {
            $query->where(function ($inner) use ($visibleIds): void {
                $inner->whereNull('department_id');

                if ($visibleIds !== []) {
                    $inner->orWhereIn('department_id', $visibleIds);
                }
            });
        }

        $names = $this->names($userId);

        return $query->get()->map(fn (Position $position): array => $this->present($position, $names))->all();
    }

    /**
     * 创建岗位
     *
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    public function store(int $userId, array $input): array
    {
        $departmentId = $this->nullableId($input['department_id'] ?? null);
        $this->visibility->assertDepartment($departmentId, $userId);
        $this->assertCodeAvailable((string) $input['code'], null);

        $position = Position::query()->create([
            'department_id' => $departmentId,
            'name' => (string) $input['name'],
            'code' => (string) $input['code'],
            'sort' => (int) ($input['sort'] ?? 0),
            'status' => 1,
        ]);

        return $this->present($position, $this->names($userId));
    }

    /**
     * 更新岗位
     *
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    public function update(int $id, int $userId, array $input): array
    {
        $position = $this->find($id);
        $departmentId = array_key_exists('department_id', $input)
            ? $this->nullableId($input['department_id'])
            : ($position->department_id ? (int) $position->department_id : null);
        $this->visibility->assertDepartment($departmentId, $userId);

        if (isset($input['code'])) {
            $this->assertCodeAvailable((string) $input['code'], $position->id);
            $position->code = (string) $input['code'];
        }

        if (isset($input['name'])) {
            $position->name = (string) $input['name'];
        }

        if (isset($input['sort'])) {
            $position->sort = (int) $input['sort'];
        }

        if (isset($input['status'])) {
            $position->status = (int) $input['status'] === 1 ? 1 : 0;
        }

        $position->department_id = $departmentId;
        $position->save();

        return $this->present($position, $this->names($userId));
    }

    /**
     * 删除没有在职或待入职员工的岗位
     */
    public function destroy(int $id): void
    {
        $position = $this->find($id);

        if (Employee::query()->where('position_id', $position->id)->where('status', '!=', 2)->exists()) {
            throw new BusinessException(ErrorCode::VALIDATION, '岗位下仍有员工，不能删除', 422);
        }

        $position->delete();
    }

    /**
     * 按当前租户查找岗位，其他企业的主键视为不存在
     */
    private function find(int $id): Position
    {
        $position = Position::query()->find($id);

        if ($position === null) {
            throw new BusinessException(ErrorCode::NOT_FOUND, '岗位不存在', 404);
        }

        return $position;
    }

    /**
     * 同一企业内岗位编码不能重复
     */
    private function assertCodeAvailable(string $code, ?int $ignoreId): void
    {
        $query = Position::withTrashed()->where('code', $code);

        if ($ignoreId !== null) {
            $query->where('id', '!=', $ignoreId);
        }

        if ($query->exists()) {
            throw new BusinessException(ErrorCode::VALIDATION, '岗位编码已存在', 422);
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
    private function present(Position $position, array $names): array
    {
        $departmentId = $position->department_id ? (int) $position->department_id : null;

        return [
            'id' => (int) $position->id,
            'department_id' => $departmentId,
            'department_name' => $departmentId ? ($names[$departmentId] ?? '') : '',
            'name' => (string) $position->name,
            'code' => (string) $position->code,
            'sort' => (int) $position->sort,
            'status' => (int) $position->status,
        ];
    }

    private function nullableId(mixed $value): ?int
    {
        $id = (int) $value;

        return $id > 0 ? $id : null;
    }
}
