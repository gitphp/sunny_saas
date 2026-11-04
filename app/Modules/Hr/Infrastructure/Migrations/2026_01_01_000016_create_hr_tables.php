<?php

/**
 * 创建人事表、注册人事模块并纳入标准版套餐
 *
 * @package     app\Modules\Hr\Infrastructure\Migrations
 * @license     MIT License (MIT)
 * @author      Sunny Yang <githup@163.com> QQ4873473
 * @copyright   Copyright (c) 2024 YourCompany. All rights reserved.
 * @link        http://www.budff.com
 */

use App\Modules\Hr\Infrastructure\Catalog\HrCatalog;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 创建或变更表结构
     */
    public function up(): void
    {
        $now = now();
        $module = HrCatalog::module();

        if (! DB::table('modules')->where('code', $module['code'])->whereNull('deleted_at')->exists()) {
            DB::table('modules')->insert([
                'code' => $module['code'],
                'name' => $module['name'],
                'description' => $module['description'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $standardId = DB::table('plans')->where('code', 'standard')->whereNull('deleted_at')->value('id');

        if ($standardId) {
            DB::table('plans')->where('id', $standardId)->update([
                'description' => '适合持续协作的企业。包含身份与访问和人事，有效成员上限 200 人。',
                'updated_at' => $now,
            ]);

            $linked = DB::table('plan_modules')
                ->where('plan_id', $standardId)
                ->where('module_code', 'hr')
                ->whereNull('deleted_at')
                ->exists();

            if (! $linked) {
                DB::table('plan_modules')->insert([
                    'plan_id' => $standardId,
                    'module_code' => 'hr',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        Schema::create('hr_positions', function (Blueprint $table): void {
            $table->comment('岗位');
            $table->id()->comment('主键');
            $table->unsignedBigInteger('tenant_id')->default(0)->comment('租户主键');
            $table->unsignedBigInteger('department_id')->nullable()->comment('所属部门编号，不挂部门时为空');
            $table->string('name', 100)->default('')->comment('岗位名称');
            $table->string('code', 64)->default('')->comment('岗位编码');
            $table->integer('sort')->default(0)->comment('排序');
            $table->unsignedTinyInteger('status')->default(1)->comment('状态：1启用 0停用');
            $table->timestamp('created_at')->useCurrent()->comment('创建时间');
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate()->comment('更新时间');
            $table->timestamp('deleted_at')->nullable()->comment('删除时间，未删除时为空');
            $table->unique(['tenant_id', 'code'], 'uk_hr_positions_code');
            $table->index(['tenant_id', 'department_id'], 'idx_hr_positions_department');
            $table->foreign('tenant_id', 'fk_hr_positions_tenant')->references('id')->on('tenants')->restrictOnDelete()->restrictOnUpdate();
        });

        Schema::create('hr_employees', function (Blueprint $table): void {
            $table->comment('员工档案');
            $table->id()->comment('主键');
            $table->unsignedBigInteger('tenant_id')->default(0)->comment('租户主键');
            $table->string('employee_no', 32)->default('')->comment('工号');
            $table->string('name', 100)->default('')->comment('姓名');
            $table->unsignedTinyInteger('gender')->default(0)->comment('性别：0未填 1男 2女');
            $table->string('mobile', 32)->nullable()->comment('手机号，可选');
            $table->string('email', 255)->default('')->comment('邮箱');
            $table->unsignedBigInteger('department_id')->nullable()->comment('部门编号，尚未分配时为空');
            $table->unsignedBigInteger('position_id')->nullable()->comment('岗位主键，尚未分配时为空');
            $table->unsignedBigInteger('user_id')->nullable()->comment('绑定的全局账号，没有账号时为空');
            $table->unsignedTinyInteger('status')->default(0)->comment('状态：0待入职 1在职 2离职');
            $table->date('hired_on')->nullable()->comment('入职日期，尚未入职时为空');
            $table->date('left_on')->nullable()->comment('离职日期，尚未离职时为空');
            $table->timestamp('created_at')->useCurrent()->comment('创建时间');
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate()->comment('更新时间');
            $table->timestamp('deleted_at')->nullable()->comment('删除时间，未删除时为空');
            $table->unique(['tenant_id', 'employee_no'], 'uk_hr_employees_no');
            $table->index(['tenant_id', 'department_id'], 'idx_hr_employees_department');
            $table->index(['tenant_id', 'status'], 'idx_hr_employees_status');
            $table->index('user_id', 'idx_hr_employees_user');
            $table->foreign('tenant_id', 'fk_hr_employees_tenant')->references('id')->on('tenants')->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('position_id', 'fk_hr_employees_position')->references('id')->on('hr_positions')->restrictOnDelete()->restrictOnUpdate();
        });

        Schema::create('hr_contracts', function (Blueprint $table): void {
            $table->comment('劳动合同');
            $table->id()->comment('主键');
            $table->unsignedBigInteger('tenant_id')->default(0)->comment('租户主键');
            $table->unsignedBigInteger('employee_id')->default(0)->comment('员工主键');
            $table->string('contract_no', 64)->default('')->comment('合同编号');
            $table->string('type', 16)->default('fixed')->comment('类型：fixed固定期限 open无固定期限 intern实习');
            $table->date('start_on')->comment('开始日期');
            $table->date('end_on')->nullable()->comment('结束日期，无固定期限时为空');
            $table->unsignedTinyInteger('status')->default(1)->comment('状态：1履行中 2已终止');
            $table->date('signed_on')->comment('签订日期');
            $table->timestamp('created_at')->useCurrent()->comment('创建时间');
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate()->comment('更新时间');
            $table->timestamp('deleted_at')->nullable()->comment('删除时间，未删除时为空');
            $table->unique(['tenant_id', 'contract_no'], 'uk_hr_contracts_no');
            $table->index(['tenant_id', 'employee_id'], 'idx_hr_contracts_employee');
            $table->foreign('tenant_id', 'fk_hr_contracts_tenant')->references('id')->on('tenants')->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('employee_id', 'fk_hr_contracts_employee')->references('id')->on('hr_employees')->restrictOnDelete()->restrictOnUpdate();
        });

        Schema::create('hr_attendance_records', function (Blueprint $table): void {
            $table->comment('考勤记录');
            $table->id()->comment('主键');
            $table->unsignedBigInteger('tenant_id')->default(0)->comment('租户主键');
            $table->unsignedBigInteger('employee_id')->default(0)->comment('员工主键');
            $table->date('work_on')->comment('考勤日期');
            $table->time('check_in')->nullable()->comment('上班时间，缺卡时为空');
            $table->time('check_out')->nullable()->comment('下班时间，缺卡时为空');
            $table->string('status', 16)->default('normal')->comment('状态：normal正常 late迟到 absent缺勤 leave请假');
            $table->timestamp('created_at')->useCurrent()->comment('创建时间');
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate()->comment('更新时间');
            $table->timestamp('deleted_at')->nullable()->comment('删除时间，未删除时为空');
            $table->unique(['tenant_id', 'employee_id', 'work_on'], 'uk_hr_attendance_day');
            $table->foreign('tenant_id', 'fk_hr_attendance_tenant')->references('id')->on('tenants')->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('employee_id', 'fk_hr_attendance_employee')->references('id')->on('hr_employees')->restrictOnDelete()->restrictOnUpdate();
        });

        Schema::create('hr_leave_requests', function (Blueprint $table): void {
            $table->comment('请假申请');
            $table->id()->comment('主键');
            $table->unsignedBigInteger('tenant_id')->default(0)->comment('租户主键');
            $table->unsignedBigInteger('employee_id')->default(0)->comment('员工主键');
            $table->string('leave_type', 16)->default('personal')->comment('类型：annual年假 sick病假 personal事假 other其他');
            $table->date('start_on')->comment('开始日期');
            $table->date('end_on')->comment('结束日期');
            $table->string('reason', 255)->default('')->comment('事由');
            $table->unsignedTinyInteger('status')->default(0)->comment('状态：0待审批 1已批准 2已驳回');
            $table->unsignedBigInteger('reviewer_user_id')->default(0)->comment('审批人用户主键，未审批为 0');
            $table->timestamp('reviewed_at')->nullable()->comment('审批时间，未审批时为空');
            $table->timestamp('created_at')->useCurrent()->comment('创建时间');
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate()->comment('更新时间');
            $table->timestamp('deleted_at')->nullable()->comment('删除时间，未删除时为空');
            $table->index(['tenant_id', 'employee_id', 'status'], 'idx_hr_leaves_employee');
            $table->foreign('tenant_id', 'fk_hr_leaves_tenant')->references('id')->on('tenants')->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('employee_id', 'fk_hr_leaves_employee')->references('id')->on('hr_employees')->restrictOnDelete()->restrictOnUpdate();
        });

        Schema::create('hr_resignations', function (Blueprint $table): void {
            $table->comment('离职申请');
            $table->id()->comment('主键');
            $table->unsignedBigInteger('tenant_id')->default(0)->comment('租户主键');
            $table->unsignedBigInteger('employee_id')->default(0)->comment('员工主键');
            $table->string('reason', 255)->default('')->comment('离职原因');
            $table->date('apply_on')->comment('申请日期');
            $table->date('last_on')->comment('最后工作日');
            $table->unsignedTinyInteger('status')->default(0)->comment('状态：0待审批 1已批准 2已驳回 3已办结');
            $table->unsignedBigInteger('reviewer_user_id')->default(0)->comment('审批人用户主键，未审批为 0');
            $table->timestamp('reviewed_at')->nullable()->comment('审批时间，未审批时为空');
            $table->timestamp('created_at')->useCurrent()->comment('创建时间');
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate()->comment('更新时间');
            $table->timestamp('deleted_at')->nullable()->comment('删除时间，未删除时为空');
            $table->index(['tenant_id', 'employee_id', 'status'], 'idx_hr_resignations_employee');
            $table->foreign('tenant_id', 'fk_hr_resignations_tenant')->references('id')->on('tenants')->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('employee_id', 'fk_hr_resignations_employee')->references('id')->on('hr_employees')->restrictOnDelete()->restrictOnUpdate();
        });

        $this->seedPermissions($now);
    }

    /**
     * 写入人事权限并授予已有管理员
     */
    private function seedPermissions(mixed $now): void
    {
        $ids = [];

        foreach (HrCatalog::permissions() as $permission) {
            $existing = DB::table('permissions')->where('code', $permission['code'])->whereNull('deleted_at')->value('id');

            if ($existing) {
                $ids[$permission['code']] = (int) $existing;

                continue;
            }

            $parentId = $permission['parent'] === null ? null : ($ids[$permission['parent']] ?? null);
            $ids[$permission['code']] = (int) DB::table('permissions')->insertGetId([
                'parent_id' => $parentId,
                'type' => $permission['type'],
                'code' => $permission['code'],
                'name' => $permission['name'],
                'route' => $permission['route'],
                'method' => $permission['method'],
                'icon' => $permission['icon'],
                'sort' => $permission['sort'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $adminRoles = DB::table('roles')->where('code', 'admin')->whereNull('deleted_at')->get();

        foreach ($adminRoles as $role) {
            foreach ($ids as $permissionId) {
                $exists = DB::table('role_permissions')
                    ->where('role_id', $role->id)
                    ->where('permission_id', $permissionId)
                    ->exists();

                if ($exists) {
                    continue;
                }

                DB::table('role_permissions')->insert([
                    'tenant_id' => $role->tenant_id,
                    'role_id' => $role->id,
                    'permission_id' => $permissionId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    /**
     * 回滚本次迁移
     */
    public function down(): void
    {
        Schema::dropIfExists('hr_resignations');
        Schema::dropIfExists('hr_leave_requests');
        Schema::dropIfExists('hr_attendance_records');
        Schema::dropIfExists('hr_contracts');
        Schema::dropIfExists('hr_employees');
        Schema::dropIfExists('hr_positions');
        DB::table('plan_modules')->where('module_code', 'hr')->delete();
        DB::table('modules')->where('code', 'hr')->delete();
        DB::table('permissions')->where('code', 'like', 'hr%')->delete();
    }
};
