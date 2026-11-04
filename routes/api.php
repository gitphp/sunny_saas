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

use App\Modules\Iam\Presentation\Http\Controllers\AuthController;
use App\Modules\Iam\Presentation\Http\Controllers\DepartmentController;
use App\Modules\Iam\Presentation\Http\Controllers\MemberController;
use App\Modules\Iam\Presentation\Http\Controllers\MenuController;
use App\Modules\Iam\Presentation\Http\Controllers\ModuleController;
use App\Modules\Iam\Presentation\Http\Controllers\PermissionController;
use App\Modules\Iam\Presentation\Http\Controllers\RoleController;
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
    // 角色同步比成员更新更具体，必须先注册。
    Route::put('members/{member}/roles', [MemberController::class, 'syncRoles'])->middleware('permission:iam.members.roles.sync');
    Route::put('members/{member}', [MemberController::class, 'update'])->middleware('permission:iam.members.update');

    Route::get('modules', [ModuleController::class, 'index'])->middleware('permission:iam.modules.index');
    Route::put('modules/{moduleCode}', [ModuleController::class, 'update'])->middleware('permission:iam.modules.update');

    Route::get('permissions', [PermissionController::class, 'index'])->middleware('permission:iam.permissions.index');
});
