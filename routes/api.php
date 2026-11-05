<?php

/**
 * 版本化 API 路由
 *
 * @package     routes
 * @license     MIT License (MIT)
 * @author      Sunny Yang <githup@163.com> QQ4873473
 * @copyright   Copyright (c) 2024 YourCompany. All rights reserved.
 * @link        http://www.budff.com
 */

use App\Modules\Approval\Presentation\Http\Controllers\DefinitionController;
use App\Modules\Approval\Presentation\Http\Controllers\GroupController;
use App\Modules\Approval\Presentation\Http\Controllers\InstanceController;
use App\Modules\Hr\Presentation\Http\Controllers\AttendanceController;
use App\Modules\Hr\Presentation\Http\Controllers\ContractController;
use App\Modules\Hr\Presentation\Http\Controllers\EmployeeController;
use App\Modules\Hr\Presentation\Http\Controllers\LeaveController;
use App\Modules\Hr\Presentation\Http\Controllers\OrganizationController;
use App\Modules\Hr\Presentation\Http\Controllers\PositionController;
use App\Modules\Hr\Presentation\Http\Controllers\ResignationController;
use App\Modules\Iam\Presentation\Http\Controllers\AuthController;
use App\Modules\Iam\Presentation\Http\Controllers\DepartmentController;
use App\Modules\Iam\Presentation\Http\Controllers\MemberController;
use App\Modules\Iam\Presentation\Http\Controllers\MenuController;
use App\Modules\Iam\Presentation\Http\Controllers\ModuleController;
use App\Modules\Iam\Presentation\Http\Controllers\PermissionController;
use App\Modules\Iam\Presentation\Http\Controllers\RoleController;
use App\Modules\Tenant\Presentation\Http\Controllers\PlanController;
use App\Modules\Tenant\Presentation\Http\Controllers\TenantController;
use App\Shared\Presentation\Middleware\LogContextMiddleware;
use Illuminate\Support\Facades\Route;

// 注册、登录不要求租户；退出只要求登录。
Route::prefix('auth')->group(function (): void {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout'])->middleware(['auth:sanctum', LogContextMiddleware::class]);
});

// 当前用户和租户列表不带租户上下文，创建租户本身就是进入第一个租户。
Route::middleware(['auth:sanctum', LogContextMiddleware::class])->group(function (): void {
    Route::get('me', [AuthController::class, 'me']);
    Route::get('me/tenants', [TenantController::class, 'mine']);
    Route::get('plans', [PlanController::class, 'index']);
    Route::post('tenants', [TenantController::class, 'store']);
});

// 身份与访问接口必须先解析租户，再确认模块已开通，最后按权限码放行。
Route::middleware(['auth:sanctum', 'tenant', LogContextMiddleware::class, 'module:iam'])->prefix('iam')->group(function (): void {
    Route::get('menus', [MenuController::class, 'index'])->middleware('permission:iam.menus.index');
    Route::get('me', [MenuController::class, 'me'])->middleware('permission:iam.me.show');

    Route::get('departments', [DepartmentController::class, 'index'])->middleware('permission:iam.departments.index');
    Route::post('departments', [DepartmentController::class, 'store'])->middleware('permission:iam.departments.store');
    Route::get('departments/{department}', [DepartmentController::class, 'show'])->middleware('permission:iam.departments.show');
    Route::put('departments/{department}', [DepartmentController::class, 'update'])->middleware('permission:iam.departments.update');
    Route::delete('departments/{department}', [DepartmentController::class, 'destroy'])->middleware('permission:iam.departments.destroy');

    Route::get('roles', [RoleController::class, 'index'])->middleware('permission:iam.roles.index');
    Route::post('roles', [RoleController::class, 'store'])->middleware('permission:iam.roles.store');
    Route::get('roles/{role}', [RoleController::class, 'show'])->middleware('permission:iam.roles.show');
    Route::put('roles/{role}', [RoleController::class, 'update'])->middleware('permission:iam.roles.update');
    Route::delete('roles/{role}', [RoleController::class, 'destroy'])->middleware('permission:iam.roles.destroy');

    Route::get('members', [MemberController::class, 'index'])->middleware('permission:iam.members.index');
    Route::post('members', [MemberController::class, 'store'])->middleware('permission:iam.members.store');
    // 角色同步比成员更新更具体，必须先注册。
    Route::put('members/{member}/roles', [MemberController::class, 'syncRoles'])->middleware('permission:iam.members.roles.sync');
    Route::put('members/{member}', [MemberController::class, 'update'])->middleware('permission:iam.members.update');
    Route::delete('members/{member}', [MemberController::class, 'destroy'])->middleware('permission:iam.members.destroy');

    Route::get('modules', [ModuleController::class, 'index'])->middleware('permission:iam.modules.index');
    Route::put('modules/{moduleCode}', [ModuleController::class, 'update'])->middleware('permission:iam.modules.update');

    Route::get('permissions', [PermissionController::class, 'index'])->middleware('permission:iam.permissions.index');

    Route::get('plan', [PlanController::class, 'show'])->middleware('permission:iam.plans.show');
    Route::put('plan', [PlanController::class, 'update'])->middleware('permission:iam.plans.update');
});

// 人事接口同样先确认租户和模块开通，再按权限码放行。
Route::middleware(['auth:sanctum', 'tenant', LogContextMiddleware::class, 'module:hr'])->prefix('hr')->group(function (): void {
    Route::get('organization', [OrganizationController::class, 'index'])->middleware('permission:hr.organization.index');

    Route::get('positions', [PositionController::class, 'index'])->middleware('permission:hr.positions.index');
    Route::post('positions', [PositionController::class, 'store'])->middleware('permission:hr.positions.store');
    Route::put('positions/{position}', [PositionController::class, 'update'])->middleware('permission:hr.positions.update');
    Route::delete('positions/{position}', [PositionController::class, 'destroy'])->middleware('permission:hr.positions.destroy');

    Route::get('employees', [EmployeeController::class, 'index'])->middleware('permission:hr.employees.index');
    Route::post('employees', [EmployeeController::class, 'store'])->middleware('permission:hr.employees.store');
    Route::post('employees/{employee}/onboard', [EmployeeController::class, 'onboard'])->middleware('permission:hr.employees.onboard');
    Route::get('employees/{employee}', [EmployeeController::class, 'show'])->middleware('permission:hr.employees.show');
    Route::put('employees/{employee}', [EmployeeController::class, 'update'])->middleware('permission:hr.employees.update');

    Route::get('resignations', [ResignationController::class, 'index'])->middleware('permission:hr.resignations.index');
    Route::post('resignations', [ResignationController::class, 'store'])->middleware('permission:hr.resignations.store');
    Route::post('resignations/{resignation}/approve', [ResignationController::class, 'approve'])->middleware('permission:hr.resignations.approve');
    Route::post('resignations/{resignation}/reject', [ResignationController::class, 'reject'])->middleware('permission:hr.resignations.reject');
    Route::post('resignations/{resignation}/complete', [ResignationController::class, 'complete'])->middleware('permission:hr.resignations.complete');

    Route::get('contracts', [ContractController::class, 'index'])->middleware('permission:hr.contracts.index');
    Route::post('contracts', [ContractController::class, 'store'])->middleware('permission:hr.contracts.store');
    Route::post('contracts/{contract}/terminate', [ContractController::class, 'terminate'])->middleware('permission:hr.contracts.terminate');

    Route::get('attendance', [AttendanceController::class, 'index'])->middleware('permission:hr.attendance.index');
    Route::post('attendance', [AttendanceController::class, 'store'])->middleware('permission:hr.attendance.store');

    Route::get('leaves', [LeaveController::class, 'index'])->middleware('permission:hr.leaves.index');
    Route::post('leaves', [LeaveController::class, 'store'])->middleware('permission:hr.leaves.store');
    Route::post('leaves/{leave}/approve', [LeaveController::class, 'approve'])->middleware('permission:hr.leaves.approve');
    Route::post('leaves/{leave}/reject', [LeaveController::class, 'reject'])->middleware('permission:hr.leaves.reject');
});

// 审批接口先确认租户和模块开通，再按权限码放行。
Route::middleware(['auth:sanctum', 'tenant', LogContextMiddleware::class, 'module:approval'])->prefix('approval')->group(function (): void {
    Route::get('catalog', [DefinitionController::class, 'catalog'])->middleware('permission:approval.catalog.index');
    Route::get('members', [DefinitionController::class, 'members'])->middleware('permission:approval.members.index');

    Route::get('groups', [GroupController::class, 'index'])->middleware('permission:approval.groups.index');
    Route::post('groups', [GroupController::class, 'store'])->middleware('permission:approval.groups.store');
    Route::put('groups/{group}', [GroupController::class, 'update'])->middleware('permission:approval.groups.update');
    Route::delete('groups/{group}', [GroupController::class, 'destroy'])->middleware('permission:approval.groups.destroy');

    Route::get('definitions', [DefinitionController::class, 'index'])->middleware('permission:approval.definitions.index');
    Route::post('definitions', [DefinitionController::class, 'store'])->middleware('permission:approval.definitions.store');
    Route::get('definitions/{definition}', [DefinitionController::class, 'show'])->middleware('permission:approval.definitions.show');
    Route::put('definitions/{definition}', [DefinitionController::class, 'update'])->middleware('permission:approval.definitions.update');
    Route::post('definitions/{definition}/publish', [DefinitionController::class, 'publish'])->middleware('permission:approval.definitions.publish');

    Route::get('tasks', [InstanceController::class, 'tasks'])->middleware('permission:approval.tasks.index');
    Route::get('instances', [InstanceController::class, 'mine'])->middleware('permission:approval.instances.mine');
    Route::post('instances', [InstanceController::class, 'store'])->middleware('permission:approval.instances.store');
    Route::get('data', [InstanceController::class, 'data'])->middleware('permission:approval.data.index');
    Route::get('instances/{instance}', [InstanceController::class, 'show'])->middleware('permission:approval.instances.show');
    Route::post('instances/{instance}/approve', [InstanceController::class, 'approve'])->middleware('permission:approval.instances.approve');
    Route::post('instances/{instance}/reject', [InstanceController::class, 'reject'])->middleware('permission:approval.instances.reject');
    Route::post('instances/{instance}/revoke', [InstanceController::class, 'revoke'])->middleware('permission:approval.instances.revoke');
    Route::post('instances/{instance}/comment', [InstanceController::class, 'comment'])->middleware('permission:approval.instances.comment');
});
