<?php

/**
 * 创建租户模块开通表
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
        Schema::create('tenant_modules', function (Blueprint $table): void {
            $table->comment('租户模块开通记录');
            $table->id()->comment('主键');
            $table->unsignedBigInteger('tenant_id')->default(0)->comment('租户主键');
            $table->string('module_code', 64)->default('')->comment('模块编码');
            $table->unsignedTinyInteger('status')->default(0)->comment('开通状态：1已开通 0未开通');
            $table->timestamp('enabled_at')->useCurrent()->comment('开通时间');
            $table->timestamp('created_at')->useCurrent()->comment('创建时间');
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate()->comment('更新时间');
            $table->timestamp('deleted_at')->nullable()->comment('删除时间，未删除时为空');
            $table->unique(['tenant_id', 'module_code'], 'uk_tenant_modules');
            $table->index(['tenant_id', 'status'], 'idx_tenant_modules_status');
            $table->foreign('tenant_id', 'fk_tenant_modules_tenant')->references('id')->on('tenants')->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('module_code', 'fk_tenant_modules_module')->references('code')->on('modules')->restrictOnDelete()->restrictOnUpdate();
        });
    }

    /**
     * 回滚本次迁移
     */
    public function down(): void
    {
        Schema::dropIfExists('tenant_modules');
    }
};

