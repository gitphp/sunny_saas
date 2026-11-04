<?php

/**
 * 跨租户读写与数据范围测试
 *
 * @package     Tests\Feature
 * @license     MIT License (MIT)
 * @author      Sunny Yang <githup@163.com> QQ4873473
 * @copyright   Copyright (c) 2024 YourCompany. All rights reserved.
 * @link        http://www.budff.com
 */

namespace Tests\Feature;

use App\Modules\Iam\Infrastructure\Persistence\Department;
use App\Modules\Iam\Infrastructure\Persistence\Permission;
use App\Modules\Iam\Infrastructure\Persistence\Role;
use App\Modules\Iam\Infrastructure\Persistence\RolePermission;
use App\Modules\Iam\Infrastructure\Persistence\TenantModule;
use App\Modules\Iam\Infrastructure\Persistence\TenantUser;
use App\Modules\Iam\Infrastructure\Persistence\TenantUserRole;
use App\Modules\Iam\Infrastructure\Persistence\User;
use App\Shared\Exceptions\MissingTenantContextException;
use App\Shared\Tenancy\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * 跨租户读写与数据范围测试
 */
class TenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 同一用户在两个租户看到的部门互不可见
     */
    #[Test]
    public function one_user_cannot_see_another_tenants_departments(): void
    {
        $user = User::factory()->create();
        $alpha = $this->createTenant($user, '阿尔法', 'alpha');
        $beta = $this->createTenant($user, '贝塔', 'beta');

        $this->actingAs($user, 'sanctum')
            ->withHeader('X-Tenant', 'alpha')
            ->postJson('/api/v1/iam/departments', ['name' => '阿尔法部'])
            ->assertCreated();

        $this->actingAs($user, 'sanctum')
            ->withHeader('X-Tenant', 'beta')
            ->postJson('/api/v1/iam/departments', ['name' => '贝塔部'])
            ->assertCreated();

        $alphaList = $this->actingAs($user, 'sanctum')
            ->withHeader('X-Tenant', 'alpha')
            ->getJson('/api/v1/iam/departments')
            ->assertOk()
            ->json('data');

        $names = array_column($alphaList, 'name');
        $this->assertSame(['阿尔法部'], $names);
        $this->assertSame($alpha['id'], $alphaList[0]['tenant_id']);
        $this->assertNotSame($beta['id'], $alphaList[0]['tenant_id']);
    }

    /**
     * 用别的租户上下文访问部门主键返回 404
     */
    #[Test]
    public function cross_tenant_department_id_returns_404(): void
    {
        $user = User::factory()->create();
        $this->createTenant($user, '阿尔法', 'alpha');
        $this->createTenant($user, '贝塔', 'beta');

        $created = $this->actingAs($user, 'sanctum')
            ->withHeader('X-Tenant', 'beta')
            ->postJson('/api/v1/iam/departments', ['name' => '贝塔部'])
            ->assertCreated()
            ->json('data');

        $this->actingAs($user, 'sanctum')
            ->withHeader('X-Tenant', 'alpha')
            ->getJson('/api/v1/iam/departments/'.$created['id'])
            ->assertNotFound()
            ->assertJsonPath('code', 40400);
    }

    /**
     * 请求体里的租户主键不能写入或切换租户
     */
    #[Test]
    public function body_tenant_id_cannot_write_or_switch_tenant(): void
    {
        $user = User::factory()->create();
        $alpha = $this->createTenant($user, '阿尔法', 'alpha');
        $beta = $this->createTenant($user, '贝塔', 'beta');

        $created = $this->actingAs($user, 'sanctum')
            ->withHeader('X-Tenant', 'alpha')
            ->postJson('/api/v1/iam/departments', [
                'name' => '伪造部门',
                'tenant_id' => $beta['id'],
            ])
            ->assertCreated()
            ->json('data');

        $this->assertSame($alpha['id'], $created['tenant_id']);

        $betaNames = array_column(
            $this->actingAs($user, 'sanctum')->withHeader('X-Tenant', 'beta')->getJson('/api/v1/iam/departments')->json('data'),
            'name'
        );
        $this->assertNotContains('伪造部门', $betaNames);
    }

    /**
     * 不能把其他租户的部门当作上级
     */
    #[Test]
    public function parent_department_from_another_tenant_is_rejected(): void
    {
        $user = User::factory()->create();
        $this->createTenant($user, '阿尔法', 'alpha');
        $this->createTenant($user, '贝塔', 'beta');

        $parent = $this->actingAs($user, 'sanctum')
            ->withHeader('X-Tenant', 'beta')
            ->postJson('/api/v1/iam/departments', ['name' => '贝塔上级'])
            ->assertCreated()
            ->json('data');

        $this->actingAs($user, 'sanctum')
            ->withHeader('X-Tenant', 'alpha')
            ->postJson('/api/v1/iam/departments', [
                'name' => '跨租户子部门',
                'parent_id' => $parent['id'],
            ])
            ->assertStatus(422)
            ->assertJsonPath('code', 42200);
    }

    /**
     * 非成员和未开通模块都会被拒绝
     */
    #[Test]
    public function non_member_and_disabled_module_are_forbidden(): void
    {
        $owner = User::factory()->create();
        $outsider = User::factory()->create();
        $tenant = $this->createTenant($owner, '阿尔法', 'alpha');

        $this->actingAs($outsider, 'sanctum')
            ->withHeader('X-Tenant', 'alpha')
            ->getJson('/api/v1/iam/menus')
            ->assertForbidden()
            ->assertJsonPath('code', 40301);

        // 临时进入该租户，只为关掉模块，用完立刻清掉上下文。
        app(TenantContext::class)->set($tenant['id'], 'alpha');
        TenantModule::query()->where('module_code', 'iam')->update(['status' => 0]);
        app(TenantContext::class)->clear();

        $this->actingAs($owner, 'sanctum')
            ->withHeader('X-Tenant', 'alpha')
            ->getJson('/api/v1/iam/menus')
            ->assertForbidden()
            ->assertJsonPath('code', 40302);
    }

    /**
     * 缺少权限码或超出部门范围时不可见
     */
    #[Test]
    public function missing_permission_and_department_scope_are_enforced(): void
    {
        $owner = User::factory()->create();
        $staff = User::factory()->create(['email' => 'staff@example.com']);
        $colleague = User::factory()->create(['email' => 'sales@example.com']);
        $finance = User::factory()->create(['email' => 'finance@example.com']);
        $tenant = $this->createTenant($owner, '阿尔法', 'alpha');

        $sales = $this->actingAs($owner, 'sanctum')
            ->withHeader('X-Tenant', 'alpha')
            ->postJson('/api/v1/iam/departments', ['name' => '销售'])
            ->assertCreated()
            ->json('data');
        $financeDept = $this->actingAs($owner, 'sanctum')
            ->withHeader('X-Tenant', 'alpha')
            ->postJson('/api/v1/iam/departments', ['name' => '财务'])
            ->assertCreated()
            ->json('data');

        $context = app(TenantContext::class);
        $context->set($tenant['id'], 'alpha');

        $limited = Role::query()->create([
            'code' => 'staff',
            'name' => '员工',
            'data_scope' => 'self',
        ]);
        $member = TenantUser::query()->create([
            'user_id' => $staff->id,
            'department_id' => $sales['id'],
            'status' => 1,
            'is_owner' => 0,
            'joined_at' => now(),
        ]);
        TenantUserRole::query()->create([
            'tenant_user_id' => $member->id,
            'role_id' => $limited->id,
        ]);

        $context->clear();

        $this->actingAs($staff, 'sanctum')
            ->withHeader('X-Tenant', 'alpha')
            ->getJson('/api/v1/iam/departments')
            ->assertForbidden()
            ->assertJsonPath('code', 40303);

        $context->set($tenant['id'], 'alpha');
        $permissionId = (int) Permission::query()->where('code', 'iam.members.index')->value('id');
        $lead = Role::query()->create([
            'code' => 'lead',
            'name' => '主管',
            'data_scope' => 'department',
        ]);
        RolePermission::query()->create([
            'role_id' => $lead->id,
            'permission_id' => $permissionId,
        ]);
        TenantUserRole::query()->where('tenant_user_id', $member->id)->forceDelete();
        TenantUserRole::query()->create([
            'tenant_user_id' => $member->id,
            'role_id' => $lead->id,
        ]);
        $member->department_id = $sales['id'];
        $member->save();

        TenantUser::query()->create([
            'user_id' => $colleague->id,
            'department_id' => $sales['id'],
            'status' => 1,
            'is_owner' => 0,
            'joined_at' => now(),
        ]);
        TenantUser::query()->create([
            'user_id' => $finance->id,
            'department_id' => $financeDept['id'],
            'status' => 1,
            'is_owner' => 0,
            'joined_at' => now(),
        ]);
        $context->clear();

        $emails = array_column(
            $this->actingAs($staff, 'sanctum')->withHeader('X-Tenant', 'alpha')->getJson('/api/v1/iam/members')->assertOk()->json('data'),
            'email'
        );

        $this->assertContains('staff@example.com', $emails);
        $this->assertContains('sales@example.com', $emails);
        $this->assertNotContains('finance@example.com', $emails);
    }

    /**
     * 没有租户上下文时查询直接失败
     */
    #[Test]
    public function missing_tenant_context_throws_instead_of_returning_rows(): void
    {
        $user = User::factory()->create();
        $tenant = $this->createTenant($user, '阿尔法', 'alpha');
        $context = app(TenantContext::class);
        $context->set($tenant['id'], 'alpha');
        Department::query()->create([
            'name' => '必须存在的部门',
            'path' => '',
            'sort' => 0,
        ]);
        $context->clear();

        try {
            $rows = Department::query()->get();
            $this->fail('缺少租户上下文时不应返回数据，实际返回 '.$rows->count().' 行');
        } catch (MissingTenantContextException) {
            $this->assertTrue(true);
        }
    }

    /**
     * 通过接口创建一个测试租户
     *
     * @return array{id: int, slug: string}
     */
    private function createTenant(User $user, string $name, string $slug): array
    {
        $data = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/tenants', ['name' => $name, 'slug' => $slug])
            ->assertCreated()
            ->json('data');

        return ['id' => (int) $data['id'], 'slug' => (string) $data['slug']];
    }
}

