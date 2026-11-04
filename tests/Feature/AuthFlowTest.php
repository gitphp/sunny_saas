<?php

/**
 * 注册登录与追踪号测试
 *
 * @package     Tests\Feature
 * @license     MIT License (MIT)
 * @author      Sunny Yang <githup@163.com> QQ4873473
 * @copyright   Copyright (c) 2024 YourCompany. All rights reserved.
 * @link        http://www.budff.com
 */

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * 注册登录与追踪号测试
 */
class AuthFlowTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 准备测试环境
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->withHeader('Origin', 'http://localhost');
    }

    /**
     * 注册、登录和退出的响应带有追踪号
     */
    #[Test]
    public function register_login_and_logout_include_trace_id(): void
    {
        $traceId = '11111111-1111-4111-8111-111111111111';

        $register = $this->withHeader('X-Request-Id', $traceId)->postJson('/api/v1/auth/register', [
            'name' => '张三',
            'email' => 'zhangsan@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $register->assertCreated()
            ->assertJsonPath('code', 0)
            ->assertJsonPath('message', 'ok')
            ->assertJsonPath('trace_id', $traceId)
            ->assertJsonPath('data.user.email', 'zhangsan@example.com');

        $this->postJson('/api/v1/auth/logout')->assertOk();

        $login = $this->withHeader('X-Request-Id', $traceId)->postJson('/api/v1/auth/login', [
            'email' => 'zhangsan@example.com',
            'password' => 'password123',
            'device_name' => 'phpunit',
        ]);

        $login->assertOk()
            ->assertJsonPath('code', 0)
            ->assertJsonPath('trace_id', $traceId)
            ->assertJsonPath('data.user.email', 'zhangsan@example.com');
        $this->assertNotEmpty($login->json('data.token'));

        $logout = $this->withHeader('X-Request-Id', $traceId)->postJson('/api/v1/auth/logout');
        $logout->assertOk()->assertJsonPath('trace_id', $traceId)->assertJsonPath('code', 0);

        // PHPUnit 复用同一进程，Sanctum RequestGuard 会缓存上一请求的用户。
        // 清掉守卫后，下一次请求才按会话重新解析，和 PHP-FPM 每次新进程一致。
        $this->app['auth']->forgetGuards();

        $this->getJson('/api/v1/me')->assertUnauthorized()->assertJsonPath('code', 40100);
    }

    /**
     * 非法请求号会换成新的追踪号
     */
    #[Test]
    public function invalid_request_id_is_replaced_with_uuid(): void
    {
        $response = $this->withHeader('X-Request-Id', 'not-a-uuid')->postJson('/api/v1/auth/login', [
            'email' => 'missing@example.com',
            'password' => 'password123',
        ]);

        $response->assertUnauthorized();
        $this->assertNotSame('not-a-uuid', $response->json('trace_id'));
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[1-8][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/',
            (string) $response->json('trace_id')
        );
    }
}

