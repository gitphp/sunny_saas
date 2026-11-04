<?php

/**
 * 创建全局权限目录表
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
        Schema::create('permissions', function (Blueprint $table): void {
            $table->comment('全局权限目录，含菜单、按钮与接口');
            $table->id()->comment('主键');
            $table->unsignedBigInteger('parent_id')->nullable()->comment('上级权限，顶级为空');
            $table->enum('type', ['menu', 'button', 'api'])->default('api')->comment('权限类型：menu菜单 button按钮 api接口');
            $table->string('code', 128)->default('')->comment('权限编码');
            $table->string('name', 100)->default('')->comment('权限名称');
            $table->string('route', 255)->default('')->comment('前端路由或接口路径');
            $table->string('method', 16)->default('')->comment('HTTP 方法，菜单和按钮为空');
            $table->string('icon', 64)->default('')->comment('图标');
            $table->integer('sort')->default(0)->comment('排序');
            $table->timestamp('created_at')->useCurrent()->comment('创建时间');
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate()->comment('更新时间');
            $table->timestamp('deleted_at')->nullable()->comment('删除时间，未删除时为空');
            $table->unique('code', 'uk_permissions_code');
            $table->index('parent_id', 'idx_permissions_parent');
            $table->index('type', 'idx_permissions_type');
            $table->foreign('parent_id', 'fk_permissions_parent')->references('id')->on('permissions')->restrictOnDelete()->restrictOnUpdate();
        });

        $ids = [];
        $now = now();

        foreach (IamCatalog::permissions() as $permission) {
            $parentId = $permission['parent'] === null ? null : ($ids[$permission['parent']] ?? null);

            $ids[$permission['code']] = DB::table('permissions')->insertGetId([
                'parent_id' => $parentId,
                'type' => $permission['type'],
                'code' => $permission['code'],
                'name' => $permission['name'],
                'route' => $permission['route'],
                'method' => $permission['method'],
                'icon' => $permission['icon'],
                'sort' => $permission['sort'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    /**
     * 回滚本次迁移
     */
    public function down(): void
    {
        Schema::dropIfExists('permissions');
    }
};

