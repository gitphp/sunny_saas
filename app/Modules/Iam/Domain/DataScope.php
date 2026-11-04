<?php

/**
 * 数据范围枚举
 *
 * @package     App\Modules\Iam\Domain
 * @license     MIT License (MIT)
 * @author      Sunny Yang <githup@163.com> QQ4873473
 * @copyright   Copyright (c) 2024 YourCompany. All rights reserved.
 * @link        http://www.budff.com
 */

namespace App\Modules\Iam\Domain;

/**
 * 数据范围枚举
 */
final class DataScope
{
    public const ALL = 'all';

    public const DEPARTMENT_AND_CHILDREN = 'department_and_children';

    public const DEPARTMENT = 'department';

    public const SELF = 'self';

    public const CUSTOM = 'custom';

    /**
     * 返回全部合法取值
     *
     * @return list<string>
     */
    public static function all(): array
    {
        return [
            self::ALL,
            self::DEPARTMENT_AND_CHILDREN,
            self::DEPARTMENT,
            self::SELF,
            self::CUSTOM,
        ];
    }
}

