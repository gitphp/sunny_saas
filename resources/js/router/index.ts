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
import PlanView from '@/views/iam/PlanView.vue';
import RolesView from '@/views/iam/RolesView.vue';
import AttendanceView from '@/views/hr/AttendanceView.vue';
import ContractsView from '@/views/hr/ContractsView.vue';
import EmployeesView from '@/views/hr/EmployeesView.vue';
import LeavesView from '@/views/hr/LeavesView.vue';
import OnboardingView from '@/views/hr/OnboardingView.vue';
import OrganizationView from '@/views/hr/OrganizationView.vue';
import PositionsView from '@/views/hr/PositionsView.vue';
import ResignationsView from '@/views/hr/ResignationsView.vue';
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
                { path: 'iam/plan', name: 'plan', component: PlanView },
                { path: 'hr', redirect: '/hr/organization' },
                { path: 'hr/organization', name: 'hr-organization', component: OrganizationView },
                { path: 'hr/positions', name: 'hr-positions', component: PositionsView },
                { path: 'hr/employees', name: 'hr-employees', component: EmployeesView },
                { path: 'hr/onboarding', name: 'hr-onboarding', component: OnboardingView },
                { path: 'hr/resignations', name: 'hr-resignations', component: ResignationsView },
                { path: 'hr/contracts', name: 'hr-contracts', component: ContractsView },
                { path: 'hr/attendance', name: 'hr-attendance', component: AttendanceView },
                { path: 'hr/leaves', name: 'hr-leaves', component: LeavesView },
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


