<?php

/**
 * 模块边界测试
 *
 * @package     Tests\Unit
 * @license     MIT License (MIT)
 * @author      Sunny Yang <githup@163.com> QQ4873473
 * @copyright   Copyright (c) 2024 YourCompany. All rights reserved.
 * @link        http://www.budff.com
 */

namespace Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use Tests\TestCase;

/**
 * 模块边界测试
 */
class ArchitectureBoundaryTest extends TestCase
{
    /**
     * 跨模块引用只能指向应用层契约
     */
    #[Test]
    public function cross_module_references_only_use_application_contracts(): void
    {
        $root = app_path('Modules');
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root));
        $violations = [];

        /** @var SplFileInfo $file */
        foreach ($iterator as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $relative = str_replace('\\', '/', substr($file->getPathname(), strlen($root) + 1));
            $current = explode('/', $relative)[0];
            $contents = (string) file_get_contents($file->getPathname());

            if (! preg_match_all('/App\\\\Modules\\\\([A-Za-z0-9_]+)\\\\([A-Za-z0-9_\\\\]+)/', $contents, $matches, PREG_SET_ORDER)) {
                continue;
            }

            foreach ($matches as $match) {
                $usedModule = $match[1];
                $remainder = $match[2];

                if ($usedModule === $current) {
                    continue;
                }

                if (! str_starts_with($remainder, 'Application\\Contracts\\')) {
                    $violations[] = $relative.' -> App\\Modules\\'.$usedModule.'\\'.$remainder;
                }
            }
        }

        $this->assertSame([], $violations, "跨模块只能引用 Application\\Contracts：\n".implode("\n", $violations));
    }
}

