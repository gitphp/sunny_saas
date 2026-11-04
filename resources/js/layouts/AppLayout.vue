<!--
 * 后台布局
 *
 * @package     resources\js\layouts
 * @license     MIT License (MIT)
 * @author      Sunny Yang <githup@163.com> QQ4873473
 * @copyright   Copyright (c) 2024 YourCompany. All rights reserved.
 * @link        http://www.budff.com
 -->
<template>
    <el-container class="layout">
        <el-aside width="220px" class="aside">
            <div class="brand">Sunny SaaS</div>
            <div class="tenant">{{ tenant.name || tenant.slug }}</div>
            <el-menu :default-active="route.path" router background-color="#001529" text-color="#fff" active-text-color="#ffd04b">
                <template v-for="item in menus" :key="item.code">
                    <el-sub-menu v-if="item.children.length" :index="item.route || item.code">
                        <template #title>{{ item.name }}</template>
                        <el-menu-item v-for="child in item.children" :key="child.code" :index="child.route || child.code">
                            {{ child.name }}
                        </el-menu-item>
                    </el-sub-menu>
                    <el-menu-item v-else :index="item.route || item.code">{{ item.name }}</el-menu-item>
                </template>
            </el-menu>
        </el-aside>
        <el-container>
            <el-header class="header">
                <span>{{ auth.user?.name }}</span>
                <el-button link type="primary" @click="logout">退出</el-button>
            </el-header>
            <el-main>
                <router-view />
            </el-main>
        </el-container>
    </el-container>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { http, type ApiEnvelope } from '@/api/http';
import { useAccessStore } from '@/stores/access';
import { useAuthStore } from '@/stores/auth';
import { useTenantStore } from '@/stores/tenant';

interface MenuItem {
    id: number;
    code: string;
    name: string;
    route: string;
    icon: string;
    children: MenuItem[];
}

const menus = ref<MenuItem[]>([]);
const auth = useAuthStore();
const access = useAccessStore();
const tenant = useTenantStore();
const router = useRouter();
const route = useRoute();

onMounted(async () => {
    const [response] = await Promise.all([
        http.get<ApiEnvelope<MenuItem[]>>('/api/v1/iam/menus'),
        access.load(),
    ]);
    menus.value = response.data.data;
});

/** 退出登录 */
async function logout(): Promise<void> {
    await auth.logout();
    access.reset();
    tenant.slug = '';
    tenant.name = '';
    await router.push('/login');
}
</script>

<style scoped>
.layout {
    min-height: 100vh;
}

.aside {
    background: #001529;
    color: #fff;
}

.brand,
.tenant {
    padding: 16px;
}

.tenant {
    color: #9fb3c8;
    font-size: 13px;
}

.header {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 12px;
    border-bottom: 1px solid #e5e7eb;
}
</style>


