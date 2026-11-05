<!--
 * 审批单详情
 *
 * @package     resources\js\components\approval
 * @license     MIT License (MIT)
 * @author      Sunny Yang <githup@163.com> QQ4873473
 * @copyright   Copyright (c) 2024 YourCompany. All rights reserved.
 * @link        http://www.budff.com
-->
<template>
    <el-drawer v-model="opened" :title="detail?.title || '审批单'" size="480px" @open="load">
        <template v-if="detail">
            <p class="meta">单号 {{ detail.serial_no }} · {{ instanceStatusLabel[detail.status] }} · V{{ detail.version_no }}</p>
            <p class="meta">申请人 {{ detail.applicant_name }}</p>
            <el-descriptions :column="1" border>
                <el-descriptions-item v-for="field in detail.form_schema.fields" :key="field.key" :label="field.label">
                    {{ detail.form_data[field.key] ?? '—' }}
                </el-descriptions-item>
            </el-descriptions>
            <h3>记录</h3>
            <el-timeline>
                <el-timeline-item v-for="record in detail.records" :key="record.id" :timestamp="record.created_at || ''">
                    {{ record.actor_name }} {{ actionLabel[record.action] || record.action }}
                    <span v-if="record.comment">：{{ record.comment }}</span>
                </el-timeline-item>
            </el-timeline>
            <el-input v-model="comment" type="textarea" placeholder="意见" />
            <div class="actions">
                <el-button v-if="detail.can_approve && access.can('approval.instances.approve')" type="primary" @click="act('approve')">同意</el-button>
                <el-button v-if="detail.can_approve && access.can('approval.instances.reject')" @click="act('reject')">驳回</el-button>
                <el-button v-if="detail.can_revoke && access.can('approval.instances.revoke')" type="danger" @click="act('revoke')">撤销</el-button>
                <el-button v-if="access.can('approval.instances.comment')" @click="act('comment')">评论</el-button>
            </div>
        </template>
    </el-drawer>
</template>

<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { ElMessage } from 'element-plus';
import { actionLabel, fetchInstance, instanceStatusLabel, type InstanceDetail } from '@/api/approval';
import { apiErrorMessage } from '@/api/error';
import { http } from '@/api/http';
import { useAccessStore } from '@/stores/access';

const props = defineProps<{ modelValue: boolean; instanceId: number | null }>();
const emit = defineEmits<{ 'update:modelValue': [boolean]; changed: [] }>();
const access = useAccessStore();
const detail = ref<InstanceDetail | null>(null);
const comment = ref('');
const opened = computed({
    get: () => props.modelValue,
    set: (value: boolean) => emit('update:modelValue', value),
});

watch(() => props.instanceId, () => {
    if (props.modelValue) {
        load();
    }
});

/** 读取审批单。打开抄送时后端会把它标成已读。 */
async function load(): Promise<void> {
    if (!props.instanceId) {
        return;
    }

    detail.value = await fetchInstance(props.instanceId);
}

/** 同意、驳回、撤销或评论 */
async function act(action: 'approve' | 'reject' | 'revoke' | 'comment'): Promise<void> {
    if (!props.instanceId) {
        return;
    }

    try {
        await http.post(`/api/v1/approval/instances/${props.instanceId}/${action}`, { comment: comment.value });
        const done: Record<typeof action, string> = { approve: '已同意', reject: '已驳回', revoke: '已撤销', comment: '已评论' };
        ElMessage.success(done[action]);
        comment.value = '';
        await load();
        emit('changed');
    } catch (caught) {
        ElMessage.error(apiErrorMessage(caught));
    }
}
</script>

<style scoped>
.meta { color: #6b7280; font-size: 13px; }
h3 { margin: 18px 0 8px; font-size: 15px; }
.actions { display: flex; gap: 8px; margin-top: 12px; }
</style>
