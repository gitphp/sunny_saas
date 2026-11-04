<!--
 * 合同管理页
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
            <h2>合同</h2>
            <el-button v-if="access.can('hr.contracts.store')" type="primary" @click="visible = true">签订合同</el-button>
        </div>
        <el-table :data="rows" border>
            <el-table-column prop="contract_no" label="编号" width="140" />
            <el-table-column prop="employee_name" label="员工" width="120" />
            <el-table-column label="类型" width="120">
                <template #default="{ row }">{{ typeLabel[row.type] }}</template>
            </el-table-column>
            <el-table-column prop="start_on" label="开始" width="120" />
            <el-table-column prop="end_on" label="结束" width="120" />
            <el-table-column label="状态" width="100">
                <template #default="{ row }">{{ row.status === 1 ? '履行中' : '已终止' }}</template>
            </el-table-column>
            <el-table-column label="操作" width="90">
                <template #default="{ row }">
                    <el-button v-if="row.status === 1 && access.can('hr.contracts.terminate')" link type="danger" @click="terminate(row.id)">终止</el-button>
                </template>
            </el-table-column>
        </el-table>
        <el-dialog v-model="visible" title="签订合同" width="460px">
            <el-form label-position="top">
                <el-form-item label="员工">
                    <el-select v-model="form.employee_id" style="width: 100%">
                        <el-option v-for="item in employees" :key="item.id" :label="item.name" :value="item.id" />
                    </el-select>
                </el-form-item>
                <el-form-item label="编号"><el-input v-model="form.contract_no" /></el-form-item>
                <el-form-item label="类型">
                    <el-select v-model="form.type" style="width: 100%">
                        <el-option label="固定期限" value="fixed" />
                        <el-option label="无固定期限" value="open" />
                        <el-option label="实习" value="intern" />
                    </el-select>
                </el-form-item>
                <el-form-item label="签订日"><el-date-picker v-model="form.signed_on" type="date" value-format="YYYY-MM-DD" /></el-form-item>
                <el-form-item label="开始"><el-date-picker v-model="form.start_on" type="date" value-format="YYYY-MM-DD" /></el-form-item>
                <el-form-item v-if="form.type !== 'open'" label="结束"><el-date-picker v-model="form.end_on" type="date" value-format="YYYY-MM-DD" /></el-form-item>
            </el-form>
            <template #footer>
                <el-button @click="visible = false">取消</el-button>
                <el-button type="primary" @click="create">保存</el-button>
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

interface ContractRow {
    id: number;
    employee_name: string;
    contract_no: string;
    type: string;
    start_on: string;
    end_on: string | null;
    status: number;
}

const typeLabel: Record<string, string> = { fixed: '固定期限', open: '无固定期限', intern: '实习' };
const access = useAccessStore();
const rows = ref<ContractRow[]>([]);
const employees = ref<EmployeeRow[]>([]);
const visible = ref(false);
const form = reactive({ employee_id: null as number | null, contract_no: '', type: 'fixed', signed_on: '', start_on: '', end_on: '' });

onMounted(load);

/** 读取合同和可签订的员工 */
async function load(): Promise<void> {
    const [response, people] = await Promise.all([
        http.get<ApiEnvelope<ContractRow[]>>('/api/v1/hr/contracts'),
        fetchEmployees(),
    ]);
    rows.value = response.data.data;
    employees.value = people.filter((item) => item.status !== 2);
}

/** 签订合同 */
async function create(): Promise<void> {
    try {
        await http.post('/api/v1/hr/contracts', { ...form, end_on: form.type === 'open' ? null : form.end_on });
        visible.value = false;
        ElMessage.success('合同已签订');
        await load();
    } catch (caught) {
        ElMessage.error(apiErrorMessage(caught));
    }
}

/** 终止履行中的合同 */
async function terminate(id: number): Promise<void> {
    try {
        await http.post(`/api/v1/hr/contracts/${id}/terminate`);
        ElMessage.success('合同已终止');
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
