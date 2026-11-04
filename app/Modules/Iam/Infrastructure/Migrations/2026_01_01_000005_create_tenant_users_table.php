<?php

/**
 * 创建租户成员表
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
        Schema::create('tenant_users', function (Blueprint $table): void {
            $table->comment('租户成员，一个用户可加入多个租户');
            $table->id()->comment('主键');
            $table->unsignedBigInteger('tenant_id')->default(0)->comment('租户主键');
            $table->unsignedBigInteger('user_id')->default(0)->comment('用户主键');
            $table->unsignedBigInteger('department_id')->nullable()->comment('所属部门，尚未归属时为空');
            $table->unsignedTinyInteger('status')->default(1)->comment('状态：1有效 0停用');
            $table->unsignedTinyInteger('is_owner')->default(0)->comment('是否租户所有者：1是 0否');
            $table->timestamp('joined_at')->useCurrent()->comment('加入时间');
            $table->timestamp('created_at')->useCurrent()->comment('创建时间');
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate()->comment('更新时间');
            $table->timestamp('deleted_at')->nullable()->comment('删除时间，未删除时为空');
            $table->unique(['tenant_id', 'user_id'], 'uk_tenant_users_tenant_user');
            $table->index('user_id', 'idx_tenant_users_user');
            $table->index(['tenant_id', 'department_id'], 'idx_tenant_users_department');
            $table->foreign('tenant_id', 'fk_tenant_users_tenant')->references('id')->on('tenants')->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('user_id', 'fk_tenant_users_user')->references('id')->on('users')->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('department_id', 'fk_tenant_users_department')->references('id')->on('departments')->restrictOnDelete()->restrictOnUpdate();
        });
    }

    /**
     * 回滚本次迁移
     */
    public function down(): void
    {
        Schema::dropIfExists('tenant_users');
    }
};

