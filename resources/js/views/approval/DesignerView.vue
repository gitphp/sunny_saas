<!--
 * 流程设计：基础信息、表单、流程和发布
 *
 * @package     resources\js\views\approval
 * @license     MIT License (MIT)
 * @author      Sunny Yang <githup@163.com> QQ4873473
 * @copyright   Copyright (c) 2024 YourCompany. All rights reserved.
 * @link        http://www.budff.com
-->
<template>
    <div v-if="loaded">
        <div class="toolbar">
            <h2>{{ name || '流程设计' }}</h2>
            <div>
                <el-button @click="save">保存草稿</el-button>
                <el-button v-if="access.can('approval.definitions.publish')" type="primary" @click="publish">发布</el-button>
            </div>
        </div>
        <el-steps :active="step" finish-status="success" align-center class="steps">
            <el-step title="基础信息" />
            <el-step title="表单设计" />
            <el-step title="流程设计" />
            <el-step title="更多设置" />
        </el-steps>
        <el-card v-show="step === 0">
            <el-form label-position="top">
                <el-form-item label="名称"><el-input v-model="name" /></el-form-item>
                <el-form-item label="说明"><el-input v-model="description" type="textarea" /></el-form-item>
                <el-form-item label="图标"><el-input v-model="icon" /></el-form-item>
                <el-form-item label="分组">
                    <el-select v-model="groupId" clearable style="width: 100%">
                        <el-option v-for="group in groups" :key="group.id" :label="group.name" :value="group.id" />
                    </el-select>
                </el-form-item>
            </el-form>
        </el-card>
        <el-card v-show="step === 1">
            <el-button @click="addField">添加字段</el-button>
            <div v-for="(field, index) in fields" :key="index" class="field">
                <el-input v-model="field.label" placeholder="名称" />
                <el-input v-model="field.key" placeholder="编码" />
                <el-select v-model="field.type">
                    <el-option label="单行文本" value="text" />
                    <el-option label="多行文本" value="textarea" />
                    <el-option label="数字" value="number" />
                    <el-option label="金额" value="money" />
                    <el-option label="日期" value="date" />
                    <el-option label="下拉" value="select" />
                </el-select>
                <el-checkbox v-model="field.required">必填</el-checkbox>
                <el-button link type="danger" @click="fields.splice(index, 1)">删除</el-button>
            </div>
        </el-card>
        <el-card v-show="step === 2">
            <p class="hint">开始之后按顺序经过这些节点，最后结束。带条件的连线优先于这条默认顺序。</p>
            <div class="node">开始</div>
            <div v-for="(node, index) in nodes" :key="node.key" class="node">
                <el-input v-model="node.name" />
                <el-select v-model="node.type">
                    <el-option label="审批" value="approve" />
                    <el-option label="抄送" value="cc" />
                </el-select>
                <el-select v-if="node.type === 'approve'" v-model="node.assignee_type">
                    <el-option label="指定成员" value="user" />
                    <el-option label="发起人本人" value="initiator" />
                </el-select>
                <el-select v-if="node.type === 'approve'" v-model="node.sign">
                    <el-option label="或签" value="any" />
                    <el-option label="会签" value="all" />
                </el-select>
                <el-select v-if="node.assignee_type !== 'initiator' || node.type === 'cc'" v-model="node.user_ids" multiple placeholder="选择人员" style="min-width: 220px">
                    <el-option v-for="member in members" :key="member.user_id" :label="member.name" :value="member.user_id" />
                </el-select>
                <el-button link type="danger" @click="nodes.splice(index, 1)">删除</el-button>
            </div>
            <div class="node">结束</div>
            <div class="adds">
                <el-button @click="addNode('approve')">添加审批人</el-button>
                <el-button @click="addNode('cc')">添加抄送人</el-button>
            </div>
            <h3>条件分支</h3>
            <div v-for="(edge, index) in conditions" :key="index" class="field">
                <el-input v-model="edge.field" placeholder="字段编码" />
                <el-select v-model="edge.op">
                    <el-option label="大于等于" value="gte" />
                    <el-option label="大于" value="gt" />
                    <el-option label="等于" value="eq" />
                    <el-option label="小于等于" value="lte" />
                    <el-option label="小于" value="lt" />
                </el-select>
                <el-input v-model="edge.value" placeholder="比较值" />
                <el-select v-model="edge.to" placeholder="进入节点">
                    <el-option v-for="node in nodes" :key="node.key" :label="node.name" :value="node.key" />
                </el-select>
                <el-button link type="danger" @click="conditions.splice(index, 1)">删除</el-button>
            </div>
            <el-button @click="addCondition">添加条件</el-button>
        </el-card>
        <el-card v-show="step === 3">
            <el-form label-position="top">
                <el-form-item label="允许撤销审批中的申请">
                    <el-switch v-model="allowRevoke" />
                </el-form-item>
            </el-form>
            <h3>版本</h3>
            <el-table :data="versions" border>
                <el-table-column prop="version_no" label="版本" width="80" />
                <el-table-column label="状态" width="100">
                    <template #default="{ row }">{{ versionStatusLabel[row.status] }}</template>
                </el-table-column>
                <el-table-column prop="published_at" label="发布时间" min-width="180" />
            </el-table>
        </el-card>
        <div class="footer">
            <el-button :disabled="step === 0" @click="step -= 1">上一步</el-button>
            <el-button v-if="step < 3" type="primary" @click="step += 1">下一步</el-button>
        </div>
    </div>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { useRoute } from 'vue-router';
import { ElMessage } from 'element-plus';
import { fetchDefinition, versionStatusLabel, type ApprovalMember, type FormField, type FlowNode } from '@/api/approval';
import { apiErrorMessage } from '@/api/error';
import { http, type ApiEnvelope } from '@/api/http';
import { useAccessStore } from '@/stores/access';

interface ConditionDraft {
    field: string;
    op: string;
    value: string;
    to: string;
    priority: number;
}

const access = useAccessStore();
const route = useRoute();
const loaded = ref(false);
const step = ref(0);
const name = ref('');
const description = ref('');
const icon = ref('user');
const groupId = ref<number | null>(null);
const allowRevoke = ref(true);
const groups = ref<{ id: number; name: string }[]>([]);
const members = ref<ApprovalMember[]>([]);
const fields = ref<FormField[]>([]);
const nodes = ref<FlowNode[]>([]);
const conditions = ref<ConditionDraft[]>([]);
const versions = ref<{ version_no: number; status: number; published_at: string | null }[]>([]);

onMounted(async () => {
    const id = Number(route.params.id);
    const [detail, groupRows, memberRows] = await Promise.all([
        fetchDefinition(id),
        http.get<ApiEnvelope<{ id: number; name: string }[]>>('/api/v1/approval/groups'),
        http.get<ApiEnvelope<ApprovalMember[]>>('/api/v1/approval/members'),
    ]);
    groups.value = groupRows.data.data;
    members.value = memberRows.data.data;
    name.value = detail.name;
    description.value = detail.description;
    icon.value = detail.icon;
    groupId.value = detail.group_id;
    allowRevoke.value = detail.allow_revoke === 1;
    versions.value = detail.versions;
    const editing = detail.editing;
    fields.value = editing?.form_schema.fields ? editing.form_schema.fields.map((field) => ({ ...field, options: field.options || [] })) : [];
    nodes.value = (editing?.flow_schema.nodes || []).filter((node) => node.type === 'approve' || node.type === 'cc');
    conditions.value = (editing?.flow_schema.edges || [])
        .filter((edge) => edge.when.length > 0)
        .map((edge) => ({
            field: edge.when[0].field,
            op: edge.when[0].op,
            value: String(edge.when[0].value),
            to: edge.to,
            priority: edge.priority,
        }));
    loaded.value = true;
});

/** 增加一个表单字段 */
function addField(): void {
    const index = fields.value.length + 1;
    fields.value.push({ key: `field_${index}`, type: 'text', label: `字段${index}`, required: false, options: [] });
}

/** 在结束前插入审批或抄送 */
function addNode(type: 'approve' | 'cc'): void {
    nodes.value.push({
        key: `${type}_${Date.now()}`,
        type,
        name: type === 'approve' ? '审批' : '抄送',
        assignee_type: type === 'approve' ? 'user' : '',
        user_ids: [],
        sign: 'any',
    });
}

/** 从开始节点分出一条条件 */
function addCondition(): void {
    conditions.value.push({ field: '', op: 'gte', value: '', to: nodes.value[0]?.key || '', priority: conditions.value.length + 1 });
}

/** 把设计器里的顺序节点还原成流程图 */
function flowSchema(): { nodes: FlowNode[]; edges: { from: string; to: string; priority: number; when: { field: string; op: string; value: string | number }[] }[] } {
    const graphNodes: FlowNode[] = [
        { key: 'start', type: 'start', name: '开始', assignee_type: '', user_ids: [], sign: 'any' },
        ...nodes.value,
        { key: 'end', type: 'end', name: '结束', assignee_type: '', user_ids: [], sign: 'any' },
    ];
    const edges = [];
    let previous = 'start';

    for (const node of nodes.value) {
        edges.push({ from: previous, to: node.key, priority: 100, when: [] });
        previous = node.key;
    }

    edges.push({ from: previous, to: 'end', priority: 100, when: [] });

    for (const condition of conditions.value) {
        if (!condition.field || !condition.to) {
            continue;
        }

        const numeric = Number(condition.value);
        edges.push({
            from: 'start',
            to: condition.to,
            priority: condition.priority,
            when: [{ field: condition.field, op: condition.op, value: Number.isNaN(numeric) ? condition.value : numeric }],
        });
    }

    return { nodes: graphNodes, edges };
}

/** 保存草稿。已发布版本不会被改写。 */
async function save(): Promise<void> {
    try {
        const response = await http.put<ApiEnvelope<{ versions: { version_no: number; status: number; published_at: string | null }[] }>>(`/api/v1/approval/definitions/${route.params.id}`, {
            name: name.value,
            description: description.value,
            icon: icon.value,
            group_id: groupId.value,
            allow_revoke: allowRevoke.value ? 1 : 0,
            form_schema: { fields: fields.value },
            flow_schema: flowSchema(),
        });
        versions.value = response.data.data.versions;
        ElMessage.success('草稿已保存');
    } catch (caught) {
        ElMessage.error(apiErrorMessage(caught));
    }
}

/** 发布当前草稿 */
async function publish(): Promise<void> {
    try {
        await save();
        const response = await http.post<ApiEnvelope<{ versions: { version_no: number; status: number; published_at: string | null }[] }>>(`/api/v1/approval/definitions/${route.params.id}/publish`);
        versions.value = response.data.data.versions;
        ElMessage.success('已发布');
    } catch (caught) {
        ElMessage.error(apiErrorMessage(caught));
    }
}
</script>

<style scoped>
.toolbar, .footer, .adds { display: flex; justify-content: space-between; gap: 8px; align-items: center; }
.toolbar { margin-bottom: 16px; }
.footer { margin-top: 16px; justify-content: flex-end; }
h2 { margin: 0; font-size: 18px; }
h3 { margin: 18px 0 8px; font-size: 15px; }
.steps { margin-bottom: 16px; }
.field, .node { display: flex; gap: 8px; align-items: center; margin-top: 10px; flex-wrap: wrap; }
.node { border: 1px solid #e5e7eb; border-radius: 8px; padding: 10px; background: #fff; }
.hint { color: #6b7280; font-size: 13px; }
</style>
