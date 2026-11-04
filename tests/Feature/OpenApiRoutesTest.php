<?php

/**
 * OpenAPI 覆盖本阶段路由的测试
 *
 * @package     Tests\Feature
 * @license     MIT License (MIT)
 * @author      Sunny Yang <githup@163.com> QQ4873473
 * @copyright   Copyright (c) 2024 YourCompany. All rights reserved.
 * @link        http://www.budff.com
 */

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * OpenAPI 覆盖本阶段路由的测试
 */
class OpenApiRoutesTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 本阶段接口都出现在 OpenAPI 文档中
     */
    #[Test]
    public function phase_one_routes_appear_in_openapi(): void
    {
        $document = $this->getJson('/docs/api.json');
        $document->assertOk();
        $paths = array_keys($document->json('paths') ?? []);
        $this->assertNotEmpty($paths, 'OpenAPI 未包含任何路径');

        $uris = collect(Route::getRoutes())
            ->map(fn ($route) => '/'.ltrim($route->uri(), '/'))
            ->filter(fn (string $uri) => str_starts_with($uri, '/api/v1/'))
            ->unique()
            ->values();

        $this->assertNotEmpty($uris);

        foreach ($uris as $uri) {
            $withoutApi = preg_replace('#^/api#', '', $uri) ?: '/';
            $withoutPrefix = preg_replace('#^/api/v1#', '', $uri) ?: '/';
            $this->assertTrue(
                in_array($uri, $paths, true)
                    || in_array($withoutApi, $paths, true)
                    || in_array($withoutPrefix, $paths, true),
                'OpenAPI 缺少路由 '.$uri.'，已有：'.implode(', ', $paths)
            );
        }
    }
}

