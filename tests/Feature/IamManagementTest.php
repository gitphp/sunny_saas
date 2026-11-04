<?php

/**
 * 权限目录、成员部门与模块开通测试
 *
 * @package     Tests\Feature
 * @license     MIT License (MIT)
 * @author      Sunny Yang <githup@163.com> QQ4873473
 * @copyright   Copyright (c) 2024 YourCompany. All rights reserved.
 * @link        http://www.budff.com
 */

namespace Tests\Feature;

use App\Modules\Iam\Infrastructure\Persistence\Permission;
use App\Modules\Iam\Infrastructure\Persistence\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * 权限目录、成员部门与模块开通测试
 */
class IamManagementTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 权限目录、成员部门和模块开关可以正常使用
     */
    #[Test]
    public function permission_catalog_member_department_and_module_switch_are_available(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/tenants', ['name' => '演示', 'slug' => 'demo'])
            ->assertCreated();

        $permissions = $this->actingAs($user, 'sanctum')
            ->withHeader('X-Tenant', 'demo')
            ->getJson('/api/v1/iam/permissions')
            ->assertOk()
            ->json('data');

        $this->assertContains('iam.permissions.menu', array_column($permissions, 'code'));

        $department = $this->actingAs($user, 'sanctum')
            ->withHeader('X-Tenant', 'demo')
            ->postJson('/api/v1/iam/departments', ['name' => '研发'])
            ->assertCreated()
            ->json('data');

        $memberId = $this->actingAs($user, 'sanctum')
            ->withHeader('X-Tenant', 'demo')
            ->getJson('/api/v1/iam/members')
            ->assertOk()
            ->json('data.0.id');

        $this->actingAs($user, 'sanctum')
            ->withHeader('X-Tenant', 'demo')
            ->putJson('/api/v1/iam/members/'.$memberId, ['department_id' => $department['id']])
            ->assertOk()
            ->assertJsonPath('data.department_id', $department['id']);

        $this->actingAs($user, 'sanctum')
            ->withHeader('X-Tenant', 'demo')
            ->putJson('/api/v1/iam/modules/iam', ['status' => 0])
            ->assertStatus(422)
            ->assertJsonPath('code', 42200);

        $this->actingAs($user, 'sanctum')
            ->withHeader('X-Tenant', 'demo')
            ->putJson('/api/v1/iam/modules/missing', ['status' => 1])
            ->assertNotFound();

        $this->assertNotNull(Permission::query()->where('code', 'iam.members.update')->value('id'));
    }
}

