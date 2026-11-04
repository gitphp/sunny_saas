<!--
 * 考勤登记页
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
            <h2>考勤</h2>
            <el-button v-if="access.can('hr.attendance.store')" type="primary" @click="visible = true">登记</el-button>
        </div>
        <el-table :data="rows" border>
            <el-table-column prop="employee_name" label="员工" width="120" />
            <el-table-column prop="work_on" label="日期" width="120" />
            <el-table-column prop="check_in" label="上班" width="100" />
            <el-table-column prop="check_out" label="下班" width="100" />
            <el-table-column label="状态" width="100">
                <template #default="{ row }">{{ statusLabel[row.status] }}</template>
            </el-table-column>
        </el-table>
        <el-dialog v-model="visible" title="登记考勤" width="460px">
            <el-form label-position="top">
                <el-form-item label="员工">
                    <el-select v-model="form.employee_id" style="width: 100%">
                        <el-option v-for="item in employees" :key="item.id" :label="item.name" :value="item.id" />
                    </el-select>
                </el-form-item>
                <el-form-item label="日期"><el-date-picker v-model="form.work_on" type="date" value-format="YYYY-MM-DD" /></el-form-item>
                <el-form-item label="上班"><el-time-picker v-model="form.check_in" value-format="HH:mm" format="HH:mm" /></el-form-item>
                <el-form-item label="下班"><el-time-picker v-model="form.check_out" value-format="HH:mm" format="HH:mm" /></el-form-item>
                <el-form-item label="状态">
                    <el-select v-model="form.status" style="width: 100%">
                        <el-option v-for="(label, value) in statusLabel" :key="value" :label="label" :value="value" />
                    </el-select>
                </el-form-item>
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

interface AttendanceRow {
    id: number;
    employee_name: string;
    work_on: string;
    check_in: string | null;
    check_out: string | null;
    status: string;
}

const statusLabel: Record<string, string> = { normal: '正常', late: '迟到', absent: '缺勤', leave: '请假' };
const access = useAccessStore();
const rows = ref<AttendanceRow[]>([]);
const employees = ref<EmployeeRow[]>([]);
const visible = ref(false);
const form = reactive({ employee_id: null as number | null, work_on: '', check_in: '', check_out: '', status: 'normal' });

onMounted(load);

/** 读取考勤和在职员工 */
async function load(): Promise<void> {
    const [response, active] = await Promise.all([
        http.get<ApiEnvelope<AttendanceRow[]>>('/api/v1/hr/attendance'),
        fetchEmployees(1),
    ]);
    rows.value = response.data.data;
    employees.value = active;
}

/** 同一天再次登记会覆盖原记录 */
async function create(): Promise<void> {
    try {
        await http.post('/api/v1/hr/attendance', {
            ...form,
            check_in: form.check_in || null,
            check_out: form.check_out || null,
        });
        visible.value = false;
        ElMessage.success('考勤已登记');
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
