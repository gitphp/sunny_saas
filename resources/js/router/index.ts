/**
 * 前端路由
 *
 * @package     resources\js\router
 * @license     MIT License (MIT)
 * @author      Sunny Yang <githup@163.com> QQ4873473
 * @copyright   Copyright (c) 2024 YourCompany. All rights reserved.
 * @link        http://www.budff.com
 */
import { createRouter, createWebHistory } from 'vue-router';
import AppLayout from '@/layouts/AppLayout.vue';
import LoginView from '@/views/LoginView.vue';
import TenantSelectView from '@/views/TenantSelectView.vue';
import DepartmentsView from '@/views/iam/DepartmentsView.vue';
import MembersView from '@/views/iam/MembersView.vue';
import ModulesView from '@/views/iam/ModulesView.vue';
import PermissionsView from '@/views/iam/PermissionsView.vue';
import RolesView from '@/views/iam/RolesView.vue';
import { useAuthStore } from '@/stores/auth';
import { useTenantStore } from '@/stores/tenant';

const router = createRouter({
    history: createWebHistory(),
    routes: [
        { path: '/login', name: 'login', component: LoginView },
        { path: '/tenants', name: 'tenants', component: TenantSelectView },
        {
            path: '/',
            component: AppLayout,
            children: [
                { path: '', redirect: '/iam/departments' },
                { path: 'iam', redirect: '/iam/departments' },
                { path: 'iam/departments', name: 'departments', component: DepartmentsView },
                { path: 'iam/roles', name: 'roles', component: RolesView },
                { path: 'iam/members', name: 'members', component: MembersView },
                { path: 'iam/modules', name: 'modules', component: ModulesView },
                { path: 'iam/permissions', name: 'permissions', component: PermissionsView },
            ],
        },
    ],
});

router.beforeEach(async (to) => {
    const auth = useAuthStore();
    const tenant = useTenantStore();

    if (to.path === '/login') {
        return true;
    }

    if (!auth.user) {
        try {
            await auth.fetchMe();
        } catch {
            return '/login';
        }
    }

    if (to.path !== '/tenants' && !tenant.slug) {
        return '/tenants';
    }

    return true;
});

export default router;


