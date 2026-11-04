<?php

/**
 * 员工入职到离职的生命周期，以及企业之间的隔离
 *
 * @package     Tests\Feature
 * @license     MIT License (MIT)
 * @author      Sunny Yang <githup@163.com> QQ4873473
 * @copyright   Copyright (c) 2024 YourCompany. All rights reserved.
 * @link        http://www.budff.com
 */

namespace Tests\Feature;

use App\Modules\Iam\Infrastructure\Persistence\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * 员工入职到离职的生命周期，以及企业之间的隔离
 */
class HrLifecycleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 标准版可以走完入职、合同、考勤、请假和离职，另一家企业读不到这些档案
     */
    #[Test]
    public function an_employee_can_move_through_hire_and_exit_inside_one_tenant(): void
    {
        $ownerA = User::factory()->create();
        $ownerB = User::factory()->create();

        $this->actingAs($ownerA, 'sanctum')
            ->postJson('/api/v1/tenants', ['name' => '甲企业', 'slug' => 'alpha', 'plan_code' => 'standard'])
            ->assertCreated();
        $this->actingAs($ownerB, 'sanctum')
            ->postJson('/api/v1/tenants', ['name' => '乙企业', 'slug' => 'beta', 'plan_code' => 'standard'])
            ->assertCreated();
        $this->actingAs($ownerA, 'sanctum')
            ->postJson('/api/v1/tenants', ['name' => '丙企业', 'slug' => 'gamma', 'plan_code' => 'basic'])
            ->assertCreated();

        $this->actingAs($ownerA, 'sanctum')
            ->withHeader('X-Tenant', 'alpha')
            ->getJson('/api/v1/iam/menus')
            ->assertOk()
            ->assertJsonMissing(['code' => 'hr']);

        $this->actingAs($ownerA, 'sanctum')
            ->withHeader('X-Tenant', 'gamma')
            ->putJson('/api/v1/iam/modules/hr', ['status' => 1])
            ->assertStatus(422)
            ->assertJsonPath('message', '当前套餐不包含该模块');

        $this->actingAs($ownerA, 'sanctum')
            ->withHeader('X-Tenant', 'alpha')
            ->putJson('/api/v1/iam/modules/hr', ['status' => 1])
            ->assertOk()
            ->assertJsonPath('data.included', true);
        $this->actingAs($ownerB, 'sanctum')
            ->withHeader('X-Tenant', 'beta')
            ->putJson('/api/v1/iam/modules/hr', ['status' => 1])
            ->assertOk();

        $department = $this->actingAs($ownerA, 'sanctum')
            ->withHeader('X-Tenant', 'alpha')
            ->postJson('/api/v1/iam/departments', ['name' => '研发'])
            ->assertCreated()
            ->json('data');

        $position = $this->actingAs($ownerA, 'sanctum')
            ->withHeader('X-Tenant', 'alpha')
            ->postJson('/api/v1/hr/positions', [
                'name' => '工程师',
                'code' => 'eng',
                'department_id' => $department['id'],
            ])
            ->assertCreated()
            ->json('data');

        $employee = $this->actingAs($ownerA, 'sanctum')
            ->withHeader('X-Tenant', 'alpha')
            ->postJson('/api/v1/hr/employees', [
                'employee_no' => 'E001',
                'name' => '张三',
                'department_id' => $department['id'],
                'position_id' => $position['id'],
            ])
            ->assertCreated()
            ->assertJsonPath('data.status', 0)
            ->json('data');

        $this->actingAs($ownerB, 'sanctum')
            ->withHeader('X-Tenant', 'beta')
            ->getJson('/api/v1/hr/employees/'.$employee['id'])
            ->assertNotFound();

        $this->actingAs($ownerA, 'sanctum')
            ->withHeader('X-Tenant', 'alpha')
            ->postJson('/api/v1/hr/employees/'.$employee['id'].'/onboard', ['hired_on' => '2026-03-01'])
            ->assertOk()
            ->assertJsonPath('data.status', 1)
            ->assertJsonPath('data.hired_on', '2026-03-01');

        $this->actingAs($ownerA, 'sanctum')
            ->withHeader('X-Tenant', 'alpha')
            ->postJson('/api/v1/hr/contracts', [
                'employee_id' => $employee['id'],
                'contract_no' => 'HT-001',
                'type' => 'fixed',
                'start_on' => '2026-03-01',
                'end_on' => '2027-02-28',
                'signed_on' => '2026-03-01',
            ])
            ->assertCreated()
            ->assertJsonPath('data.status', 1);

        $this->actingAs($ownerA, 'sanctum')
            ->withHeader('X-Tenant', 'alpha')
            ->postJson('/api/v1/hr/attendance', [
                'employee_id' => $employee['id'],
                'work_on' => '2026-03-02',
                'check_in' => '09:05',
                'check_out' => '18:00',
                'status' => 'late',
            ])
            ->assertCreated()
            ->assertJsonPath('data.status', 'late');

        $leave = $this->actingAs($ownerA, 'sanctum')
            ->withHeader('X-Tenant', 'alpha')
            ->postJson('/api/v1/hr/leaves', [
                'employee_id' => $employee['id'],
                'leave_type' => 'annual',
                'start_on' => '2026-03-10',
                'end_on' => '2026-03-11',
                'reason' => '年假',
            ])
            ->assertCreated()
            ->json('data');

        $this->actingAs($ownerA, 'sanctum')
            ->withHeader('X-Tenant', 'alpha')
            ->postJson('/api/v1/hr/leaves/'.$leave['id'].'/approve')
            ->assertOk()
            ->assertJsonPath('data.status', 1);

        $resignation = $this->actingAs($ownerA, 'sanctum')
            ->withHeader('X-Tenant', 'alpha')
            ->postJson('/api/v1/hr/resignations', [
                'employee_id' => $employee['id'],
                'reason' => '个人发展',
                'apply_on' => '2026-06-01',
                'last_on' => '2026-06-30',
            ])
            ->assertCreated()
            ->json('data');

        $this->actingAs($ownerA, 'sanctum')
            ->withHeader('X-Tenant', 'alpha')
            ->postJson('/api/v1/hr/resignations/'.$resignation['id'].'/approve')
            ->assertOk();
        $this->actingAs($ownerA, 'sanctum')
            ->withHeader('X-Tenant', 'alpha')
            ->postJson('/api/v1/hr/resignations/'.$resignation['id'].'/complete')
            ->assertOk()
            ->assertJsonPath('data.status', 3);

        $this->actingAs($ownerA, 'sanctum')
            ->withHeader('X-Tenant', 'alpha')
            ->getJson('/api/v1/hr/employees/'.$employee['id'])
            ->assertOk()
            ->assertJsonPath('data.status', 2)
            ->assertJsonPath('data.left_on', '2026-06-30');

        $this->actingAs($ownerA, 'sanctum')
            ->withHeader('X-Tenant', 'alpha')
            ->getJson('/api/v1/hr/organization')
            ->assertOk()
            ->assertJsonPath('data.departments.0.name', '研发');

        $this->actingAs($ownerA, 'sanctum')
            ->withHeader('X-Tenant', 'alpha')
            ->getJson('/api/v1/iam/menus')
            ->assertOk()
            ->assertJsonFragment(['name' => '组织架构']);

        $this->actingAs($ownerB, 'sanctum')
            ->withHeader('X-Tenant', 'beta')
            ->getJson('/api/v1/hr/employees')
            ->assertOk()
            ->assertJsonMissing(['name' => '张三']);
    }
}
