<!--
 * 请假管理页
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
            <h2>请假</h2>
            <el-button v-if="access.can('hr.leaves.store')" type="primary" @click="visible = true">提交请假</el-button>
        </div>
        <el-table :data="rows" border>
            <el-table-column prop="employee_name" label="员工" width="120" />
            <el-table-column label="类型" width="100">
                <template #default="{ row }">{{ typeLabel[row.leave_type] }}</template>
            </el-table-column>
            <el-table-column prop="start_on" label="开始" width="120" />
            <el-table-column prop="end_on" label="结束" width="120" />
            <el-table-column prop="reason" label="事由" min-width="160" />
            <el-table-column label="状态" width="100">
                <template #default="{ row }">{{ statusLabel[row.status] }}</template>
            </el-table-column>
            <el-table-column label="操作" width="140">
                <template #default="{ row }">
                    <el-button v-if="row.status === 0 && access.can('hr.leaves.approve')" link type="primary" @click="review(row.id, 'approve')">批准</el-button>
                    <el-button v-if="row.status === 0 && access.can('hr.leaves.reject')" link @click="review(row.id, 'reject')">驳回</el-button>
                </template>
            </el-table-column>
        </el-table>
        <el-dialog v-model="visible" title="提交请假" width="460px">
            <el-form label-position="top">
                <el-form-item label="员工">
                    <el-select v-model="form.employee_id" style="width: 100%">
                        <el-option v-for="item in employees" :key="item.id" :label="item.name" :value="item.id" />
                    </el-select>
                </el-form-item>
                <el-form-item label="类型">
                    <el-select v-model="form.leave_type" style="width: 100%">
                        <el-option v-for="(label, value) in typeLabel" :key="value" :label="label" :value="value" />
                    </el-select>
                </el-form-item>
                <el-form-item label="开始"><el-date-picker v-model="form.start_on" type="date" value-format="YYYY-MM-DD" /></el-form-item>
                <el-form-item label="结束"><el-date-picker v-model="form.end_on" type="date" value-format="YYYY-MM-DD" /></el-form-item>
                <el-form-item label="事由"><el-input v-model="form.reason" /></el-form-item>
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

interface LeaveRow {
    id: number;
    employee_name: string;
    leave_type: string;
    start_on: string;
    end_on: string;
    reason: string;
    status: number;
}

const typeLabel: Record<string, string> = { annual: '年假', sick: '病假', personal: '事假', other: '其他' };
const statusLabel: Record<number, string> = { 0: '待审批', 1: '已批准', 2: '已驳回' };
const access = useAccessStore();
const rows = ref<LeaveRow[]>([]);
const employees = ref<EmployeeRow[]>([]);
const visible = ref(false);
const form = reactive({ employee_id: null as number | null, leave_type: 'annual', start_on: '', end_on: '', reason: '' });

onMounted(load);

/** 读取请假和在职员工 */
async function load(): Promise<void> {
    const [response, active] = await Promise.all([
        http.get<ApiEnvelope<LeaveRow[]>>('/api/v1/hr/leaves'),
        fetchEmployees(1),
    ]);
    rows.value = response.data.data;
    employees.value = active;
}

/** 提交请假 */
async function create(): Promise<void> {
    try {
        await http.post('/api/v1/hr/leaves', form);
        visible.value = false;
        ElMessage.success('请假已提交');
        await load();
    } catch (caught) {
        ElMessage.error(apiErrorMessage(caught));
    }
}

/** 批准或驳回 */
async function review(id: number, action: 'approve' | 'reject'): Promise<void> {
    try {
        await http.post(`/api/v1/hr/leaves/${id}/${action}`);
        ElMessage.success('已审批');
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
