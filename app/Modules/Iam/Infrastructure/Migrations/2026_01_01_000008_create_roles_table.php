<?php

/**
 * 创建角色表
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
        Schema::create('roles', function (Blueprint $table): void {
            $table->comment('租户角色');
            $table->id()->comment('主键');
            $table->unsignedBigInteger('tenant_id')->default(0)->comment('租户主键');
            $table->string('code', 64)->default('')->comment('角色编码');
            $table->string('name', 100)->default('')->comment('角色名称');
            $table->string('data_scope', 32)->default('self')->comment('数据范围：all、department_and_children、department、self、custom');
            $table->timestamp('created_at')->useCurrent()->comment('创建时间');
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate()->comment('更新时间');
            $table->timestamp('deleted_at')->nullable()->comment('删除时间，未删除时为空');
            $table->unique(['tenant_id', 'code'], 'uk_roles_tenant_code');
            $table->index(['tenant_id', 'data_scope'], 'idx_roles_scope');
            $table->foreign('tenant_id', 'fk_roles_tenant')->references('id')->on('tenants')->restrictOnDelete()->restrictOnUpdate();
        });
    }

    /**
     * 回滚本次迁移
     */
    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};

