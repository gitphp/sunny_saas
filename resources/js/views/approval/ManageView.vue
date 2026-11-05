<!--
 * 审批管理
 *
 * @package     resources\js\views\approval
 * @license     MIT License (MIT)
 * @author      Sunny Yang <githup@163.com> QQ4873473
 * @copyright   Copyright (c) 2024 YourCompany. All rights reserved.
 * @link        http://www.budff.com
-->
<template>
    <div>
        <div class="toolbar">
            <h2>审批管理</h2>
            <div>
                <el-button v-if="access.can('approval.groups.store')" @click="groupVisible = true">新建分组</el-button>
                <el-button v-if="access.can('approval.definitions.store')" type="primary" @click="openCreate">新建流程</el-button>
            </div>
        </div>
        <el-table :data="rows" border>
            <el-table-column prop="name" label="名称" min-width="140" />
            <el-table-column prop="code" label="编码" width="140" />
            <el-table-column prop="group_name" label="分组" width="120" />
            <el-table-column label="已发布" width="90">
                <template #default="{ row }">{{ row.published_version_no ? `V${row.published_version_no}` : '未发布' }}</template>
            </el-table-column>
            <el-table-column label="草稿" width="90">
                <template #default="{ row }">{{ row.draft_version_no ? `V${row.draft_version_no}` : '—' }}</template>
            </el-table-column>
            <el-table-column label="状态" width="90">
                <template #default="{ row }">{{ row.status === 1 ? '启用' : '停用' }}</template>
            </el-table-column>
            <el-table-column label="操作" width="100">
                <template #default="{ row }">
                    <el-button v-if="access.can('approval.definitions.show')" link type="primary" @click="router.push(`/approval/design/${row.id}`)">设计</el-button>
                </template>
            </el-table-column>
        </el-table>
        <el-dialog v-model="groupVisible" title="新建分组" width="420px">
            <el-input v-model="groupName" placeholder="例如假勤" />
            <template #footer>
                <el-button @click="groupVisible = false">取消</el-button>
                <el-button type="primary" @click="createGroup">保存</el-button>
            </template>
        </el-dialog>
        <el-dialog v-model="visible" title="新建流程" width="460px">
            <el-form label-position="top">
                <el-form-item label="名称"><el-input v-model="form.name" /></el-form-item>
                <el-form-item label="编码"><el-input v-model="form.code" placeholder="例如 leave" /></el-form-item>
                <el-form-item label="分组">
                    <el-select v-model="form.group_id" clearable style="width: 100%">
                        <el-option v-for="group in groups" :key="group.id" :label="group.name" :value="group.id" />
                    </el-select>
                </el-form-item>
                <el-form-item label="说明"><el-input v-model="form.description" type="textarea" /></el-form-item>
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
import { useRouter } from 'vue-router';
import { ElMessage } from 'element-plus';
import { apiErrorMessage } from '@/api/error';
import { http, type ApiEnvelope } from '@/api/http';
import { useAccessStore } from '@/stores/access';

interface Row {
    id: number;
    name: string;
    code: string;
    group_name: string;
    status: number;
    published_version_no: number | null;
    draft_version_no: number | null;
}

const access = useAccessStore();
const router = useRouter();
const rows = ref<Row[]>([]);
const groups = ref<{ id: number; name: string }[]>([]);
const visible = ref(false);
const groupVisible = ref(false);
const groupName = ref('');
const form = reactive({ name: '', code: '', group_id: null as number | null, description: '' });

onMounted(load);

/** 读取流程和分组 */
async function load(): Promise<void> {
    const [definitions, groupRows] = await Promise.all([
        http.get<ApiEnvelope<Row[]>>('/api/v1/approval/definitions'),
        http.get<ApiEnvelope<{ id: number; name: string }[]>>('/api/v1/approval/groups'),
    ]);
    rows.value = definitions.data.data;
    groups.value = groupRows.data.data;
}

/** 打开新建流程 */
function openCreate(): void {
    form.name = '';
    form.code = '';
    form.group_id = null;
    form.description = '';
    visible.value = true;
}

/** 新建流程后进入设计器 */
async function create(): Promise<void> {
    try {
        const response = await http.post<ApiEnvelope<{ id: number }>>('/api/v1/approval/definitions', form);
        visible.value = false;
        await router.push(`/approval/design/${response.data.data.id}`);
    } catch (caught) {
        ElMessage.error(apiErrorMessage(caught));
    }
}

/** 新建分组 */
async function createGroup(): Promise<void> {
    try {
        await http.post('/api/v1/approval/groups', { name: groupName.value });
        groupVisible.value = false;
        groupName.value = '';
        ElMessage.success('分组已保存');
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
