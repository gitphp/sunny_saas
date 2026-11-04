<!--
 * 当前套餐页
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
            <h2>套餐</h2>
        </div>
        <el-alert v-if="current" type="success" :closable="false" :title="`当前套餐：${current.name}`" :description="current.description" />
        <el-table :data="plans" border class="table">
            <el-table-column prop="name" label="名称" min-width="120" />
            <el-table-column prop="code" label="编码" width="120" />
            <el-table-column label="成员上限" width="120">
                <template #default="{ row }">{{ row.member_limit > 0 ? row.member_limit : '不限制' }}</template>
            </el-table-column>
            <el-table-column label="包含模块" min-width="160">
                <template #default="{ row }">{{ row.modules.join('、') }}</template>
            </el-table-column>
            <el-table-column prop="description" label="说明" min-width="240" />
            <el-table-column label="操作" width="120">
                <template #default="{ row }">
                    <el-button
                        v-if="access.can('iam.plans.update') && current?.code !== row.code"
                        link
                        type="primary"
                        :loading="switching === row.code"
                        @click="usePlan(row.code)"
                    >
                        使用
                    </el-button>
                    <span v-else-if="current?.code === row.code">使用中</span>
                </template>
            </el-table-column>
        </el-table>
    </div>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { ElMessage } from 'element-plus';
import { apiErrorMessage } from '@/api/error';
import { fetchCurrentPlan, fetchPlans, type PlanRow } from '@/api/iam';
import { http } from '@/api/http';
import { useAccessStore } from '@/stores/access';

const access = useAccessStore();
const plans = ref<PlanRow[]>([]);
const current = ref<PlanRow | null>(null);
const switching = ref('');

onMounted(load);

/** 读取套餐目录和当前企业套餐 */
async function load(): Promise<void> {
    [plans.value, current.value] = await Promise.all([fetchPlans(), fetchCurrentPlan()]);
}

/** 更换当前企业套餐 */
async function usePlan(code: string): Promise<void> {
    switching.value = code;

    try {
        current.value = (await http.put('/api/v1/iam/plan', { code })).data.data;
        ElMessage.success('套餐已更换');
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

.table {
    margin-top: 16px;
}
</style>
