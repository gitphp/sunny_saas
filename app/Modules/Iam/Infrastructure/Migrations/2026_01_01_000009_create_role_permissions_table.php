<?php

/**
 * 创建角色权限关联表
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
        Schema::create('role_permissions', function (Blueprint $table): void {
            $table->comment('角色与权限关联');
            $table->id()->comment('主键');
            $table->unsignedBigInteger('tenant_id')->default(0)->comment('租户主键');
            $table->unsignedBigInteger('role_id')->default(0)->comment('角色主键');
            $table->unsignedBigInteger('permission_id')->default(0)->comment('权限主键');
            $table->timestamp('created_at')->useCurrent()->comment('创建时间');
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate()->comment('更新时间');
            $table->timestamp('deleted_at')->nullable()->comment('删除时间，未删除时为空');
            $table->unique(['role_id', 'permission_id'], 'uk_role_permissions');
            $table->index('permission_id', 'idx_role_permissions_permission');
            $table->foreign('tenant_id', 'fk_role_permissions_tenant')->references('id')->on('tenants')->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('role_id', 'fk_role_permissions_role')->references('id')->on('roles')->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('permission_id', 'fk_role_permissions_permission')->references('id')->on('permissions')->restrictOnDelete()->restrictOnUpdate();
        });
    }

    /**
     * 回滚本次迁移
     */
    public function down(): void
    {
        Schema::dropIfExists('role_permissions');
    }
};

