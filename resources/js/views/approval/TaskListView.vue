<!--
 * 待办、已办、抄送和我的申请
 *
 * @package     resources\js\views\approval
 * @license     MIT License (MIT)
 * @author      Sunny Yang <githup@163.com> QQ4873473
 * @copyright   Copyright (c) 2024 YourCompany. All rights reserved.
 * @link        http://www.budff.com
-->
<template>
    <div>
        <div class="toolbar"><h2>{{ title }}</h2></div>
        <el-table :data="rows" border @row-click="open">
            <el-table-column prop="serial_no" label="单号" min-width="160" />
            <el-table-column prop="title" label="标题" min-width="160" />
            <el-table-column prop="definition_name" label="流程" min-width="120" />
            <el-table-column prop="applicant_name" label="申请人" width="120" />
            <el-table-column label="状态" width="100">
                <template #default="{ row }">{{ instanceStatusLabel[row.instance_status ?? row.status] }}</template>
            </el-table-column>
            <el-table-column prop="node_name" label="节点" width="120" />
        </el-table>
        <InstanceDrawer v-model="visible" :instance-id="currentId" @changed="load" />
    </div>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { instanceStatusLabel } from '@/api/approval';
import { http, type ApiEnvelope } from '@/api/http';
import InstanceDrawer from '@/components/approval/InstanceDrawer.vue';

const props = defineProps<{ title: string; source: 'tasks' | 'instances' | 'data'; box?: string }>();
const rows = ref<Record<string, string | number>[]>([]);
const visible = ref(false);
const currentId = ref<number | null>(null);

onMounted(load);

/** 按页面来源读取列表 */
async function load(): Promise<void> {
    const url = props.source === 'tasks'
        ? `/api/v1/approval/tasks?box=${props.box || 'todo'}`
        : props.source === 'data'
            ? '/api/v1/approval/data'
            : '/api/v1/approval/instances';
    const response = await http.get<ApiEnvelope<Record<string, string | number>[]>>(url);
    rows.value = response.data.data;
}

/** 打开详情 */
function open(row: { instance_id?: number; id: number }): void {
    currentId.value = row.instance_id || row.id;
    visible.value = true;
}
</script>

<style scoped>
.toolbar { margin-bottom: 16px; }
h2 { margin: 0; font-size: 18px; }
</style>
