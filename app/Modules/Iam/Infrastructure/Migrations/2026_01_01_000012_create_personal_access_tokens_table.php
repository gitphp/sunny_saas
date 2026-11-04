<?php

/**
 * 创建个人访问令牌表
 *
 * @package     app\Modules\Iam\Infrastructure\Migrations
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
        Schema::create('personal_access_tokens', function (Blueprint $table): void {
            $table->comment('Sanctum 个人访问令牌，主体为全局用户');
            $table->id()->comment('主键');
            $table->string('tokenable_type', 255)->default('')->comment('令牌主体类型');
            $table->unsignedBigInteger('tokenable_id')->default(0)->comment('令牌主体主键');
            $table->string('name', 255)->default('')->comment('令牌名称');
            $table->string('token', 64)->default('')->comment('令牌哈希');
            $table->json('abilities')->default(new Expression('(JSON_ARRAY())'))->comment('令牌能力');
            $table->timestamp('last_used_at')->nullable()->comment('最近使用时间，未使用时为空');
            $table->timestamp('expires_at')->nullable()->comment('过期时间，不设过期时为空');
            $table->timestamp('created_at')->useCurrent()->comment('创建时间');
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate()->comment('更新时间');
            $table->index(['tokenable_type', 'tokenable_id'], 'idx_pat_tokenable');
            $table->unique('token', 'uk_personal_access_tokens_token');
            $table->index('expires_at', 'idx_personal_access_tokens_expires_at');
        });
    }

    /**
     * 回滚本次迁移
     */
    public function down(): void
    {
        Schema::dropIfExists('personal_access_tokens');
    }
};

