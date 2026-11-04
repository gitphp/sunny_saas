<?php

/**
 * 创建部门表
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
        Schema::create('departments', function (Blueprint $table): void {
            $table->comment('部门，隶属租户，供数据范围使用');
            $table->id()->comment('主键');
            $table->unsignedBigInteger('tenant_id')->default(0)->comment('租户主键');
            $table->unsignedBigInteger('parent_id')->nullable()->comment('上级部门，根部门为空');
            $table->string('name', 100)->default('')->comment('部门名称');
            $table->string('path', 255)->default('')->comment('物化路径，形如 /1/5');
            $table->integer('sort')->default(0)->comment('排序');
            $table->timestamp('created_at')->useCurrent()->comment('创建时间');
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate()->comment('更新时间');
            $table->timestamp('deleted_at')->nullable()->comment('删除时间，未删除时为空');
            $table->index(['tenant_id', 'parent_id'], 'idx_departments_parent');
            $table->index(['tenant_id', 'path'], 'idx_departments_path');
            $table->foreign('tenant_id', 'fk_departments_tenant')->references('id')->on('tenants')->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('parent_id', 'fk_departments_parent')->references('id')->on('departments')->restrictOnDelete()->restrictOnUpdate();
        });
    }

    /**
     * 回滚本次迁移
     */
    public function down(): void
    {
        Schema::dropIfExists('departments');
    }
};

