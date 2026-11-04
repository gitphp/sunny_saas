<!--
 * 成员管理页
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
            <h2>成员</h2>
            <el-button v-if="access.can('iam.members.store')" type="primary" @click="openCreate">添加成员</el-button>
        </div>
        <el-table :data="members" border>
            <el-table-column prop="name" label="姓名" min-width="120" />
            <el-table-column prop="email" label="邮箱" min-width="200" />
            <el-table-column label="部门" min-width="140">
                <template #default="{ row }">{{ departmentName(row.department_id) }}</template>
            </el-table-column>
            <el-table-column label="角色" min-width="180">
                <template #default="{ row }">
                    <el-tag v-for="role in row.roles" :key="role.id" class="tag">{{ role.name }}</el-tag>
                    <span v-if="row.roles.length === 0">未分配</span>
                </template>
            </el-table-column>
            <el-table-column label="身份" width="90">
                <template #default="{ row }">{{ row.is_owner ? '所有者' : '成员' }}</template>
            </el-table-column>
            <el-table-column label="状态" width="90">
                <template #default="{ row }">{{ row.status === 1 ? '有效' : '停用' }}</template>
            </el-table-column>
            <el-table-column label="操作" width="160">
                <template #default="{ row }">
                    <el-button v-if="access.can('iam.members.roles.sync') || access.can('iam.members.update')" link type="primary" @click="openEdit(row)">调整</el-button>
                    <el-button v-if="access.can('iam.members.destroy') && !row.is_owner" link type="danger" @click="remove(row)">移除</el-button>
                </template>
            </el-table-column>
        </el-table>

        <el-dialog v-model="visible" title="调整成员" width="480px">
            <el-form label-position="top">
                <el-form-item label="成员">
                    <el-input :model-value="current ? `${current.name}（${current.email}）` : ''" disabled />
                </el-form-item>
                <el-form-item v-if="access.can('iam.members.update')" label="部门">
                    <el-select v-model="departmentId" clearable placeholder="未分配" style="width: 100%">
                        <el-option v-for="item in departments" :key="item.id" :label="item.name" :value="item.id" />
                    </el-select>
                </el-form-item>
                <el-form-item v-if="access.can('iam.members.roles.sync')" label="角色">
                    <el-select v-model="roleIds" multiple style="width: 100%">
                        <el-option v-for="item in roles" :key="item.id" :label="item.name" :value="item.id" />
                    </el-select>
                </el-form-item>
                <el-form-item v-if="access.can('iam.members.update') && current && !current.is_owner" label="状态">
                    <el-switch v-model="memberStatus" active-text="有效" inactive-text="停用" />
                </el-form-item>
            </el-form>
            <template #footer>
                <el-button @click="visible = false">取消</el-button>
                <el-button type="primary" :loading="saving" @click="save">保存</el-button>
            </template>
        </el-dialog>

        <el-dialog v-model="creating" title="添加成员" width="480px">
            <el-form label-position="top">
                <el-form-item label="姓名">
                    <el-input v-model="createForm.name" />
                </el-form-item>
                <el-form-item label="邮箱">
                    <el-input v-model="createForm.email" />
                </el-form-item>
                <el-form-item label="密码">
                    <el-input v-model="createForm.password" type="password" show-password placeholder="邮箱已注册时可留空" />
                </el-form-item>
                <el-form-item label="部门">
                    <el-select v-model="createForm.department_id" clearable placeholder="未分配" style="width: 100%">
                        <el-option v-for="item in departments" :key="item.id" :label="item.name" :value="item.id" />
                    </el-select>
                </el-form-item>
                <el-form-item label="角色">
                    <el-select v-model="createForm.role_ids" multiple style="width: 100%">
                        <el-option v-for="item in roles" :key="item.id" :label="item.name" :value="item.id" />
                    </el-select>
                </el-form-item>
            </el-form>
            <template #footer>
                <el-button @click="creating = false">取消</el-button>
                <el-button type="primary" :loading="saving" @click="createMember">保存</el-button>
            </template>
        </el-dialog>
    </div>
</template>

<script setup lang="ts">
import { onMounted, reactive, ref } from 'vue';
import { ElMessage, ElMessageBox } from 'element-plus';
import { apiErrorMessage } from '@/api/error';
import { fetchDepartments, fetchMembers, fetchRoles, type DepartmentRow, type MemberRow, type RoleRow } from '@/api/iam';
import { http } from '@/api/http';
import { useAccessStore } from '@/stores/access';

const access = useAccessStore();
const members = ref<MemberRow[]>([]);
const departments = ref<DepartmentRow[]>([]);
const roles = ref<RoleRow[]>([]);
const visible = ref(false);
const saving = ref(false);
const current = ref<MemberRow | null>(null);
const departmentId = ref<number | null>(null);
const roleIds = ref<number[]>([]);
const memberStatus = ref(true);
const creating = ref(false);
const createForm = reactive({
    name: '',
    email: '',
    password: '',
    department_id: null as number | null,
    role_ids: [] as number[],
});

onMounted(async () => {
    [members.value, departments.value, roles.value] = await Promise.all([fetchMembers(), fetchDepartments(), fetchRoles()]);
});

/** 把部门主键显示成名称 */
function departmentName(id: number | null): string {
    if (!id) {
        return '未分配';
    }

    return departments.value.find((item) => item.id === id)?.name ?? '未分配';
}

/** 打开编辑表单 */
function openEdit(row: MemberRow): void {
    current.value = row;
    departmentId.value = row.department_id;
    roleIds.value = row.roles.map((role) => role.id);
    memberStatus.value = row.status === 1;
    visible.value = true;
}

/** 打开添加成员表单 */
function openCreate(): void {
    createForm.name = '';
    createForm.email = '';
    createForm.password = '';
    createForm.department_id = null;
    createForm.role_ids = [];
    creating.value = true;
}

/** 提交保存 */
async function save(): Promise<void> {
    if (!current.value) {
        return;
    }

    saving.value = true;

    try {
        if (access.can('iam.members.update')) {
            await http.put(`/api/v1/iam/members/${current.value.id}`, {
                department_id: departmentId.value,
                status: current.value.is_owner ? 1 : (memberStatus.value ? 1 : 0),
            });
        }

        if (access.can('iam.members.roles.sync')) {
            await http.put(`/api/v1/iam/members/${current.value.id}/roles`, { role_ids: roleIds.value });
        }

        visible.value = false;
        ElMessage.success('成员已更新');
        members.value = await fetchMembers();
    } catch (caught) {
        ElMessage.error(apiErrorMessage(caught));
    } finally {
        saving.value = false;
    }
}

/** 创建全局账号或把已有账号加入当前企业 */
async function createMember(): Promise<void> {
    saving.value = true;

    try {
        await http.post('/api/v1/iam/members', {
            name: createForm.name,
            email: createForm.email,
            password: createForm.password || null,
            department_id: createForm.department_id,
            role_ids: createForm.role_ids,
        });
        creating.value = false;
        ElMessage.success('成员已加入');
        members.value = await fetchMembers();
    } catch (caught) {
        ElMessage.error(apiErrorMessage(caught));
    } finally {
        saving.value = false;
    }
}

/** 解除该账号与当前企业的成员关系 */
async function remove(row: MemberRow): Promise<void> {
    try {
        await ElMessageBox.confirm(`确定把 ${row.name} 移出当前企业？`, '移除成员');
        await http.delete(`/api/v1/iam/members/${row.id}`);
        ElMessage.success('成员已移除');
        members.value = await fetchMembers();
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

.tag {
    margin-right: 6px;
}
</style>


