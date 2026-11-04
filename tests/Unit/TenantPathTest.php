<?php

/**
 * 租户文件路径测试
 *
 * @package     Tests\Unit
 * @license     MIT License (MIT)
 * @author      Sunny Yang <githup@163.com> QQ4873473
 * @copyright   Copyright (c) 2024 YourCompany. All rights reserved.
 * @link        http://www.budff.com
 */

namespace Tests\Unit;

use App\Shared\Tenancy\TenantPath;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * 租户文件路径测试
 */
class TenantPathTest extends TestCase
{
    /**
     * 空路径片段必须被拒绝
     */
    #[Test]
    public function it_rejects_empty_segments(): void
    {
        $this->expectException(InvalidArgumentException::class);
        (new TenantPath)->make(3, '');
    }

    /**
     * 上级目录片段必须被拒绝
     */
    #[Test]
    public function it_rejects_parent_directory_segments(): void
    {
        $this->expectException(InvalidArgumentException::class);
        (new TenantPath)->make(3, 'a/../b');
    }
}

