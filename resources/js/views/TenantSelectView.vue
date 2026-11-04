/**
 * 选择并创建租户页
 *
 * @package     resources\js\views
 * @license     MIT License (MIT)
 * @author      Sunny Yang <githup@163.com> QQ4873473
 * @copyright   Copyright (c) 2024 YourCompany. All rights reserved.
 * @link        http://www.budff.com
 */
<template>
    <div class="page">
        <el-card class="card">
            <template #header>选择租户</template>
            <el-empty v-if="!tenant.tenants.length" description="还没有租户，先创建一个" />
            <el-radio-group v-else v-model="selected" class="list">
                <el-radio v-for="item in tenant.tenants" :key="item.id" :value="item.slug" border>
                    {{ item.name }}（{{ item.slug }}）{{ item.is_owner ? '所有者' : '' }}
                </el-radio>
            </el-radio-group>
            <div class="actions">
                <el-button type="primary" :disabled="!selected" @click="enter">进入</el-button>
            </div>
            <el-divider>创建租户</el-divider>
            <el-form label-position="top" @submit.prevent="create">
                <el-form-item label="名称">
                    <el-input v-model="form.name" />
                </el-form-item>
                <el-form-item label="标识">
                    <el-input v-model="form.slug" placeholder="例如 acme" />
                </el-form-item>
                <el-button type="success" :loading="loading" @click="create">创建并进入</el-button>
            </el-form>
            <el-alert v-if="error" class="error" :title="error" type="error" show-icon :closable="false" />
        </el-card>
    </div>
</template>

<script setup lang="ts">
import { onMounted, reactive, ref } from 'vue';
import { useRouter } from 'vue-router';
import { useTenantStore } from '@/stores/tenant';

const tenant = useTenantStore();
const router = useRouter();
const selected = ref(tenant.slug);
const loading = ref(false);
const error = ref('');
const form = reactive({ name: '', slug: '' });

onMounted(async () => {
    await tenant.fetchTenants();

    if (!selected.value && tenant.tenants[0]) {
        selected.value = tenant.tenants[0].slug;
    }
});

/** 进入选中的租户 */
async function enter(): Promise<void> {
    const current = tenant.tenants.find((item) => item.slug === selected.value);

    if (!current) {
        return;
    }

    tenant.select(current);
    await router.push('/');
}

/** 创建 */
async function create(): Promise<void> {
    loading.value = true;
    error.value = '';

    try {
        await tenant.create(form.name, form.slug);
        await router.push('/');
    } catch (caught) {
        error.value = typeof caught === 'object' && caught && 'response' in caught
            ? ((caught as { response?: { data?: { message?: string } } }).response?.data?.message ?? '创建失败')
            : '创建失败';
    } finally {
        loading.value = false;
    }
}
</script>

<style scoped>
.page {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f5f7fa;
}

.card {
    width: 480px;
}

.list {
    display: flex;
    flex-direction: column;
    gap: 8px;
    align-items: stretch;
}

.actions,
.error {
    margin-top: 16px;
}
</style>


