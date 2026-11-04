<?php

/**
 * 缓存、队列和文件路径的租户隔离测试
 *
 * @package     Tests\Feature
 * @license     MIT License (MIT)
 * @author      Sunny Yang <githup@163.com> QQ4873473
 * @copyright   Copyright (c) 2024 YourCompany. All rights reserved.
 * @link        http://www.budff.com
 */

namespace Tests\Feature;

use App\Modules\Iam\Infrastructure\Persistence\User;
use App\Shared\Tenancy\TenantCache;
use App\Shared\Tenancy\TenantContext;
use App\Shared\Tenancy\TenantPath;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use Tests\Fixtures\ProbeTenantJob;
use Tests\TestCase;

/**
 * 缓存、队列和文件路径的租户隔离测试
 */
class CacheQueuePathTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 不同租户的相同逻辑键不能互相命中
     */
    #[Test]
    public function cache_keys_are_isolated_by_tenant_prefix(): void
    {
        Cache::flush();
        $user = User::factory()->create();
        $alpha = $this->actingAs($user, 'sanctum')->postJson('/api/v1/tenants', ['name' => '甲', 'slug' => 'cache-a'])->assertCreated()->json('data');
        $beta = $this->actingAs($user, 'sanctum')->postJson('/api/v1/tenants', ['name' => '乙', 'slug' => 'cache-b'])->assertCreated()->json('data');

        $context = app(TenantContext::class);
        $cache = app(TenantCache::class);

        $context->set((int) $alpha['id'], 'cache-a');
        $this->assertSame('t:'.$alpha['id'].':color', $cache->key('color'));
        $this->assertStringContainsString('t:'.$alpha['id'].':', $cache->key('color'));
        $cache->put('color', 'red');

        $context->set((int) $beta['id'], 'cache-b');
        $this->assertSame('t:'.$beta['id'].':color', $cache->key('color'));
        $cache->put('color', 'blue');
        $this->assertSame('blue', $cache->get('color'));

        $context->set((int) $alpha['id'], 'cache-a');
        $this->assertSame('red', $cache->get('color'));
        $context->clear();
    }

    /**
     * 队列任务会恢复租户，结束后清空上下文
     */
    #[Test]
    public function queue_job_restores_tenant_and_clears_context(): void
    {
        $this->artisan('queue:clear', ['connection' => 'redis', '--force' => true]);
        $user = User::factory()->create();
        $tenant = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/tenants', ['name' => '队列', 'slug' => 'queue-co'])
            ->assertCreated()
            ->json('data');

        ProbeTenantJob::$seen = null;
        ProbeTenantJob::$payloadTenantId = null;

        $context = app(TenantContext::class);
        $context->set((int) $tenant['id'], 'queue-co');
        ProbeTenantJob::dispatch();
        $context->clear();

        $this->artisan('queue:work', [
            'connection' => 'redis',
            '--once' => true,
            '--stop-when-empty' => true,
        ])->assertSuccessful();

        $this->assertSame((int) $tenant['id'], ProbeTenantJob::$payloadTenantId);
        $this->assertSame((int) $tenant['id'], ProbeTenantJob::$seen);
        $this->assertNull(app(TenantContext::class)->id());
    }

    /**
     * 文件路径带租户编号并拒绝穿越
     */
    #[Test]
    public function tenant_path_includes_tenant_id_and_rejects_dot_dot(): void
    {
        $path = app(TenantPath::class);

        $this->assertSame('15/contracts/a.pdf', $path->make(15, 'contracts', 'a.pdf'));

        $this->expectException(InvalidArgumentException::class);
        $path->make(15, 'contracts', '..', 'secret');
    }
}

