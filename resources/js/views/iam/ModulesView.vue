<!--
 * 模块开通页
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
            <h2>模块</h2>
        </div>
        <el-table :data="modules" border>
            <el-table-column prop="name" label="名称" min-width="160" />
            <el-table-column prop="code" label="编码" min-width="120" />
            <el-table-column prop="description" label="说明" min-width="240" />
            <el-table-column label="套餐" width="100">
                <template #default="{ row }">{{ row.included ? '包含' : '未包含' }}</template>
            </el-table-column>
            <el-table-column label="开通" width="140">
                <template #default="{ row }">
                    <el-switch
                        :model-value="row.status === 1"
                        :disabled="row.required || !row.included || !access.can('iam.modules.update') || switching === row.code"
                        :loading="switching === row.code"
                        @change="(value: boolean) => toggle(row, value)"
                    />
                </template>
            </el-table-column>
        </el-table>
        <p class="hint">只能开通行套餐包含的模块。身份与访问保持开通。</p>
    </div>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { ElMessage } from 'element-plus';
import { apiErrorMessage } from '@/api/error';
import { fetchModules, type ModuleRow } from '@/api/iam';
import { http } from '@/api/http';
import { useAccessStore } from '@/stores/access';

const access = useAccessStore();
const modules = ref<ModuleRow[]>([]);
const switching = ref('');

onMounted(async () => {
    modules.value = await fetchModules();
});

/** 切换模块开通状态 */
async function toggle(row: ModuleRow, enabled: boolean): Promise<void> {
    switching.value = row.code;

    try {
        await http.put(`/api/v1/iam/modules/${row.code}`, { status: enabled ? 1 : 0 });
        modules.value = await fetchModules();
        ElMessage.success(enabled ? '模块已开通' : '模块已关闭');
    } catch (caught) {
        ElMessage.error(apiErrorMessage(caught));
    } finally {
        switching.value = '';
    }
}
</script>

<style scoped>
.toolbar {
    margin-bottom: 16px;
}

h2 {
    margin: 0;
    font-size: 18px;
}

.hint {
    color: #6b7280;
    font-size: 13px;
}
</style>


