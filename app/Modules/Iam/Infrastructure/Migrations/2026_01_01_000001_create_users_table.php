<?php

/**
 * 创建全局用户表
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
        Schema::create('users', function (Blueprint $table): void {
            $table->comment('全局用户账号，不隶属单一租户');
            $table->id()->comment('主键');
            $table->string('name', 100)->default('')->comment('姓名');
            $table->string('email', 255)->default('')->comment('邮箱，业务必填，空字符串由校验拒绝');
            $table->string('mobile', 32)->nullable()->comment('手机号，可选联系方式');
            $table->string('password', 255)->default('')->comment('密码哈希');
            $table->unsignedTinyInteger('status')->default(1)->comment('状态：1启用 0停用');
            $table->string('remember_token', 100)->default('')->comment('记住登录令牌');
            $table->timestamp('created_at')->useCurrent()->comment('创建时间');
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate()->comment('更新时间');
            $table->timestamp('deleted_at')->nullable()->comment('删除时间，未删除时为空');
            $table->unique('email', 'uk_users_email');
            $table->unique('mobile', 'uk_users_mobile');
        });
    }

    /**
     * 回滚本次迁移
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};

