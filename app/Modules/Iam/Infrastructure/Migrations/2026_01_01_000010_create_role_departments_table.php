<?php

/**
 * 创建角色自定义部门表
 *
 * @package     app\Modules\Iam\Infrastructure\Migrations
 * @license     MIT License (MIT)
 * @author      Sunny Yang <githup@163.com> QQ4873473
 * @copyright   Copyright (c) 2024 YourCompany. All rights reserved.
 * @link        http://www.budff.com
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 创建或变更表结构
     */
    public function up(): void
    {
        Schema::create('role_departments', function (Blueprint $table): void {
            $table->comment('自定义数据范围的角色部门');
            $table->id()->comment('主键');
            $table->unsignedBigInteger('tenant_id')->default(0)->comment('租户主键');
            $table->unsignedBigInteger('role_id')->default(0)->comment('角色主键');
            $table->unsignedBigInteger('department_id')->default(0)->comment('部门主键');
            $table->timestamp('created_at')->useCurrent()->comment('创建时间');
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate()->comment('更新时间');
            $table->timestamp('deleted_at')->nullable()->comment('删除时间，未删除时为空');
            $table->unique(['role_id', 'department_id'], 'uk_role_departments');
            $table->index('department_id', 'idx_role_departments_department');
            $table->foreign('tenant_id', 'fk_role_departments_tenant')->references('id')->on('tenants')->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('role_id', 'fk_role_departments_role')->references('id')->on('roles')->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('department_id', 'fk_role_departments_department')->references('id')->on('departments')->restrictOnDelete()->restrictOnUpdate();
        });
    }

    /**
     * 回滚本次迁移
     */
    public function down(): void
    {
        Schema::dropIfExists('role_departments');
    }
};

