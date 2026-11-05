<?php

/**
 * 创建审批表、注册审批模块并纳入标准版套餐
 *
 * @package     app\Modules\Approval\Infrastructure\Migrations
 * @license     MIT License (MIT)
 * @author      Sunny Yang <githup@163.com> QQ4873473
 * @copyright   Copyright (c) 2024 YourCompany. All rights reserved.
 * @link        http://www.budff.com
 */

use App\Modules\Approval\Infrastructure\Catalog\ApprovalCatalog;
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
        $module = ApprovalCatalog::module();

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
                'description' => '适合持续协作的企业。包含身份与访问、人事和审批，有效成员上限 200 人。',
                'updated_at' => $now,
            ]);

            $linked = DB::table('plan_modules')
                ->where('plan_id', $standardId)
                ->where('module_code', 'approval')
                ->whereNull('deleted_at')
                ->exists();

            if (! $linked) {
                DB::table('plan_modules')->insert([
                    'plan_id' => $standardId,
                    'module_code' => 'approval',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        Schema::create('approval_groups', function (Blueprint $table): void {
            $table->comment('审批流程分组');
            $table->id()->comment('主键');
            $table->unsignedBigInteger('tenant_id')->default(0)->comment('租户主键');
            $table->string('name', 100)->default('')->comment('分组名称');
            $table->integer('sort')->default(0)->comment('排序');
            $table->unsignedTinyInteger('status')->default(1)->comment('状态：1启用 0停用');
            $table->timestamp('created_at')->useCurrent()->comment('创建时间');
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate()->comment('更新时间');
            $table->timestamp('deleted_at')->nullable()->comment('删除时间，未删除时为空');
            $table->index(['tenant_id', 'sort'], 'idx_approval_groups_sort');
            $table->foreign('tenant_id', 'fk_approval_groups_tenant')->references('id')->on('tenants')->restrictOnDelete()->restrictOnUpdate();
        });

        Schema::create('approval_definitions', function (Blueprint $table): void {
            $table->comment('审批流程定义');
            $table->id()->comment('主键');
            $table->unsignedBigInteger('tenant_id')->default(0)->comment('租户主键');
            $table->unsignedBigInteger('group_id')->nullable()->comment('分组主键，未分组时为空');
            $table->string('code', 32)->default('')->comment('流程编码');
            $table->string('name', 100)->default('')->comment('流程名称');
            $table->string('icon', 32)->default('user')->comment('图标');
            $table->string('description', 255)->default('')->comment('说明');
            $table->unsignedTinyInteger('status')->default(1)->comment('状态：1启用 0停用');
            $table->unsignedTinyInteger('allow_revoke')->default(1)->comment('是否允许撤销审批中的申请：1允许 0不允许');
            $table->unsignedBigInteger('current_version_id')->nullable()->comment('当前已发布版本，尚未发布时为空');
            $table->timestamp('created_at')->useCurrent()->comment('创建时间');
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate()->comment('更新时间');
            $table->timestamp('deleted_at')->nullable()->comment('删除时间，未删除时为空');
            $table->unique(['tenant_id', 'code'], 'uk_approval_def_code');
            $table->index(['tenant_id', 'group_id'], 'idx_approval_def_group');
            $table->foreign('tenant_id', 'fk_approval_def_tenant')->references('id')->on('tenants')->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('group_id', 'fk_approval_def_group')->references('id')->on('approval_groups')->restrictOnDelete()->restrictOnUpdate();
        });

        Schema::create('approval_versions', function (Blueprint $table): void {
            $table->comment('审批流程版本');
            $table->id()->comment('主键');
            $table->unsignedBigInteger('tenant_id')->default(0)->comment('租户主键');
            $table->unsignedBigInteger('definition_id')->default(0)->comment('流程定义主键');
            $table->unsignedInteger('version_no')->default(1)->comment('版本号');
            $table->unsignedTinyInteger('status')->default(0)->comment('状态：0草稿 1已发布 2已归档');
            $table->json('form_schema')->comment('表单字段');
            $table->json('flow_schema')->comment('流程图');
            $table->timestamp('published_at')->nullable()->comment('发布时间，草稿为空');
            $table->timestamp('created_at')->useCurrent()->comment('创建时间');
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate()->comment('更新时间');
            $table->timestamp('deleted_at')->nullable()->comment('删除时间，未删除时为空');
            $table->unique(['tenant_id', 'definition_id', 'version_no'], 'uk_approval_version');
            $table->foreign('tenant_id', 'fk_approval_ver_tenant')->references('id')->on('tenants')->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('definition_id', 'fk_approval_ver_def')->references('id')->on('approval_definitions')->restrictOnDelete()->restrictOnUpdate();
        });

        Schema::create('approval_instances', function (Blueprint $table): void {
            $table->comment('审批单');
            $table->id()->comment('主键');
            $table->unsignedBigInteger('tenant_id')->default(0)->comment('租户主键');
            $table->unsignedBigInteger('definition_id')->default(0)->comment('流程定义主键');
            $table->unsignedBigInteger('version_id')->default(0)->comment('发起时锁定的版本');
            $table->string('serial_no', 32)->default('')->comment('审批单号');
            $table->string('title', 200)->default('')->comment('标题');
            $table->unsignedBigInteger('applicant_user_id')->default(0)->comment('申请人用户主键');
            $table->unsignedTinyInteger('status')->default(1)->comment('状态：1审批中 2已通过 3已驳回 4已撤销');
            $table->json('form_data')->comment('表单数据');
            $table->string('current_node_key', 32)->default('')->comment('当前节点编码，结束后为空');
            $table->string('source_module', 32)->default('')->comment('来源模块编码，审批中心发起时为空');
            $table->unsignedBigInteger('source_id')->default(0)->comment('来源业务主键，没有时为 0');
            $table->timestamp('submitted_at')->useCurrent()->comment('提交时间');
            $table->timestamp('finished_at')->nullable()->comment('结束时间，未结束时为空');
            $table->timestamp('created_at')->useCurrent()->comment('创建时间');
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate()->comment('更新时间');
            $table->timestamp('deleted_at')->nullable()->comment('删除时间，未删除时为空');
            $table->unique(['tenant_id', 'serial_no'], 'uk_approval_inst_serial');
            $table->index(['tenant_id', 'applicant_user_id'], 'idx_approval_inst_applicant');
            $table->index(['tenant_id', 'status'], 'idx_approval_inst_status');
            $table->foreign('tenant_id', 'fk_approval_inst_tenant')->references('id')->on('tenants')->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('definition_id', 'fk_approval_inst_def')->references('id')->on('approval_definitions')->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('version_id', 'fk_approval_inst_ver')->references('id')->on('approval_versions')->restrictOnDelete()->restrictOnUpdate();
        });

        Schema::create('approval_tasks', function (Blueprint $table): void {
            $table->comment('审批任务和抄送');
            $table->id()->comment('主键');
            $table->unsignedBigInteger('tenant_id')->default(0)->comment('租户主键');
            $table->unsignedBigInteger('instance_id')->default(0)->comment('审批单主键');
            $table->string('node_key', 32)->default('')->comment('节点编码');
            $table->string('node_name', 100)->default('')->comment('节点名称');
            $table->unsignedBigInteger('assignee_user_id')->default(0)->comment('处理人用户主键');
            $table->string('task_type', 16)->default('approve')->comment('类型：approve审批 cc抄送');
            $table->unsignedTinyInteger('status')->default(0)->comment('状态：0待处理 1已处理 2已跳过');
            $table->timestamp('acted_at')->nullable()->comment('处理时间，未处理时为空');
            $table->timestamp('created_at')->useCurrent()->comment('创建时间');
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate()->comment('更新时间');
            $table->timestamp('deleted_at')->nullable()->comment('删除时间，未删除时为空');
            $table->index(['tenant_id', 'assignee_user_id', 'status'], 'idx_approval_tasks_assignee');
            $table->index(['instance_id', 'node_key'], 'idx_approval_tasks_node');
            $table->foreign('tenant_id', 'fk_approval_tasks_tenant')->references('id')->on('tenants')->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('instance_id', 'fk_approval_tasks_inst')->references('id')->on('approval_instances')->restrictOnDelete()->restrictOnUpdate();
        });

        Schema::create('approval_records', function (Blueprint $table): void {
            $table->comment('审批记录');
            $table->id()->comment('主键');
            $table->unsignedBigInteger('tenant_id')->default(0)->comment('租户主键');
            $table->unsignedBigInteger('instance_id')->default(0)->comment('审批单主键');
            $table->unsignedBigInteger('task_id')->default(0)->comment('任务主键，没有对应任务时为 0');
            $table->string('node_key', 32)->default('')->comment('节点编码');
            $table->unsignedBigInteger('actor_user_id')->default(0)->comment('操作人用户主键');
            $table->string('action', 16)->default('')->comment('动作：submit approve reject cc revoke comment');
            $table->string('comment', 500)->default('')->comment('意见');
            $table->timestamp('created_at')->useCurrent()->comment('创建时间');
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate()->comment('更新时间');
            $table->timestamp('deleted_at')->nullable()->comment('删除时间，未删除时为空');
            $table->index(['instance_id', 'id'], 'idx_approval_records_inst');
            $table->foreign('tenant_id', 'fk_approval_records_tenant')->references('id')->on('tenants')->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('instance_id', 'fk_approval_records_inst')->references('id')->on('approval_instances')->restrictOnDelete()->restrictOnUpdate();
        });

        $this->seedPermissions($now);
    }

    /**
     * 写入审批权限并授予已有管理员
     */
    private function seedPermissions(mixed $now): void
    {
        $ids = [];

        foreach (ApprovalCatalog::permissions() as $permission) {
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
        Schema::dropIfExists('approval_records');
        Schema::dropIfExists('approval_tasks');
        Schema::dropIfExists('approval_instances');
        Schema::dropIfExists('approval_versions');
        Schema::dropIfExists('approval_definitions');
        Schema::dropIfExists('approval_groups');
        DB::table('plan_modules')->where('module_code', 'approval')->delete();
        DB::table('modules')->where('code', 'approval')->delete();
        DB::table('permissions')->where('code', 'like', 'approval%')->delete();
    }
};
