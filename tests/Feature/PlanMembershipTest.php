<?php

/**
 * 套餐、成员加入与企业隔离测试
 *
 * @package     Tests\Feature
 * @license     MIT License (MIT)
 * @author      Sunny Yang <githup@163.com> QQ4873473
 * @copyright   Copyright (c) 2024 YourCompany. All rights reserved.
 * @link        http://www.budff.com
 */

namespace Tests\Feature;

use App\Modules\Iam\Infrastructure\Persistence\Module;
use App\Modules\Iam\Infrastructure\Persistence\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * 套餐、成员加入与企业隔离测试
 */
class PlanMembershipTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 两个企业不能看到彼此的成员，套餐约束成员数和模块开通
     */
    #[Test]
    public function plans_limit_members_and_tenants_cannot_read_each_other(): void
    {
        $ownerA = User::factory()->create();
        $ownerB = User::factory()->create();

        $this->actingAs($ownerA, 'sanctum')
            ->getJson('/api/v1/plans')
            ->assertOk()
            ->assertJsonFragment(['code' => 'basic'])
            ->assertJsonFragment(['code' => 'standard']);

        $this->actingAs($ownerA, 'sanctum')
            ->postJson('/api/v1/tenants', ['name' => '甲企业', 'slug' => 'alpha'])
            ->assertCreated()
            ->assertJsonPath('data.plan_code', 'basic');

        $this->actingAs($ownerB, 'sanctum')
            ->postJson('/api/v1/tenants', ['name' => '乙企业', 'slug' => 'beta', 'plan_code' => 'standard'])
            ->assertCreated()
            ->assertJsonPath('data.plan_code', 'standard');

        $member = $this->actingAs($ownerA, 'sanctum')
            ->withHeader('X-Tenant', 'alpha')
            ->postJson('/api/v1/iam/members', [
                'name' => '甲同事',
                'email' => 'colleague@example.com',
                'password' => 'password123',
            ])
            ->assertCreated()
            ->assertJsonPath('data.email', 'colleague@example.com')
            ->json('data');

        $this->actingAs($ownerB, 'sanctum')
            ->withHeader('X-Tenant', 'beta')
            ->getJson('/api/v1/iam/members')
            ->assertOk()
            ->assertJsonMissing(['email' => 'colleague@example.com']);

        $this->actingAs($ownerB, 'sanctum')
            ->withHeader('X-Tenant', 'beta')
            ->deleteJson('/api/v1/iam/members/'.$member['id'])
            ->assertNotFound();

        $ownerMemberId = $this->actingAs($ownerA, 'sanctum')
            ->withHeader('X-Tenant', 'alpha')
            ->getJson('/api/v1/iam/members')
            ->json('data.0.id');

        $this->actingAs($ownerA, 'sanctum')
            ->withHeader('X-Tenant', 'alpha')
            ->deleteJson('/api/v1/iam/members/'.$ownerMemberId)
            ->assertStatus(422);

        DB::table('plans')->where('code', 'basic')->update(['member_limit' => 1]);

        $this->actingAs($ownerA, 'sanctum')
            ->withHeader('X-Tenant', 'alpha')
            ->postJson('/api/v1/iam/members', [
                'name' => '超出',
                'email' => 'overflow@example.com',
                'password' => 'password123',
            ])
            ->assertStatus(422)
            ->assertJsonPath('message', '已达到套餐成员上限');

        Module::query()->create([
            'code' => 'extra',
            'name' => '扩展',
            'description' => '套餐外模块',
        ]);

        $this->actingAs($ownerA, 'sanctum')
            ->withHeader('X-Tenant', 'alpha')
            ->putJson('/api/v1/iam/modules/extra', ['status' => 1])
            ->assertStatus(422)
            ->assertJsonPath('message', '当前套餐不包含该模块');

        $this->actingAs($ownerA, 'sanctum')
            ->withHeader('X-Tenant', 'alpha')
            ->putJson('/api/v1/iam/plan', ['code' => 'standard'])
            ->assertOk()
            ->assertJsonPath('data.code', 'standard');

        DB::table('plans')->where('code', 'basic')->update(['member_limit' => 20]);

        DB::table('plan_modules')->where('module_code', 'iam')->whereIn('plan_id', function ($query): void {
            $query->select('id')->from('plans')->where('code', 'basic');
        })->delete();

        $this->actingAs($ownerA, 'sanctum')
            ->withHeader('X-Tenant', 'alpha')
            ->putJson('/api/v1/iam/plan', ['code' => 'basic'])
            ->assertOk();

        $this->actingAs($ownerA, 'sanctum')
            ->withHeader('X-Tenant', 'alpha')
            ->getJson('/api/v1/iam/menus')
            ->assertForbidden()
            ->assertJsonPath('code', 40302);
    }
}
