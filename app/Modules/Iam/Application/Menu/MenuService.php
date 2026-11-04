<?php

/**
 * 菜单与当前成员信息
 *
 * @package     App\Modules\Iam\Application\Menu
 * @license     MIT License (MIT)
 * @author      Sunny Yang <githup@163.com> QQ4873473
 * @copyright   Copyright (c) 2024 YourCompany. All rights reserved.
 * @link        http://www.budff.com
 */

namespace App\Modules\Iam\Application\Menu;

use App\Modules\Iam\Application\Access\AccessService;
use App\Modules\Iam\Application\Access\DataScopeResolver;
use App\Modules\Iam\Infrastructure\Persistence\Permission;
use App\Modules\Iam\Infrastructure\Persistence\Role;
use App\Modules\Iam\Infrastructure\Persistence\TenantUser;
use App\Modules\Iam\Infrastructure\Persistence\TenantUserRole;
use Illuminate\Support\Collection;

/**
 * 菜单与当前成员信息
 */
class MenuService
{
    /**
     * 注入依赖
     */
    public function __construct(
        private readonly AccessService $access,
        private readonly DataScopeResolver $scopes,
    ) {}

    /**
     * 返回当前用户可见菜单
     *
     * @return list<array<string, mixed>>
     */
    public function menus(int $userId): array
    {
        /** @var Collection<int, Permission> $menus */
        $menus = Permission::query()->where('type', 'menu')->orderBy('sort')->orderBy('id')->get();

        if ($this->access->isOwner($userId)) {
            return $this->nest($menus, null);
        }

        $codes = $this->access->permissionCodes($userId);
        $allowed = $menus->whereIn('code', $codes)->pluck('id')->map(fn ($id): int => (int) $id)->all();
        $allowed = $this->withAncestors($menus, $allowed);
        $visible = $menus->whereIn('id', $allowed)->values();

        return $this->nest($visible, null);
    }

    /**
     * 返回当前用户或成员
     *
     * @return array<string, mixed>
     */
    public function me(int $userId): array
    {
        $member = TenantUser::query()->with(['user', 'roles'])->where('user_id', $userId)->where('status', 1)->first();
        $scope = $this->scopes->forUser($userId);
        $roleIds = $member === null
            ? collect()
            : TenantUserRole::query()->where('tenant_user_id', $member->id)->pluck('role_id');
        $scopes = Role::query()->whereIn('id', $roleIds)->pluck('data_scope')->unique()->values()->all();

        return [
            'user' => [
                'id' => (int) ($member?->user?->id ?? $userId),
                'name' => $member?->user?->name ?? '',
                'email' => $member?->user?->email ?? '',
            ],
            'member' => [
                'id' => (int) ($member?->id ?? 0),
                'is_owner' => (int) ($member?->is_owner ?? 0),
                'department_id' => $member?->department_id ? (int) $member->department_id : null,
                'status' => (int) ($member?->status ?? 0),
            ],
            'roles' => $member?->roles->map(fn (Role $role): array => [
                'id' => (int) $role->id,
                'code' => $role->code,
                'name' => $role->name,
                'data_scope' => $role->data_scope,
            ])->values()->all() ?? [],
            'permissions' => $this->access->permissionCodes($userId),
            'data_scopes' => $scope['unrestricted'] ? array_values(array_unique(array_merge($scopes, ['all']))) : $scopes,
        ];
    }

    /**
     * 补上菜单的上级，避免只剩子节点时树断开
     *
     * @param  Collection<int, Permission>  $menus
     * @param  list<int>  $ids
     * @return list<int>
     */
    private function withAncestors(Collection $menus, array $ids): array
    {
        $byId = $menus->keyBy('id');
        $result = $ids;

        foreach ($ids as $id) {
            $current = $byId->get($id);

            while ($current !== null && $current->parent_id) {
                $parentId = (int) $current->parent_id;

                if (! in_array($parentId, $result, true)) {
                    $result[] = $parentId;
                }

                $current = $byId->get($parentId);
            }
        }

        return $result;
    }

    /**
     * 按上级主键组装树
     *
     * @param  Collection<int, Permission>  $menus
     * @return list<array<string, mixed>>
     */
    private function nest(Collection $menus, ?int $parentId): array
    {
        return $menus
            ->filter(fn (Permission $menu): bool => ($menu->parent_id ? (int) $menu->parent_id : null) === $parentId)
            ->values()
            ->map(fn (Permission $menu): array => [
                'id' => (int) $menu->id,
                'code' => $menu->code,
                'name' => $menu->name,
                'route' => $menu->route,
                'icon' => $menu->icon,
                'sort' => (int) $menu->sort,
                'children' => $this->nest($menus, (int) $menu->id),
            ])
            ->all();
    }
}

