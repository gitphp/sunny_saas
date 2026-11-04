<?php

/**
 * 创建全局模块目录表
 *
 * @package     app\Modules\Iam\Infrastructure\Migrations
 * @license     MIT License (MIT)
 * @author      Sunny Yang <githup@163.com> QQ4873473
 * @copyright   Copyright (c) 2024 YourCompany. All rights reserved.
 * @link        http://www.budff.com
 */

use App\Modules\Iam\Infrastructure\Catalog\IamCatalog;
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
        Schema::create('modules', function (Blueprint $table): void {
            $table->comment('全局模块目录');
            $table->id()->comment('主键');
            $table->string('code', 64)->default('')->comment('模块编码');
            $table->string('name', 100)->default('')->comment('模块名称');
            $table->string('description', 255)->default('')->comment('模块说明');
            $table->timestamp('created_at')->useCurrent()->comment('创建时间');
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate()->comment('更新时间');
            $table->timestamp('deleted_at')->nullable()->comment('删除时间，未删除时为空');
            $table->unique('code', 'uk_modules_code');
        });

        $module = IamCatalog::module();
        $now = now();

        DB::table('modules')->insert([
            'code' => $module['code'],
            'name' => $module['name'],
            'description' => $module['description'],
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    /**
     * 回滚本次迁移
     */
    public function down(): void
    {
        Schema::dropIfExists('modules');
    }
};

