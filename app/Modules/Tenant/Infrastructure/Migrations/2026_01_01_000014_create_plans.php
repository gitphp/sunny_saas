<?php

/**
 * 创建套餐并给已有租户补上基础版
 *
 * @package     app\Modules\Tenant\Infrastructure\Migrations
 * @license     MIT License (MIT)
 * @author      Sunny Yang <githup@163.com> QQ4873473
 * @copyright   Copyright (c) 2024 YourCompany. All rights reserved.
 * @link        http://www.budff.com
 */

use App\Modules\Tenant\Infrastructure\Catalog\PlanCatalog;
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
        Schema::create('plans', function (Blueprint $table): void {
            $table->comment('全局套餐目录，不隶属单一租户');
            $table->id()->comment('主键');
            $table->string('code', 32)->default('')->comment('套餐编码');
            $table->string('name', 100)->default('')->comment('套餐名称');
            $table->string('description', 255)->default('')->comment('套餐说明');
            $table->unsignedInteger('member_limit')->default(0)->comment('有效成员上限，0 表示不限制');
            $table->unsignedTinyInteger('status')->default(1)->comment('状态：1可售 0停售');
            $table->integer('sort')->default(0)->comment('排序');
            $table->timestamp('created_at')->useCurrent()->comment('创建时间');
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate()->comment('更新时间');
            $table->timestamp('deleted_at')->nullable()->comment('删除时间，未删除时为空');
            $table->unique('code', 'uk_plans_code');
            $table->index('status', 'idx_plans_status');
        });

        Schema::create('plan_modules', function (Blueprint $table): void {
            $table->comment('套餐包含的模块');
            $table->id()->comment('主键');
            $table->unsignedBigInteger('plan_id')->default(0)->comment('套餐主键');
            $table->string('module_code', 64)->default('')->comment('模块编码');
            $table->timestamp('created_at')->useCurrent()->comment('创建时间');
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate()->comment('更新时间');
            $table->timestamp('deleted_at')->nullable()->comment('删除时间，未删除时为空');
            $table->unique(['plan_id', 'module_code'], 'uk_plan_modules');
            $table->foreign('plan_id', 'fk_plan_modules_plan')->references('id')->on('plans')->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('module_code', 'fk_plan_modules_module')->references('code')->on('modules')->restrictOnDelete()->restrictOnUpdate();
        });

        $now = now();
        $basicId = 0;

        foreach (PlanCatalog::definitions() as $definition) {
            $planId = (int) DB::table('plans')->insertGetId([
                'code' => $definition['code'],
                'name' => $definition['name'],
                'description' => $definition['description'],
                'member_limit' => $definition['member_limit'],
                'status' => 1,
                'sort' => $definition['sort'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            if ($definition['code'] === 'basic') {
                $basicId = $planId;
            }

            foreach ($definition['modules'] as $moduleCode) {
                DB::table('plan_modules')->insert([
                    'plan_id' => $planId,
                    'module_code' => $moduleCode,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        Schema::table('tenants', function (Blueprint $table): void {
            $table->unsignedBigInteger('plan_id')->default(0)->comment('套餐主键')->after('status');
            $table->index('plan_id', 'idx_tenants_plan');
        });

        if ($basicId > 0) {
            DB::table('tenants')->where('plan_id', 0)->update(['plan_id' => $basicId]);
        }

        Schema::table('tenants', function (Blueprint $table): void {
            $table->foreign('plan_id', 'fk_tenants_plan')->references('id')->on('plans')->restrictOnDelete()->restrictOnUpdate();
        });

        DB::statement('ALTER TABLE tenants ALTER plan_id DROP DEFAULT');
    }

    /**
     * 回滚本次迁移
     */
    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table): void {
            $table->dropForeign('fk_tenants_plan');
            $table->dropIndex('idx_tenants_plan');
            $table->dropColumn('plan_id');
        });

        Schema::dropIfExists('plan_modules');
        Schema::dropIfExists('plans');
    }
};
