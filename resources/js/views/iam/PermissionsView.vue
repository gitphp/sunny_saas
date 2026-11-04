/**
 * 权限目录页
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
            <h2>权限</h2>
            <el-radio-group v-model="type">
                <el-radio-button value="">全部</el-radio-button>
                <el-radio-button value="menu">菜单</el-radio-button>
                <el-radio-button value="button">按钮</el-radio-button>
                <el-radio-button value="api">接口</el-radio-button>
            </el-radio-group>
        </div>
        <el-table :data="filtered" row-key="id" border default-expand-all :tree-props="{ children: 'children' }">
            <el-table-column prop="name" label="名称" min-width="180" />
            <el-table-column label="类型" width="90">
                <template #default="{ row }">{{ typeLabel[row.type] ?? row.type }}</template>
            </el-table-column>
            <el-table-column prop="code" label="编码" min-width="220" />
            <el-table-column prop="method" label="方法" width="90" />
            <el-table-column prop="route" label="路由" min-width="240" />
        </el-table>
        <p class="hint">权限目录随模块注册，在角色里分配。这里只查看，不单独增删。</p>
    </div>
</template>

<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { fetchPermissions, nestRows, type PermissionRow } from '@/api/iam';

const rows = ref<PermissionRow[]>([]);
const type = ref('');
const typeLabel: Record<string, string> = { menu: '菜单', button: '按钮', api: '接口' };

const filtered = computed(() => {
    if (!type.value) {
        return nestRows(rows.value);
    }

    const matched = new Set(rows.value.filter((row) => row.type === type.value).map((row) => row.id));
    const keep = new Set<number>(matched);

    rows.value.forEach((row) => {
        if (!matched.has(row.id) || !row.parent_id) {
            return;
        }

        let parentId: number | null = row.parent_id;

        while (parentId) {
            keep.add(parentId);
            parentId = rows.value.find((item) => item.id === parentId)?.parent_id ?? null;
        }
    });

    return nestRows(rows.value.filter((row) => keep.has(row.id)));
});

onMounted(async () => {
    rows.value = await fetchPermissions();
});
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

.hint {
    color: #6b7280;
    font-size: 13px;
}
</style>


