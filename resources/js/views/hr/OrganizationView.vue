<!--
 * 组织架构页
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
            <h2>组织架构</h2>
        </div>
        <p class="hint">部门在「组织权限 / 部门」里维护。这里按部门挂岗位，并统计尚未离职的员工。</p>
        <el-table :data="departments" row-key="id" border default-expand-all :tree-props="{ children: 'children' }">
            <el-table-column prop="name" label="部门" min-width="180" />
            <el-table-column prop="employee_count" label="在册人数" width="110" />
            <el-table-column label="岗位" min-width="220">
                <template #default="{ row }">{{ positionNames(row) }}</template>
            </el-table-column>
        </el-table>
        <p v-if="unassigned.length" class="hint">未挂部门的岗位：{{ unassigned.map((item) => item.name).join('、') }}</p>
    </div>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { fetchOrganization, type OrgNode, type OrgPosition } from '@/api/hr';

const departments = ref<OrgNode[]>([]);
const unassigned = ref<OrgPosition[]>([]);

/** 把部门上的岗位名称拼成一行 */
function positionNames(row: OrgNode): string {
    return row.positions.map((item) => item.name).join('、') || '未设岗位';
}

onMounted(async () => {
    const data = await fetchOrganization();
    departments.value = data.departments;
    unassigned.value = data.unassigned_positions;
});
</script>

<style scoped>
.toolbar { margin-bottom: 16px; }
h2 { margin: 0; font-size: 18px; }
.hint { color: #6b7280; font-size: 13px; }
</style>
