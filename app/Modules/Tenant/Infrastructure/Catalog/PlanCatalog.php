<?php

/**
 * 租户套餐目录
 *
 * @package     App\Modules\Tenant\Infrastructure\Catalog
 * @license     MIT License (MIT)
 * @author      Sunny Yang <githup@163.com> QQ4873473
 * @copyright   Copyright (c) 2024 YourCompany. All rights reserved.
 * @link        http://www.budff.com
 */

namespace App\Modules\Tenant\Infrastructure\Catalog;

/**
 * 租户套餐目录
 */
final class PlanCatalog
{
    /**
     * 返回需要写入的套餐。模块编码必须已经在模块目录中。
     *
     * @return list<array{code: string, name: string, description: string, member_limit: int, sort: int, modules: list<string>}>
     */
    public static function definitions(): array
    {
        return [
            [
                'code' => 'basic',
                'name' => '基础版',
                'description' => '适合开始使用的企业。包含身份与访问，有效成员上限 20 人。',
                'member_limit' => 20,
                'sort' => 10,
                'modules' => ['iam'],
            ],
            [
                'code' => 'standard',
                'name' => '标准版',
                'description' => '适合持续协作的企业。包含身份与访问，有效成员上限 200 人。',
                'member_limit' => 200,
                'sort' => 20,
                'modules' => ['iam'],
            ],
        ];
    }
}
