<?php

/**
 * 身份与访问模块的权限种子
 *
 * @package     App\Modules\Iam\Infrastructure\Catalog
 * @license     MIT License (MIT)
 * @author      Sunny Yang <githup@163.com> QQ4873473
 * @copyright   Copyright (c) 2024 YourCompany. All rights reserved.
 * @link        http://www.budff.com
 */

namespace App\Modules\Iam\Infrastructure\Catalog;

/**
 * 身份与访问模块的权限种子
 */
final class IamCatalog
{
    /**
     * 返回本模块的目录信息
     *
     * @return array{code: string, name: string, description: string}
     */
    public static function module(): array
    {
        return [
            'code' => 'iam',
            'name' => '身份与访问',
            'description' => '成员、部门、角色、权限与模块开通',
        ];
    }

    /**
     * 返回需要写入权限表的目录
     *
     * @return list<array{code: string, name: string, type: string, route: string, method: string, icon: string, sort: int, parent: string|null}>
     */
    public static function permissions(): array
    {
        return [
            ['code' => 'iam', 'name' => '组织权限', 'type' => 'menu', 'route' => '/iam', 'method' => '', 'icon' => 'setting', 'sort' => 10, 'parent' => null],
            ['code' => 'iam.departments.menu', 'name' => '部门', 'type' => 'menu', 'route' => '/iam/departments', 'method' => '', 'icon' => 'office-building', 'sort' => 10, 'parent' => 'iam'],
            ['code' => 'iam.roles.menu', 'name' => '角色', 'type' => 'menu', 'route' => '/iam/roles', 'method' => '', 'icon' => 'user', 'sort' => 20, 'parent' => 'iam'],
            ['code' => 'iam.members.menu', 'name' => '成员', 'type' => 'menu', 'route' => '/iam/members', 'method' => '', 'icon' => 'avatar', 'sort' => 30, 'parent' => 'iam'],
            ['code' => 'iam.modules.menu', 'name' => '模块', 'type' => 'menu', 'route' => '/iam/modules', 'method' => '', 'icon' => 'grid', 'sort' => 40, 'parent' => 'iam'],
            ['code' => 'iam.permissions.menu', 'name' => '权限', 'type' => 'menu', 'route' => '/iam/permissions', 'method' => '', 'icon' => 'key', 'sort' => 50, 'parent' => 'iam'],
            ['code' => 'iam.plans.menu', 'name' => '套餐', 'type' => 'menu', 'route' => '/iam/plan', 'method' => '', 'icon' => 'ticket', 'sort' => 60, 'parent' => 'iam'],
            ['code' => 'iam.departments.create', 'name' => '新建部门', 'type' => 'button', 'route' => '', 'method' => '', 'icon' => '', 'sort' => 10, 'parent' => 'iam.departments.menu'],
            ['code' => 'iam.roles.create', 'name' => '新建角色', 'type' => 'button', 'route' => '', 'method' => '', 'icon' => '', 'sort' => 10, 'parent' => 'iam.roles.menu'],
            ['code' => 'iam.members.assign', 'name' => '分配角色', 'type' => 'button', 'route' => '', 'method' => '', 'icon' => '', 'sort' => 10, 'parent' => 'iam.members.menu'],
            ['code' => 'iam.members.create', 'name' => '添加成员', 'type' => 'button', 'route' => '', 'method' => '', 'icon' => '', 'sort' => 20, 'parent' => 'iam.members.menu'],
            ['code' => 'iam.menus.index', 'name' => '菜单列表', 'type' => 'api', 'route' => '/api/v1/iam/menus', 'method' => 'GET', 'icon' => '', 'sort' => 10, 'parent' => 'iam'],
            ['code' => 'iam.me.show', 'name' => '当前成员', 'type' => 'api', 'route' => '/api/v1/iam/me', 'method' => 'GET', 'icon' => '', 'sort' => 20, 'parent' => 'iam'],
            ['code' => 'iam.departments.index', 'name' => '部门列表', 'type' => 'api', 'route' => '/api/v1/iam/departments', 'method' => 'GET', 'icon' => '', 'sort' => 10, 'parent' => 'iam.departments.menu'],
            ['code' => 'iam.departments.store', 'name' => '创建部门', 'type' => 'api', 'route' => '/api/v1/iam/departments', 'method' => 'POST', 'icon' => '', 'sort' => 20, 'parent' => 'iam.departments.menu'],
            ['code' => 'iam.departments.show', 'name' => '部门详情', 'type' => 'api', 'route' => '/api/v1/iam/departments/{department}', 'method' => 'GET', 'icon' => '', 'sort' => 30, 'parent' => 'iam.departments.menu'],
            ['code' => 'iam.departments.update', 'name' => '更新部门', 'type' => 'api', 'route' => '/api/v1/iam/departments/{department}', 'method' => 'PUT', 'icon' => '', 'sort' => 40, 'parent' => 'iam.departments.menu'],
            ['code' => 'iam.departments.destroy', 'name' => '删除部门', 'type' => 'api', 'route' => '/api/v1/iam/departments/{department}', 'method' => 'DELETE', 'icon' => '', 'sort' => 50, 'parent' => 'iam.departments.menu'],
            ['code' => 'iam.roles.index', 'name' => '角色列表', 'type' => 'api', 'route' => '/api/v1/iam/roles', 'method' => 'GET', 'icon' => '', 'sort' => 10, 'parent' => 'iam.roles.menu'],
            ['code' => 'iam.roles.store', 'name' => '创建角色', 'type' => 'api', 'route' => '/api/v1/iam/roles', 'method' => 'POST', 'icon' => '', 'sort' => 20, 'parent' => 'iam.roles.menu'],
            ['code' => 'iam.roles.show', 'name' => '角色详情', 'type' => 'api', 'route' => '/api/v1/iam/roles/{role}', 'method' => 'GET', 'icon' => '', 'sort' => 30, 'parent' => 'iam.roles.menu'],
            ['code' => 'iam.roles.update', 'name' => '更新角色', 'type' => 'api', 'route' => '/api/v1/iam/roles/{role}', 'method' => 'PUT', 'icon' => '', 'sort' => 40, 'parent' => 'iam.roles.menu'],
            ['code' => 'iam.roles.destroy', 'name' => '删除角色', 'type' => 'api', 'route' => '/api/v1/iam/roles/{role}', 'method' => 'DELETE', 'icon' => '', 'sort' => 50, 'parent' => 'iam.roles.menu'],
            ['code' => 'iam.members.index', 'name' => '成员列表', 'type' => 'api', 'route' => '/api/v1/iam/members', 'method' => 'GET', 'icon' => '', 'sort' => 10, 'parent' => 'iam.members.menu'],
            ['code' => 'iam.members.roles.sync', 'name' => '分配成员角色', 'type' => 'api', 'route' => '/api/v1/iam/members/{member}/roles', 'method' => 'PUT', 'icon' => '', 'sort' => 20, 'parent' => 'iam.members.menu'],
            ['code' => 'iam.members.update', 'name' => '调整成员', 'type' => 'api', 'route' => '/api/v1/iam/members/{member}', 'method' => 'PUT', 'icon' => '', 'sort' => 30, 'parent' => 'iam.members.menu'],
            ['code' => 'iam.members.store', 'name' => '添加成员', 'type' => 'api', 'route' => '/api/v1/iam/members', 'method' => 'POST', 'icon' => '', 'sort' => 40, 'parent' => 'iam.members.menu'],
            ['code' => 'iam.members.destroy', 'name' => '移除成员', 'type' => 'api', 'route' => '/api/v1/iam/members/{member}', 'method' => 'DELETE', 'icon' => '', 'sort' => 50, 'parent' => 'iam.members.menu'],
            ['code' => 'iam.modules.index', 'name' => '模块开通列表', 'type' => 'api', 'route' => '/api/v1/iam/modules', 'method' => 'GET', 'icon' => '', 'sort' => 10, 'parent' => 'iam.modules.menu'],
            ['code' => 'iam.modules.update', 'name' => '更新模块开通', 'type' => 'api', 'route' => '/api/v1/iam/modules/{moduleCode}', 'method' => 'PUT', 'icon' => '', 'sort' => 20, 'parent' => 'iam.modules.menu'],
            ['code' => 'iam.permissions.index', 'name' => '权限目录', 'type' => 'api', 'route' => '/api/v1/iam/permissions', 'method' => 'GET', 'icon' => '', 'sort' => 10, 'parent' => 'iam.permissions.menu'],
            ['code' => 'iam.plans.show', 'name' => '当前套餐', 'type' => 'api', 'route' => '/api/v1/iam/plan', 'method' => 'GET', 'icon' => '', 'sort' => 10, 'parent' => 'iam.plans.menu'],
            ['code' => 'iam.plans.update', 'name' => '更换套餐', 'type' => 'api', 'route' => '/api/v1/iam/plan', 'method' => 'PUT', 'icon' => '', 'sort' => 20, 'parent' => 'iam.plans.menu'],
        ];
    }
}

