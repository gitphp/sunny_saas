<?php

/**
 * 人事模块的权限种子
 *
 * @package     App\Modules\Hr\Infrastructure\Catalog
 * @license     MIT License (MIT)
 * @author      Sunny Yang <githup@163.com> QQ4873473
 * @copyright   Copyright (c) 2024 YourCompany. All rights reserved.
 * @link        http://www.budff.com
 */

namespace App\Modules\Hr\Infrastructure\Catalog;

/**
 * 人事模块的权限种子
 */
final class HrCatalog
{
    /**
     * 返回本模块的目录信息
     *
     * @return array{code: string, name: string, description: string}
     */
    public static function module(): array
    {
        return [
            'code' => 'hr',
            'name' => '人事',
            'description' => '组织、员工档案、入离职、合同、考勤与请假',
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
            ['code' => 'hr', 'name' => '人事', 'type' => 'menu', 'route' => '/hr', 'method' => '', 'icon' => 'user', 'sort' => 20, 'parent' => null],
            ['code' => 'hr.organization.menu', 'name' => '组织架构', 'type' => 'menu', 'route' => '/hr/organization', 'method' => '', 'icon' => '', 'sort' => 10, 'parent' => 'hr'],
            ['code' => 'hr.positions.menu', 'name' => '岗位', 'type' => 'menu', 'route' => '/hr/positions', 'method' => '', 'icon' => '', 'sort' => 20, 'parent' => 'hr'],
            ['code' => 'hr.employees.menu', 'name' => '员工档案', 'type' => 'menu', 'route' => '/hr/employees', 'method' => '', 'icon' => '', 'sort' => 30, 'parent' => 'hr'],
            ['code' => 'hr.onboarding.menu', 'name' => '入职', 'type' => 'menu', 'route' => '/hr/onboarding', 'method' => '', 'icon' => '', 'sort' => 40, 'parent' => 'hr'],
            ['code' => 'hr.resignations.menu', 'name' => '离职', 'type' => 'menu', 'route' => '/hr/resignations', 'method' => '', 'icon' => '', 'sort' => 50, 'parent' => 'hr'],
            ['code' => 'hr.contracts.menu', 'name' => '合同', 'type' => 'menu', 'route' => '/hr/contracts', 'method' => '', 'icon' => '', 'sort' => 60, 'parent' => 'hr'],
            ['code' => 'hr.attendance.menu', 'name' => '考勤', 'type' => 'menu', 'route' => '/hr/attendance', 'method' => '', 'icon' => '', 'sort' => 70, 'parent' => 'hr'],
            ['code' => 'hr.leaves.menu', 'name' => '请假', 'type' => 'menu', 'route' => '/hr/leaves', 'method' => '', 'icon' => '', 'sort' => 80, 'parent' => 'hr'],
            ['code' => 'hr.organization.index', 'name' => '组织架构', 'type' => 'api', 'route' => '/api/v1/hr/organization', 'method' => 'GET', 'icon' => '', 'sort' => 10, 'parent' => 'hr.organization.menu'],
            ['code' => 'hr.positions.index', 'name' => '岗位列表', 'type' => 'api', 'route' => '/api/v1/hr/positions', 'method' => 'GET', 'icon' => '', 'sort' => 10, 'parent' => 'hr.positions.menu'],
            ['code' => 'hr.positions.store', 'name' => '创建岗位', 'type' => 'api', 'route' => '/api/v1/hr/positions', 'method' => 'POST', 'icon' => '', 'sort' => 20, 'parent' => 'hr.positions.menu'],
            ['code' => 'hr.positions.update', 'name' => '更新岗位', 'type' => 'api', 'route' => '/api/v1/hr/positions/{position}', 'method' => 'PUT', 'icon' => '', 'sort' => 30, 'parent' => 'hr.positions.menu'],
            ['code' => 'hr.positions.destroy', 'name' => '删除岗位', 'type' => 'api', 'route' => '/api/v1/hr/positions/{position}', 'method' => 'DELETE', 'icon' => '', 'sort' => 40, 'parent' => 'hr.positions.menu'],
            ['code' => 'hr.employees.index', 'name' => '员工列表', 'type' => 'api', 'route' => '/api/v1/hr/employees', 'method' => 'GET', 'icon' => '', 'sort' => 10, 'parent' => 'hr.employees.menu'],
            ['code' => 'hr.employees.store', 'name' => '建立员工档案', 'type' => 'api', 'route' => '/api/v1/hr/employees', 'method' => 'POST', 'icon' => '', 'sort' => 20, 'parent' => 'hr.employees.menu'],
            ['code' => 'hr.employees.show', 'name' => '员工详情', 'type' => 'api', 'route' => '/api/v1/hr/employees/{employee}', 'method' => 'GET', 'icon' => '', 'sort' => 30, 'parent' => 'hr.employees.menu'],
            ['code' => 'hr.employees.update', 'name' => '更新员工档案', 'type' => 'api', 'route' => '/api/v1/hr/employees/{employee}', 'method' => 'PUT', 'icon' => '', 'sort' => 40, 'parent' => 'hr.employees.menu'],
            ['code' => 'hr.employees.onboard', 'name' => '办理入职', 'type' => 'api', 'route' => '/api/v1/hr/employees/{employee}/onboard', 'method' => 'POST', 'icon' => '', 'sort' => 10, 'parent' => 'hr.onboarding.menu'],
            ['code' => 'hr.resignations.index', 'name' => '离职列表', 'type' => 'api', 'route' => '/api/v1/hr/resignations', 'method' => 'GET', 'icon' => '', 'sort' => 10, 'parent' => 'hr.resignations.menu'],
            ['code' => 'hr.resignations.store', 'name' => '发起离职', 'type' => 'api', 'route' => '/api/v1/hr/resignations', 'method' => 'POST', 'icon' => '', 'sort' => 20, 'parent' => 'hr.resignations.menu'],
            ['code' => 'hr.resignations.approve', 'name' => '批准离职', 'type' => 'api', 'route' => '/api/v1/hr/resignations/{resignation}/approve', 'method' => 'POST', 'icon' => '', 'sort' => 30, 'parent' => 'hr.resignations.menu'],
            ['code' => 'hr.resignations.reject', 'name' => '驳回离职', 'type' => 'api', 'route' => '/api/v1/hr/resignations/{resignation}/reject', 'method' => 'POST', 'icon' => '', 'sort' => 40, 'parent' => 'hr.resignations.menu'],
            ['code' => 'hr.resignations.complete', 'name' => '办结离职', 'type' => 'api', 'route' => '/api/v1/hr/resignations/{resignation}/complete', 'method' => 'POST', 'icon' => '', 'sort' => 50, 'parent' => 'hr.resignations.menu'],
            ['code' => 'hr.contracts.index', 'name' => '合同列表', 'type' => 'api', 'route' => '/api/v1/hr/contracts', 'method' => 'GET', 'icon' => '', 'sort' => 10, 'parent' => 'hr.contracts.menu'],
            ['code' => 'hr.contracts.store', 'name' => '签订合同', 'type' => 'api', 'route' => '/api/v1/hr/contracts', 'method' => 'POST', 'icon' => '', 'sort' => 20, 'parent' => 'hr.contracts.menu'],
            ['code' => 'hr.contracts.terminate', 'name' => '终止合同', 'type' => 'api', 'route' => '/api/v1/hr/contracts/{contract}/terminate', 'method' => 'POST', 'icon' => '', 'sort' => 30, 'parent' => 'hr.contracts.menu'],
            ['code' => 'hr.attendance.index', 'name' => '考勤列表', 'type' => 'api', 'route' => '/api/v1/hr/attendance', 'method' => 'GET', 'icon' => '', 'sort' => 10, 'parent' => 'hr.attendance.menu'],
            ['code' => 'hr.attendance.store', 'name' => '登记考勤', 'type' => 'api', 'route' => '/api/v1/hr/attendance', 'method' => 'POST', 'icon' => '', 'sort' => 20, 'parent' => 'hr.attendance.menu'],
            ['code' => 'hr.leaves.index', 'name' => '请假列表', 'type' => 'api', 'route' => '/api/v1/hr/leaves', 'method' => 'GET', 'icon' => '', 'sort' => 10, 'parent' => 'hr.leaves.menu'],
            ['code' => 'hr.leaves.store', 'name' => '提交请假', 'type' => 'api', 'route' => '/api/v1/hr/leaves', 'method' => 'POST', 'icon' => '', 'sort' => 20, 'parent' => 'hr.leaves.menu'],
            ['code' => 'hr.leaves.approve', 'name' => '批准请假', 'type' => 'api', 'route' => '/api/v1/hr/leaves/{leave}/approve', 'method' => 'POST', 'icon' => '', 'sort' => 30, 'parent' => 'hr.leaves.menu'],
            ['code' => 'hr.leaves.reject', 'name' => '驳回请假', 'type' => 'api', 'route' => '/api/v1/hr/leaves/{leave}/reject', 'method' => 'POST', 'icon' => '', 'sort' => 40, 'parent' => 'hr.leaves.menu'],
        ];
    }
}
