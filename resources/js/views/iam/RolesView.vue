<!--
 * 角色管理页
 *
 * @package     resources\js\views\iam
 * @license     MIT License (MIT)
 * @author      Sunny Yang <githup@163.com> QQ4873473
 * @copyright   Copyright (c) 2024 YourCompany. All rights reserved.
 * @link        http://www.budff.com
 -->
<template>
    <div>
        <div class="toolbar">
            <h2>角色</h2>
            <el-button v-if="access.can('iam.roles.store')" type="primary" @click="openCreate">新建角色</el-button>
        </div>
        <el-table :data="roles" border>
            <el-table-column prop="name" label="名称" min-width="140" />
            <el-table-column prop="code" label="编码" min-width="140" />
            <el-table-column label="数据范围" min-width="160">
                <template #default="{ row }">{{ dataScopeLabel(row.data_scope) }}</template>
            </el-table-column>
            <el-table-column label="权限数" width="100">
                <template #default="{ row }">{{ row.permission_ids.length }}</template>
            </el-table-column>
            <el-table-column label="操作" width="160">
                <template #default="{ row }">
                    <el-button v-if="access.can('iam.roles.update')" link type="primary" @click="openEdit(row)">编辑</el-button>
                    <el-button v-if="access.can('iam.roles.destroy')" link type="danger" @click="remove(row)">删除</el-button>
                </template>
            </el-table-column>
        </el-table>

        <el-dialog v-model="visible" :title="editingId ? '编辑角色' : '新建角色'" width="640px">
            <el-form label-position="top">
                <el-form-item label="名称">
                    <el-input v-model="form.name" maxlength="100" />
                </el-form-item>
                <el-form-item label="编码">
                    <el-input v-model="form.code" maxlength="64" placeholder="小写字母、数字或下划线" :disabled="Boolean(editingId)" />
                </el-form-item>
                <el-form-item label="数据范围">
                    <el-select v-model="form.data_scope" style="width: 100%">
                        <el-option v-for="item in dataScopeOptions" :key="item.value" :label="item.label" :value="item.value" />
                    </el-select>
                </el-form-item>
                <el-form-item v-if="form.data_scope === 'custom'" label="自定义部门">
                    <el-select v-model="form.department_ids" multiple style="width: 100%">
                        <el-option v-for="item in departments" :key="item.id" :label="item.name" :value="item.id" />
                    </el-select>
                </el-form-item>
                <el-form-item label="权限">
                    <el-tree
                        ref="treeRef"
                        class="perm-tree"
                        node-key="id"
                        show-checkbox
                        check-strictly
                        default-expand-all
                        :data="permissionTree"
                        :props="{ label: 'label', children: 'children' }"
                    />
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
import { nextTick, onMounted, reactive, ref } from 'vue';
import { ElMessage, ElMessageBox, type ElTree } from 'element-plus';
import { apiErrorMessage } from '@/api/error';
import {
    dataScopeLabel,
    dataScopeOptions,
    fetchDepartments,
    fetchPermissions,
    fetchRoles,
    nestRows,
    type DepartmentRow,
    type PermissionRow,
    type RoleRow,
} from '@/api/iam';
import { http } from '@/api/http';
import { useAccessStore } from '@/stores/access';

interface PermissionNode extends PermissionRow {
    label: string;
    children?: PermissionNode[];
}

const access = useAccessStore();
const roles = ref<RoleRow[]>([]);
const departments = ref<DepartmentRow[]>([]);
const permissionTree = ref<PermissionNode[]>([]);
const visible = ref(false);
const saving = ref(false);
const editingId = ref<number | null>(null);
const treeRef = ref<InstanceType<typeof ElTree>>();
const form = reactive({
    name: '',
    code: '',
    data_scope: 'self',
    department_ids: [] as number[],
});

const typeLabel: Record<string, string> = { menu: '菜单', button: '按钮', api: '接口' };

onMounted(async () => {
    const [roleRows, departmentRows, permissionRows] = await Promise.all([fetchRoles(), fetchDepartments(), fetchPermissions()]);
    roles.value = roleRows;
    departments.value = departmentRows;
    permissionTree.value = decorate(nestRows(permissionRows));
});

/** 给权限节点补上类型标签 */
function decorate(nodes: PermissionRow[]): PermissionNode[] {
    return nodes.map((node) => ({
        ...node,
        label: `${node.name}（${typeLabel[node.type] ?? node.type}）`,
        children: node.children ? decorate(node.children) : [],
    }));
}

/** 打开新建表单 */
function openCreate(): void {
    editingId.value = null;
    form.name = '';
    form.code = '';
    form.data_scope = 'self';
    form.department_ids = [];
    visible.value = true;
    nextTick(() => treeRef.value?.setCheckedKeys([]));
}

/** 打开编辑表单 */
function openEdit(row: RoleRow): void {
    editingId.value = row.id;
    form.name = row.name;
    form.code = row.code;
    form.data_scope = row.data_scope;
    form.department_ids = [...row.department_ids];
    visible.value = true;
    nextTick(() => treeRef.value?.setCheckedKeys(row.permission_ids));
}

/** 提交保存 */
async function save(): Promise<void> {
    saving.value = true;

    try {
        const payload = {
            name: form.name,
            code: form.code,
            data_scope: form.data_scope,
            permission_ids: (treeRef.value?.getCheckedKeys(false) ?? []) as number[],
            department_ids: form.data_scope === 'custom' ? form.department_ids : [],
        };

        if (editingId.value) {
            await http.put(`/api/v1/iam/roles/${editingId.value}`, payload);
        } else {
            await http.post('/api/v1/iam/roles', payload);
        }

        visible.value = false;
        ElMessage.success('角色已保存');
        roles.value = await fetchRoles();
    } catch (caught) {
        ElMessage.error(apiErrorMessage(caught));
    } finally {
        saving.value = false;
    }
}

/** 确认后删除 */
async function remove(row: RoleRow): Promise<void> {
    try {
        await ElMessageBox.confirm(`删除角色「${row.name}」？`, '确认删除', { type: 'warning' });
        await http.delete(`/api/v1/iam/roles/${row.id}`);
        ElMessage.success('角色已删除');
        roles.value = await fetchRoles();
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

.perm-tree {
    width: 100%;
    max-height: 320px;
    overflow: auto;
    border: 1px solid #e5e7eb;
    border-radius: 4px;
    padding: 8px;
}
</style>


