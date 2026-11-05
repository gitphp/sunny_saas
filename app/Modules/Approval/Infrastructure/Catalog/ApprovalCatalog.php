<?php

/**
 * 审批模块与权限目录
 *
 * @package     App\Modules\Approval\Infrastructure\Catalog
 * @license     MIT License (MIT)
 * @author      Sunny Yang <githup@163.com> QQ4873473
 * @copyright   Copyright (c) 2024 YourCompany. All rights reserved.
 * @link        http://www.budff.com
 */

namespace App\Modules\Approval\Infrastructure\Catalog;

/**
 * 审批模块与权限目录
 */
final class ApprovalCatalog
{
    /**
     * 模块登记
     *
     * @return array{code: string, name: string, description: string}
     */
    public static function module(): array
    {
        return [
            'code' => 'approval',
            'name' => '审批',
            'description' => '流程定义、审批任务、待办与抄送',
        ];
    }

    /**
     * 菜单和接口权限。父级必须写在子级前面。
     *
     * @return list<array{code: string, name: string, type: string, route: string, method: string, icon: string, sort: int, parent: ?string}>
     */
    public static function permissions(): array
    {
        return [
            ['code' => 'approval', 'name' => '审批', 'type' => 'menu', 'route' => '/approval', 'method' => '', 'icon' => 'stamp', 'sort' => 30, 'parent' => null],
            ['code' => 'approval.start.menu', 'name' => '发起申请', 'type' => 'menu', 'route' => '/approval/start', 'method' => '', 'icon' => '', 'sort' => 10, 'parent' => 'approval'],
            ['code' => 'approval.todo.menu', 'name' => '待我审批', 'type' => 'menu', 'route' => '/approval/todo', 'method' => '', 'icon' => '', 'sort' => 20, 'parent' => 'approval'],
            ['code' => 'approval.mine.menu', 'name' => '我的申请', 'type' => 'menu', 'route' => '/approval/mine', 'method' => '', 'icon' => '', 'sort' => 30, 'parent' => 'approval'],
            ['code' => 'approval.received.menu', 'name' => '我收到的', 'type' => 'menu', 'route' => '/approval/received', 'method' => '', 'icon' => '', 'sort' => 40, 'parent' => 'approval'],
            ['code' => 'approval.done.menu', 'name' => '我已审批', 'type' => 'menu', 'route' => '/approval/done', 'method' => '', 'icon' => '', 'sort' => 50, 'parent' => 'approval'],
            ['code' => 'approval.manage.menu', 'name' => '审批管理', 'type' => 'menu', 'route' => '/approval/manage', 'method' => '', 'icon' => '', 'sort' => 60, 'parent' => 'approval'],
            ['code' => 'approval.data.menu', 'name' => '数据管理', 'type' => 'menu', 'route' => '/approval/data', 'method' => '', 'icon' => '', 'sort' => 70, 'parent' => 'approval'],
            ['code' => 'approval.catalog.index', 'name' => '可发起流程', 'type' => 'api', 'route' => '/api/v1/approval/catalog', 'method' => 'GET', 'icon' => '', 'sort' => 10, 'parent' => 'approval.start.menu'],
            ['code' => 'approval.instances.store', 'name' => '发起审批', 'type' => 'api', 'route' => '/api/v1/approval/instances', 'method' => 'POST', 'icon' => '', 'sort' => 20, 'parent' => 'approval.start.menu'],
            ['code' => 'approval.tasks.index', 'name' => '任务列表', 'type' => 'api', 'route' => '/api/v1/approval/tasks', 'method' => 'GET', 'icon' => '', 'sort' => 10, 'parent' => 'approval.todo.menu'],
            ['code' => 'approval.instances.mine', 'name' => '我的申请', 'type' => 'api', 'route' => '/api/v1/approval/instances', 'method' => 'GET', 'icon' => '', 'sort' => 10, 'parent' => 'approval.mine.menu'],
            ['code' => 'approval.instances.show', 'name' => '审批详情', 'type' => 'api', 'route' => '/api/v1/approval/instances/{instance}', 'method' => 'GET', 'icon' => '', 'sort' => 20, 'parent' => 'approval.mine.menu'],
            ['code' => 'approval.instances.approve', 'name' => '同意', 'type' => 'api', 'route' => '/api/v1/approval/instances/{instance}/approve', 'method' => 'POST', 'icon' => '', 'sort' => 30, 'parent' => 'approval.todo.menu'],
            ['code' => 'approval.instances.reject', 'name' => '驳回', 'type' => 'api', 'route' => '/api/v1/approval/instances/{instance}/reject', 'method' => 'POST', 'icon' => '', 'sort' => 40, 'parent' => 'approval.todo.menu'],
            ['code' => 'approval.instances.revoke', 'name' => '撤销', 'type' => 'api', 'route' => '/api/v1/approval/instances/{instance}/revoke', 'method' => 'POST', 'icon' => '', 'sort' => 30, 'parent' => 'approval.mine.menu'],
            ['code' => 'approval.instances.comment', 'name' => '评论', 'type' => 'api', 'route' => '/api/v1/approval/instances/{instance}/comment', 'method' => 'POST', 'icon' => '', 'sort' => 40, 'parent' => 'approval.mine.menu'],
            ['code' => 'approval.groups.index', 'name' => '流程分组', 'type' => 'api', 'route' => '/api/v1/approval/groups', 'method' => 'GET', 'icon' => '', 'sort' => 10, 'parent' => 'approval.manage.menu'],
            ['code' => 'approval.groups.store', 'name' => '新建分组', 'type' => 'api', 'route' => '/api/v1/approval/groups', 'method' => 'POST', 'icon' => '', 'sort' => 20, 'parent' => 'approval.manage.menu'],
            ['code' => 'approval.groups.update', 'name' => '更新分组', 'type' => 'api', 'route' => '/api/v1/approval/groups/{group}', 'method' => 'PUT', 'icon' => '', 'sort' => 30, 'parent' => 'approval.manage.menu'],
            ['code' => 'approval.groups.destroy', 'name' => '删除分组', 'type' => 'api', 'route' => '/api/v1/approval/groups/{group}', 'method' => 'DELETE', 'icon' => '', 'sort' => 40, 'parent' => 'approval.manage.menu'],
            ['code' => 'approval.definitions.index', 'name' => '流程列表', 'type' => 'api', 'route' => '/api/v1/approval/definitions', 'method' => 'GET', 'icon' => '', 'sort' => 50, 'parent' => 'approval.manage.menu'],
            ['code' => 'approval.definitions.store', 'name' => '新建流程', 'type' => 'api', 'route' => '/api/v1/approval/definitions', 'method' => 'POST', 'icon' => '', 'sort' => 60, 'parent' => 'approval.manage.menu'],
            ['code' => 'approval.definitions.show', 'name' => '流程详情', 'type' => 'api', 'route' => '/api/v1/approval/definitions/{definition}', 'method' => 'GET', 'icon' => '', 'sort' => 70, 'parent' => 'approval.manage.menu'],
            ['code' => 'approval.definitions.update', 'name' => '设计流程', 'type' => 'api', 'route' => '/api/v1/approval/definitions/{definition}', 'method' => 'PUT', 'icon' => '', 'sort' => 80, 'parent' => 'approval.manage.menu'],
            ['code' => 'approval.definitions.publish', 'name' => '发布版本', 'type' => 'api', 'route' => '/api/v1/approval/definitions/{definition}/publish', 'method' => 'POST', 'icon' => '', 'sort' => 90, 'parent' => 'approval.manage.menu'],
            ['code' => 'approval.members.index', 'name' => '审批选人', 'type' => 'api', 'route' => '/api/v1/approval/members', 'method' => 'GET', 'icon' => '', 'sort' => 100, 'parent' => 'approval.manage.menu'],
            ['code' => 'approval.data.index', 'name' => '审批数据', 'type' => 'api', 'route' => '/api/v1/approval/data', 'method' => 'GET', 'icon' => '', 'sort' => 10, 'parent' => 'approval.data.menu'],
        ];
    }
}
