<!--
 * 员工档案页
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
            <h2>员工档案</h2>
            <el-button v-if="access.can('hr.employees.store')" type="primary" @click="openCreate">建立档案</el-button>
        </div>
        <el-table :data="rows" border>
            <el-table-column prop="employee_no" label="工号" width="120" />
            <el-table-column prop="name" label="姓名" width="120" />
            <el-table-column prop="department_name" label="部门" min-width="140" />
            <el-table-column prop="position_name" label="岗位" min-width="140" />
            <el-table-column label="状态" width="100">
                <template #default="{ row }">{{ employeeStatusLabel[row.status] }}</template>
            </el-table-column>
            <el-table-column prop="hired_on" label="入职日期" width="120" />
            <el-table-column prop="left_on" label="离职日期" width="120" />
            <el-table-column label="操作" width="90">
                <template #default="{ row }">
                    <el-button v-if="access.can('hr.employees.update') && row.status !== 2" link type="primary" @click="openEdit(row)">编辑</el-button>
                </template>
            </el-table-column>
        </el-table>
        <el-dialog v-model="visible" :title="current ? '编辑档案' : '建立档案'" width="480px">
            <el-form label-position="top">
                <el-form-item label="工号"><el-input v-model="form.employee_no" /></el-form-item>
                <el-form-item label="姓名"><el-input v-model="form.name" /></el-form-item>
                <el-form-item label="手机"><el-input v-model="form.mobile" /></el-form-item>
                <el-form-item label="邮箱"><el-input v-model="form.email" /></el-form-item>
                <el-form-item label="部门">
                    <el-select v-model="form.department_id" clearable style="width: 100%">
                        <el-option v-for="item in departments" :key="item.id" :label="item.name" :value="item.id" />
                    </el-select>
                </el-form-item>
                <el-form-item label="岗位">
                    <el-select v-model="form.position_id" clearable style="width: 100%">
                        <el-option v-for="item in positions" :key="item.id" :label="item.name" :value="item.id" />
                    </el-select>
                </el-form-item>
            </el-form>
            <template #footer>
                <el-button @click="visible = false">取消</el-button>
                <el-button type="primary" :loading="saving" @click="save">保存</el-button>
            </template>
        </el-dialog>
    </div>
</template>

<script setup lang="ts">
import { onMounted, reactive, ref } from 'vue';
import { ElMessage } from 'element-plus';
import { apiErrorMessage } from '@/api/error';
import { employeeStatusLabel, fetchEmployees, fetchOrganization, fetchPositions, flattenDepartments, type EmployeeRow, type PositionRow } from '@/api/hr';
import { http } from '@/api/http';
import { useAccessStore } from '@/stores/access';

const access = useAccessStore();
const rows = ref<EmployeeRow[]>([]);
const positions = ref<PositionRow[]>([]);
const departments = ref<{ id: number; name: string }[]>([]);
const visible = ref(false);
const saving = ref(false);
const current = ref<EmployeeRow | null>(null);
const form = reactive({
    employee_no: '',
    name: '',
    mobile: '',
    email: '',
    department_id: null as number | null,
    position_id: null as number | null,
});

onMounted(load);

/** 读取档案、岗位和部门 */
async function load(): Promise<void> {
    const [employees, positionRows, organization] = await Promise.all([fetchEmployees(), fetchPositions(), fetchOrganization()]);
    rows.value = employees;
    positions.value = positionRows.filter((item) => item.status === 1);
    departments.value = flattenDepartments(organization.departments);
}

/** 打开新建表单 */
function openCreate(): void {
    current.value = null;
    Object.assign(form, { employee_no: '', name: '', mobile: '', email: '', department_id: null, position_id: null });
    visible.value = true;
}

/** 打开编辑表单 */
function openEdit(row: EmployeeRow): void {
    current.value = row;
    Object.assign(form, {
        employee_no: row.employee_no,
        name: row.name,
        mobile: row.mobile ?? '',
        email: row.email,
        department_id: row.department_id,
        position_id: row.position_id,
    });
    visible.value = true;
}

/** 保存档案。新建时后端固定为待入职。 */
async function save(): Promise<void> {
    saving.value = true;

    try {
        const payload = { ...form, mobile: form.mobile || null };
        if (current.value) {
            await http.put(`/api/v1/hr/employees/${current.value.id}`, payload);
        } else {
            await http.post('/api/v1/hr/employees', payload);
        }

        visible.value = false;
        ElMessage.success('档案已保存');
        await load();
    } catch (caught) {
        ElMessage.error(apiErrorMessage(caught));
    } finally {
        saving.value = false;
    }
}
</script>

<style scoped>
.toolbar { display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; }
h2 { margin: 0; font-size: 18px; }
</style>
