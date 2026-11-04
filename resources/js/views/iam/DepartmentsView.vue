/**
 * 部门管理页
 *
 * @package     resources\js\views\iam
 * @license     MIT License (MIT)
 * @author      Sunny Yang <githup@163.com> QQ4873473
 * @copyright   Copyright (c) 2024 YourCompany. All rights reserved.
 * @link        http://www.budff.com
 */
<template>
    <div>
        <div class="toolbar">
            <h2>部门</h2>
            <el-button v-if="access.can('iam.departments.store')" type="primary" @click="openCreate()">新建部门</el-button>
        </div>
        <el-table :data="tree" row-key="id" border default-expand-all :tree-props="{ children: 'children' }">
            <el-table-column prop="name" label="名称" min-width="220" />
            <el-table-column prop="sort" label="排序" width="90" />
            <el-table-column label="操作" width="180">
                <template #default="{ row }">
                    <el-button v-if="access.can('iam.departments.store')" link type="primary" @click="openCreate(row.id)">加下级</el-button>
                    <el-button v-if="access.can('iam.departments.update')" link type="primary" @click="openEdit(row)">编辑</el-button>
                    <el-button v-if="access.can('iam.departments.destroy')" link type="danger" @click="remove(row)">删除</el-button>
                </template>
            </el-table-column>
        </el-table>

        <el-dialog v-model="visible" :title="editingId ? '编辑部门' : '新建部门'" width="460px">
            <el-form label-position="top">
                <el-form-item label="名称">
                    <el-input v-model="form.name" maxlength="100" />
                </el-form-item>
                <el-form-item label="上级部门">
                    <el-select v-model="form.parent_id" clearable placeholder="无" style="width: 100%">
                        <el-option v-for="item in parentOptions" :key="item.id" :label="item.label" :value="item.id" />
                    </el-select>
                </el-form-item>
                <el-form-item label="排序">
                    <el-input-number v-model="form.sort" :min="0" />
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
import { computed, onMounted, reactive, ref } from 'vue';
import { ElMessage, ElMessageBox } from 'element-plus';
import { apiErrorMessage } from '@/api/error';
import { fetchDepartments, nestRows, type DepartmentRow } from '@/api/iam';
import { http } from '@/api/http';
import { useAccessStore } from '@/stores/access';

const access = useAccessStore();
const rows = ref<DepartmentRow[]>([]);
const tree = computed(() => nestRows(rows.value));
const visible = ref(false);
const saving = ref(false);
const editingId = ref<number | null>(null);
const form = reactive({ name: '', parent_id: null as number | null, sort: 0 });

const parentOptions = computed(() => {
    const blocked = new Set<number>();

    if (editingId.value) {
        blocked.add(editingId.value);
        rows.value
            .filter((row) => row.path.includes(`/${editingId.value}/`) || row.path.endsWith(`/${editingId.value}`))
            .forEach((row) => blocked.add(row.id));
    }

    return rows.value
        .filter((row) => !blocked.has(row.id))
        .map((row) => ({ id: row.id, label: `${'　'.repeat(row.path.split('/').filter(Boolean).length - 1)}${row.name}` }));
});

onMounted(load);

/** 重新加载列表 */
async function load(): Promise<void> {
    rows.value = await fetchDepartments();
}

/** 打开新建表单 */
function openCreate(parentId: number | null = null): void {
    editingId.value = null;
    form.name = '';
    form.parent_id = parentId;
    form.sort = 0;
    visible.value = true;
}

/** 打开编辑表单 */
function openEdit(row: DepartmentRow): void {
    editingId.value = row.id;
    form.name = row.name;
    form.parent_id = row.parent_id;
    form.sort = row.sort;
    visible.value = true;
}

/** 提交保存 */
async function save(): Promise<void> {
    saving.value = true;

    try {
        const payload = { name: form.name, parent_id: form.parent_id, sort: form.sort };

        if (editingId.value) {
            await http.put(`/api/v1/iam/departments/${editingId.value}`, payload);
        } else {
            await http.post('/api/v1/iam/departments', payload);
        }

        visible.value = false;
        ElMessage.success('部门已保存');
        await load();
    } catch (caught) {
        ElMessage.error(apiErrorMessage(caught));
    } finally {
        saving.value = false;
    }
}

/** 确认后删除 */
async function remove(row: DepartmentRow): Promise<void> {
    try {
        await ElMessageBox.confirm(`删除部门「${row.name}」？`, '确认删除', { type: 'warning' });
        await http.delete(`/api/v1/iam/departments/${row.id}`);
        ElMessage.success('部门已删除');
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
.toolbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 16px;
}

h2 {
    margin: 0;
    font-size: 18px;
}
</style>


