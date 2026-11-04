<!--
 * 入职办理页
 *
 * @package     resources\js\views\hr
 * @license     MIT License (MIT)
 * @author      Sunny Yang <githup@163.com> QQ4873473
 * @copyright   Copyright (c) 2024 YourCompany. All rights reserved.
 * @link        http://www.budff.com
-->
<template>
    <div>
        <div class="toolbar"><h2>入职</h2></div>
        <el-table :data="rows" border>
            <el-table-column prop="employee_no" label="工号" width="120" />
            <el-table-column prop="name" label="姓名" width="120" />
            <el-table-column prop="department_name" label="部门" min-width="140" />
            <el-table-column prop="position_name" label="岗位" min-width="140" />
            <el-table-column label="入职日期" width="180">
                <template #default="{ row }">
                    <el-date-picker v-model="dates[row.id]" type="date" value-format="YYYY-MM-DD" placeholder="默认今天" />
                </template>
            </el-table-column>
            <el-table-column label="操作" width="100">
                <template #default="{ row }">
                    <el-button v-if="access.can('hr.employees.onboard')" link type="primary" @click="onboard(row)">入职</el-button>
                </template>
            </el-table-column>
        </el-table>
    </div>
</template>

<script setup lang="ts">
import { onMounted, reactive, ref } from 'vue';
import { ElMessage } from 'element-plus';
import { apiErrorMessage } from '@/api/error';
import { fetchEmployees, type EmployeeRow } from '@/api/hr';
import { http } from '@/api/http';
import { useAccessStore } from '@/stores/access';

const access = useAccessStore();
const rows = ref<EmployeeRow[]>([]);
const dates = reactive<Record<number, string>>({});

onMounted(load);

/** 只看待入职档案 */
async function load(): Promise<void> {
    rows.value = await fetchEmployees(0);
}

/** 办理入职 */
async function onboard(row: EmployeeRow): Promise<void> {
    try {
        await http.post(`/api/v1/hr/employees/${row.id}/onboard`, { hired_on: dates[row.id] || null });
        ElMessage.success(`${row.name} 已入职`);
        await load();
    } catch (caught) {
        ElMessage.error(apiErrorMessage(caught));
    }
}
</script>

<style scoped>
.toolbar { margin-bottom: 16px; }
h2 { margin: 0; font-size: 18px; }
</style>
