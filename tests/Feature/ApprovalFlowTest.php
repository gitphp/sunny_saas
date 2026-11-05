<?php

/**
 * 流程发布、条件分支、待办、抄送和租户隔离
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
 * 流程发布、条件分支、待办、抄送和租户隔离
 */
class ApprovalFlowTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 已发布的流程可以按条件分给不同审批人，旧版本上的单据不受新版本影响
     */
    #[Test]
    public function a_published_flow_routes_tasks_and_keeps_old_versions(): void
    {
        $owner = User::factory()->create();
        $approver = User::factory()->create();
        $outsider = User::factory()->create();

        $this->actingAs($owner, 'sanctum')
            ->postJson('/api/v1/tenants', ['name' => '甲企业', 'slug' => 'alpha', 'plan_code' => 'standard'])
            ->assertCreated();
        $this->actingAs($outsider, 'sanctum')
            ->postJson('/api/v1/tenants', ['name' => '乙企业', 'slug' => 'beta', 'plan_code' => 'standard'])
            ->assertCreated();
        $this->actingAs($owner, 'sanctum')
            ->postJson('/api/v1/tenants', ['name' => '丙企业', 'slug' => 'gamma', 'plan_code' => 'basic'])
            ->assertCreated();

        $alpha = fn () => $this->actingAs($owner, 'sanctum')->withHeader('X-Tenant', 'alpha');

        $alpha()->getJson('/api/v1/iam/menus')->assertOk()->assertJsonMissing(['code' => 'approval']);
        $alpha()->putJson('/api/v1/iam/modules/approval', ['status' => 1])->assertOk()->assertJsonPath('data.included', true);
        $this->actingAs($owner, 'sanctum')
            ->withHeader('X-Tenant', 'gamma')
            ->putJson('/api/v1/iam/modules/approval', ['status' => 1])
            ->assertStatus(422)
            ->assertJsonPath('message', '当前套餐不包含该模块');
        $this->actingAs($outsider, 'sanctum')
            ->withHeader('X-Tenant', 'beta')
            ->putJson('/api/v1/iam/modules/approval', ['status' => 1])
            ->assertOk();

        $adminRoleId = collect($alpha()->getJson('/api/v1/iam/roles')->assertOk()->json('data'))
            ->firstWhere('code', 'admin')['id'];
        $alpha()->postJson('/api/v1/iam/members', [
            'name' => '审批人',
            'email' => $approver->email,
            'role_ids' => [$adminRoleId],
        ])->assertCreated();

        $group = $alpha()->postJson('/api/v1/approval/groups', ['name' => '假勤'])->assertCreated()->json('data');
        $definition = $alpha()->postJson('/api/v1/approval/definitions', [
            'code' => 'leave',
            'name' => '请假',
            'group_id' => $group['id'],
            'description' => '请假申请',
        ])->assertCreated()->json('data');

        $alpha()->putJson('/api/v1/approval/definitions/'.$definition['id'], [
            'form_schema' => [
                'fields' => [
                    ['key' => 'days', 'type' => 'number', 'label' => '天数', 'required' => true],
                    ['key' => 'reason', 'type' => 'textarea', 'label' => '事由', 'required' => true],
                ],
            ],
            'flow_schema' => [
                'nodes' => [
                    ['key' => 'start', 'type' => 'start', 'name' => '开始'],
                    ['key' => 'approve_owner', 'type' => 'approve', 'name' => '负责人审批', 'assignee_type' => 'user', 'user_ids' => [$approver->id], 'sign' => 'any'],
                    ['key' => 'end', 'type' => 'end', 'name' => '结束'],
                ],
                'edges' => [
                    ['from' => 'start', 'to' => 'approve_owner', 'priority' => 1, 'when' => []],
                    ['from' => 'approve_owner', 'to' => 'end', 'priority' => 1, 'when' => []],
                ],
            ],
        ])->assertOk();
        $alpha()->postJson('/api/v1/approval/definitions/'.$definition['id'].'/publish')->assertOk()
            ->assertJsonPath('data.versions.0.version_no', 1)
            ->assertJsonPath('data.versions.0.status', 1);

        $first = $alpha()->postJson('/api/v1/approval/instances', [
            'definition_code' => 'leave',
            'title' => '年假一天',
            'form_data' => ['days' => 1, 'reason' => '年假'],
            'source_module' => 'hr',
            'source_id' => 9,
        ])->assertCreated()
            ->assertJsonPath('data.status', 1)
            ->assertJsonPath('data.version_no', 1)
            ->assertJsonPath('data.source_module', 'hr')
            ->json('data');

        $this->actingAs($outsider, 'sanctum')
            ->withHeader('X-Tenant', 'beta')
            ->getJson('/api/v1/approval/instances/'.$first['id'])
            ->assertNotFound();

        $this->actingAs($approver, 'sanctum')
            ->withHeader('X-Tenant', 'alpha')
            ->getJson('/api/v1/approval/tasks?box=todo')
            ->assertOk()
            ->assertJsonPath('data.0.instance_id', $first['id']);
        $this->actingAs($approver, 'sanctum')
            ->withHeader('X-Tenant', 'alpha')
            ->postJson('/api/v1/approval/instances/'.$first['id'].'/approve', ['comment' => '同意'])
            ->assertOk()
            ->assertJsonPath('data.status', 2);

        $alpha()->putJson('/api/v1/approval/definitions/'.$definition['id'], [
            'flow_schema' => [
                'nodes' => [
                    ['key' => 'start', 'type' => 'start', 'name' => '开始'],
                    ['key' => 'approve_long', 'type' => 'approve', 'name' => '负责人审批', 'assignee_type' => 'user', 'user_ids' => [$approver->id], 'sign' => 'any'],
                    ['key' => 'approve_short', 'type' => 'approve', 'name' => '发起人确认', 'assignee_type' => 'initiator', 'user_ids' => [], 'sign' => 'any'],
                    ['key' => 'cc_approver', 'type' => 'cc', 'name' => '抄送审批人', 'user_ids' => [$approver->id]],
                    ['key' => 'end', 'type' => 'end', 'name' => '结束'],
                ],
                'edges' => [
                    ['from' => 'start', 'to' => 'approve_long', 'priority' => 1, 'when' => [['field' => 'days', 'op' => 'gte', 'value' => 3]]],
                    ['from' => 'start', 'to' => 'approve_short', 'priority' => 2, 'when' => []],
                    ['from' => 'approve_long', 'to' => 'cc_approver', 'priority' => 1, 'when' => []],
                    ['from' => 'approve_short', 'to' => 'end', 'priority' => 1, 'when' => []],
                    ['from' => 'cc_approver', 'to' => 'end', 'priority' => 1, 'when' => []],
                ],
            ],
        ])->assertOk()->assertJsonPath('data.editing.version_no', 2);
        $alpha()->postJson('/api/v1/approval/definitions/'.$definition['id'].'/publish')->assertOk();

        $alpha()->getJson('/api/v1/approval/instances/'.$first['id'])
            ->assertOk()
            ->assertJsonPath('data.version_no', 1)
            ->assertJsonPath('data.status', 2);

        $short = $alpha()->postJson('/api/v1/approval/instances', [
            'definition_code' => 'leave',
            'title' => '事假一天',
            'form_data' => ['days' => 1, 'reason' => '事假'],
        ])->assertCreated()->assertJsonPath('data.version_no', 2)->json('data');
        $this->assertSame($owner->id, $short['tasks'][0]['assignee_user_id']);
        $alpha()->postJson('/api/v1/approval/instances/'.$short['id'].'/approve', ['comment' => '确认'])
            ->assertOk()
            ->assertJsonPath('data.status', 2);

        $long = $alpha()->postJson('/api/v1/approval/instances', [
            'definition_code' => 'leave',
            'title' => '年假四天',
            'form_data' => ['days' => 4, 'reason' => '年假'],
        ])->assertCreated()->json('data');
        $this->assertSame($approver->id, $long['tasks'][0]['assignee_user_id']);
        $approved = $this->actingAs($approver, 'sanctum')
            ->withHeader('X-Tenant', 'alpha')
            ->postJson('/api/v1/approval/instances/'.$long['id'].'/approve', ['comment' => '同意长假'])
            ->assertOk()
            ->assertJsonPath('data.status', 2)
            ->json('data');
        $this->assertTrue(collect($approved['tasks'])->contains(fn (array $task): bool => $task['task_type'] === 'cc' && $task['assignee_user_id'] === $approver->id));
        $this->assertTrue(collect($approved['records'])->contains(fn (array $record): bool => $record['action'] === 'cc'));

        $pending = $alpha()->postJson('/api/v1/approval/instances', [
            'definition_code' => 'leave',
            'title' => '想撤销',
            'form_data' => ['days' => 4, 'reason' => '临时有事'],
        ])->assertCreated()->json('data');
        $alpha()->postJson('/api/v1/approval/instances/'.$pending['id'].'/revoke', ['comment' => '不请了'])
            ->assertOk()
            ->assertJsonPath('data.status', 4);
        $this->actingAs($approver, 'sanctum')
            ->withHeader('X-Tenant', 'alpha')
            ->getJson('/api/v1/approval/tasks?box=todo')
            ->assertOk()
            ->assertJsonMissing(['instance_id' => $pending['id']]);
        $this->actingAs($approver, 'sanctum')
            ->withHeader('X-Tenant', 'alpha')
            ->getJson('/api/v1/approval/tasks?box=cc')
            ->assertOk()
            ->assertJsonFragment(['instance_id' => $long['id']]);

        $alpha()->getJson('/api/v1/iam/menus')->assertOk()->assertJsonFragment(['name' => '发起申请']);
        $alpha()->getJson('/api/v1/approval/data')->assertOk()->assertJsonFragment(['title' => '年假四天']);
    }
}
