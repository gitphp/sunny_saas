<?php

/**
 * 创建租户表
 *
 * @package     app\Modules\Tenant\Infrastructure\Migrations
 * @license     MIT License (MIT)
 * @author      Sunny Yang <githup@163.com> QQ4873473
 * @copyright   Copyright (c) 2024 YourCompany. All rights reserved.
 * @link        http://www.budff.com
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Query\Expression;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 创建或变更表结构
     */
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table): void {
            $table->comment('租户');
            $table->id()->comment('主键');
            $table->string('name', 100)->default('')->comment('租户名称');
            $table->string('slug', 64)->default('')->comment('租户标识，请求头 X-Tenant 使用');
            $table->unsignedTinyInteger('status')->default(1)->comment('状态：1启用 0停用');
            $table->json('settings')->default(new Expression('(JSON_OBJECT())'))->comment('租户设置');
            $table->timestamp('created_at')->useCurrent()->comment('创建时间');
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate()->comment('更新时间');
            $table->timestamp('deleted_at')->nullable()->comment('删除时间，未删除时为空');
            $table->unique('slug', 'uk_tenants_slug');
            $table->index('status', 'idx_tenants_status');
        });
    }

    /**
     * 回滚本次迁移
     */
    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};

