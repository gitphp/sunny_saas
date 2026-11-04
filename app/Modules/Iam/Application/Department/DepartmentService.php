<?php

/**
 * 部门维护
 *
 * @package     App\Modules\Iam\Application\Department
 * @license     MIT License (MIT)
 * @author      Sunny Yang <githup@163.com> QQ4873473
 * @copyright   Copyright (c) 2024 YourCompany. All rights reserved.
 * @link        http://www.budff.com
 */

namespace App\Modules\Iam\Application\Department;

use App\Modules\Iam\Application\Access\DataScopeResolver;
use App\Modules\Iam\Infrastructure\Persistence\Department;
use App\Shared\Exceptions\BusinessException;
use App\Shared\Exceptions\ErrorCode;

/**
 * 部门维护
 */
class DepartmentService
{
    /**
     * 注入依赖
     */
    public function __construct(private readonly DataScopeResolver $scopes) {}

    /**
     * @return list<array<string, mixed>>
     */
    public function list(int $userId): array
    {
        $query = Department::query()->orderBy('sort')->orderBy('id');
        $this->scopes->applyToDepartments($query, $userId);

        return $query->get()->map(fn (Department $department): array => $this->present($department))->all();
    }

    /**
     * 创建
     *
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    public function create(array $input): array
    {
        unset($input['tenant_id']);
        $parent = $this->parentOrFail((int) ($input['parent_id'] ?? 0));

        $department = new Department;
        $department->name = (string) $input['name'];
        $department->parent_id = $parent?->id;
        $department->sort = (int) ($input['sort'] ?? 0);
        $department->path = '';
        $department->save();

        $this->writePath($department, $parent);

        return $this->present($department->refresh());
    }

    /**
     * 查看
     *
     * @return array<string, mixed>
     */
    public function show(int $id, int $userId): array
    {
        return $this->present($this->findVisible($id, $userId));
    }

    /**
     * 更新
     *
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    public function update(int $id, int $userId, array $input): array
    {
        unset($input['tenant_id']);
        $department = $this->findVisible($id, $userId);
        $parentId = array_key_exists('parent_id', $input)
            ? (int) ($input['parent_id'] ?? 0)
            : (int) ($department->parent_id ?? 0);
        $parent = $this->parentOrFail($parentId);

        // 上级必须仍在当前租户，且不能选自己或自己的下级，否则路径会成环。
        if ($parent !== null && ($parent->id === $department->id || ($department->path !== '' && str_starts_with($parent->path, $department->path.'/')))) {
            throw new BusinessException(ErrorCode::VALIDATION, '不能选择自己或下级部门作为上级', 422);
        }

        $department->name = (string) ($input['name'] ?? $department->name);
        $department->sort = (int) ($input['sort'] ?? $department->sort);
        $department->parent_id = $parent?->id;
        $department->save();
        $this->writePath($department, $parent);

        return $this->present($department->refresh());
    }

    /**
     * 删除
     */
    public function delete(int $id, int $userId): void
    {
        $department = $this->findVisible($id, $userId);

        if (Department::query()->where('parent_id', $department->id)->exists()) {
            throw new BusinessException(ErrorCode::VALIDATION, '请先删除下级部门', 422);
        }

        $department->delete();
    }

    /**
     * 按数据范围查找可见记录，范围外视为不存在
     */
    private function findVisible(int $id, int $userId): Department
    {
        $department = Department::query()->find($id);

        if ($department === null || ! $this->scopes->departmentVisible((int) $department->id, $userId)) {
            throw new BusinessException(ErrorCode::NOT_FOUND, '部门不存在', 404);
        }

        return $department;
    }

    /**
     * 校验上级部门属于当前租户
     */
    private function parentOrFail(int $parentId): ?Department
    {
        if ($parentId <= 0) {
            return null;
        }

        $parent = Department::query()->find($parentId);

        if ($parent === null) {
            throw new BusinessException(ErrorCode::VALIDATION, '上级部门不存在或不属于当前租户', 422);
        }

        return $parent;
    }

    /**
     * 按上级部门重写物化路径，并连带更新下级
     */
    private function writePath(Department $department, ?Department $parent): void
    {
        $old = (string) $department->path;
        $new = ($parent ? $parent->path : '').'/'.$department->id;
        $department->path = $new;
        $department->save();

        if ($old !== '' && $old !== $new) {
            Department::query()->where('path', 'like', $old.'/%')->get()->each(function (Department $child) use ($old, $new): void {
                $child->path = $new.substr((string) $child->path, strlen($old));
                $child->save();
            });
        }
    }

    /**
     * 把模型整理成接口数据
     *
     * @return array<string, mixed>
     */
    private function present(Department $department): array
    {
        return [
            'id' => (int) $department->id,
            'tenant_id' => (int) $department->tenant_id,
            'parent_id' => $department->parent_id ? (int) $department->parent_id : null,
            'name' => $department->name,
            'path' => $department->path,
            'sort' => (int) $department->sort,
        ];
    }
}

