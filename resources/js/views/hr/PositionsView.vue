<!--
 * 岗位管理页
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
            <h2>岗位</h2>
            <el-button v-if="access.can('hr.positions.store')" type="primary" @click="openCreate">新建岗位</el-button>
        </div>
        <el-table :data="rows" border>
            <el-table-column prop="name" label="名称" min-width="140" />
            <el-table-column prop="code" label="编码" width="140" />
            <el-table-column prop="department_name" label="部门" min-width="140" />
            <el-table-column label="状态" width="90">
                <template #default="{ row }">{{ row.status === 1 ? '启用' : '停用' }}</template>
            </el-table-column>
            <el-table-column label="操作" width="140">
                <template #default="{ row }">
                    <el-button v-if="access.can('hr.positions.update')" link type="primary" @click="openEdit(row)">编辑</el-button>
                    <el-button v-if="access.can('hr.positions.destroy')" link type="danger" @click="remove(row)">删除</el-button>
                </template>
            </el-table-column>
        </el-table>
        <el-dialog v-model="visible" :title="current ? '编辑岗位' : '新建岗位'" width="460px">
            <el-form label-position="top">
                <el-form-item label="名称"><el-input v-model="form.name" /></el-form-item>
                <el-form-item label="编码"><el-input v-model="form.code" /></el-form-item>
                <el-form-item label="部门">
                    <el-select v-model="form.department_id" clearable placeholder="不挂部门" style="width: 100%">
                        <el-option v-for="item in departments" :key="item.id" :label="item.name" :value="item.id" />
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
import { ElMessage, ElMessageBox } from 'element-plus';
import { apiErrorMessage } from '@/api/error';
import { fetchOrganization, fetchPositions, flattenDepartments, type PositionRow } from '@/api/hr';
import { http } from '@/api/http';
import { useAccessStore } from '@/stores/access';

const access = useAccessStore();
const rows = ref<PositionRow[]>([]);
const departments = ref<{ id: number; name: string }[]>([]);
const visible = ref(false);
const saving = ref(false);
const current = ref<PositionRow | null>(null);
const form = reactive({ name: '', code: '', department_id: null as number | null });

onMounted(load);

/** 读取岗位和可选部门 */
async function load(): Promise<void> {
    const [positions, organization] = await Promise.all([fetchPositions(), fetchOrganization()]);
    rows.value = positions;
    departments.value = flattenDepartments(organization.departments);
}

/** 打开新建表单 */
function openCreate(): void {
    current.value = null;
    form.name = '';
    form.code = '';
    form.department_id = null;
    visible.value = true;
}

/** 打开编辑表单 */
function openEdit(row: PositionRow): void {
    current.value = row;
    form.name = row.name;
    form.code = row.code;
    form.department_id = row.department_id;
    visible.value = true;
}

/** 保存岗位 */
async function save(): Promise<void> {
    saving.value = true;

    try {
        if (current.value) {
            await http.put(`/api/v1/hr/positions/${current.value.id}`, form);
        } else {
            await http.post('/api/v1/hr/positions', form);
        }

        visible.value = false;
        ElMessage.success('岗位已保存');
        await load();
    } catch (caught) {
        ElMessage.error(apiErrorMessage(caught));
    } finally {
        saving.value = false;
    }
}

/** 删除岗位 */
async function remove(row: PositionRow): Promise<void> {
    try {
        await ElMessageBox.confirm(`确定删除岗位 ${row.name}？`, '删除岗位');
        await http.delete(`/api/v1/hr/positions/${row.id}`);
        ElMessage.success('岗位已删除');
        await load();
    } catch (caught) {
        if (caught === 'cancel' || caught === 'close') {
            return;
        }

        ElMessage.error(apiErrorMessage(caught));
    }
}
</script>

<style scoped>
.toolbar { display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; }
h2 { margin: 0; font-size: 18px; }
</style>
