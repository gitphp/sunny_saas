<!--
 * 离职管理页
 *
 * @package     resources\js\views\hr
 * @license     MIT License (MIT)
 * @author      Sunny Yang <githup@163.com> QQ4873473
 * @copyright   Copyright (c) 2024 YourCompany. All rights reserved.
 * @link        http://www.budff.com
-->
<template>
    <div>
        <div class="toolbar">
            <h2>离职</h2>
            <el-button v-if="access.can('hr.resignations.store')" type="primary" @click="visible = true">发起离职</el-button>
        </div>
        <el-table :data="rows" border>
            <el-table-column prop="employee_name" label="员工" width="120" />
            <el-table-column prop="reason" label="原因" min-width="160" />
            <el-table-column prop="apply_on" label="申请日" width="120" />
            <el-table-column prop="last_on" label="最后工作日" width="130" />
            <el-table-column label="状态" width="100">
                <template #default="{ row }">{{ statusLabel[row.status] }}</template>
            </el-table-column>
            <el-table-column label="操作" width="180">
                <template #default="{ row }">
                    <el-button v-if="row.status === 0 && access.can('hr.resignations.approve')" link type="primary" @click="act(row.id, 'approve')">批准</el-button>
                    <el-button v-if="row.status === 0 && access.can('hr.resignations.reject')" link @click="act(row.id, 'reject')">驳回</el-button>
                    <el-button v-if="row.status === 1 && access.can('hr.resignations.complete')" link type="danger" @click="act(row.id, 'complete')">办结</el-button>
                </template>
            </el-table-column>
        </el-table>
        <el-dialog v-model="visible" title="发起离职" width="460px">
            <el-form label-position="top">
                <el-form-item label="员工">
                    <el-select v-model="form.employee_id" style="width: 100%">
                        <el-option v-for="item in employees" :key="item.id" :label="item.name" :value="item.id" />
                    </el-select>
                </el-form-item>
                <el-form-item label="原因"><el-input v-model="form.reason" /></el-form-item>
                <el-form-item label="申请日"><el-date-picker v-model="form.apply_on" type="date" value-format="YYYY-MM-DD" /></el-form-item>
                <el-form-item label="最后工作日"><el-date-picker v-model="form.last_on" type="date" value-format="YYYY-MM-DD" /></el-form-item>
            </el-form>
            <template #footer>
                <el-button @click="visible = false">取消</el-button>
                <el-button type="primary" @click="create">提交</el-button>
            </template>
        </el-dialog>
    </div>
</template>

<script setup lang="ts">
import { onMounted, reactive, ref } from 'vue';
import { ElMessage } from 'element-plus';
import { apiErrorMessage } from '@/api/error';
import { fetchEmployees, type EmployeeRow } from '@/api/hr';
import { http, type ApiEnvelope } from '@/api/http';
import { useAccessStore } from '@/stores/access';

interface ResignationRow {
    id: number;
    employee_name: string;
    reason: string;
    apply_on: string;
    last_on: string;
    status: number;
}

const statusLabel: Record<number, string> = { 0: '待审批', 1: '已批准', 2: '已驳回', 3: '已办结' };
const access = useAccessStore();
const rows = ref<ResignationRow[]>([]);
const employees = ref<EmployeeRow[]>([]);
const visible = ref(false);
const form = reactive({ employee_id: null as number | null, reason: '', apply_on: '', last_on: '' });

onMounted(load);

/** 读取离职单和在职员工 */
async function load(): Promise<void> {
    const [response, active] = await Promise.all([
        http.get<ApiEnvelope<ResignationRow[]>>('/api/v1/hr/resignations'),
        fetchEmployees(1),
    ]);
    rows.value = response.data.data;
    employees.value = active;
}

/** 提交离职申请 */
async function create(): Promise<void> {
    try {
        await http.post('/api/v1/hr/resignations', form);
        visible.value = false;
        ElMessage.success('离职申请已提交');
        await load();
    } catch (caught) {
        ElMessage.error(apiErrorMessage(caught));
    }
}

/** 批准、驳回或办结 */
async function act(id: number, action: 'approve' | 'reject' | 'complete'): Promise<void> {
    try {
        await http.post(`/api/v1/hr/resignations/${id}/${action}`);
        ElMessage.success('已更新');
        await load();
    } catch (caught) {
        ElMessage.error(apiErrorMessage(caught));
    }
}
</script>

<style scoped>
.toolbar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; }
h2 { margin: 0; font-size: 18px; }
</style>
