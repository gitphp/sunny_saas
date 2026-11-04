/**
 * 当前租户状态
 *
 * @package     resources\js\stores
 * @license     MIT License (MIT)
 * @author      Sunny Yang <githup@163.com> QQ4873473
 * @copyright   Copyright (c) 2024 YourCompany. All rights reserved.
 * @link        http://www.budff.com
 */
import { defineStore } from 'pinia';
import { http, type ApiEnvelope } from '@/api/http';

export interface TenantItem {
    id: number;
    name: string;
    slug: string;
    status: number;
    is_owner: boolean;
}

export const useTenantStore = defineStore('tenant', {
    state: () => ({
        tenants: [] as TenantItem[],
        slug: localStorage.getItem('tenant_slug') ?? '',
        name: localStorage.getItem('tenant_name') ?? '',
    }),
    actions: {
        /** 拉取当前用户加入的租户。 */
        async fetchTenants(): Promise<void> {
            const response = await http.get<ApiEnvelope<TenantItem[]>>('/api/v1/me/tenants');
            this.tenants = response.data.data;
        },
        /** 记住当前租户，后续请求用它填 X-Tenant。 */
        select(tenant: TenantItem): void {
            this.slug = tenant.slug;
            this.name = tenant.name;
            localStorage.setItem('tenant_slug', tenant.slug);
            localStorage.setItem('tenant_name', tenant.name);
        },
        /** 创建租户并立刻选中。 */
        async create(name: string, slug: string): Promise<TenantItem> {
            const response = await http.post<ApiEnvelope<TenantItem>>('/api/v1/tenants', { name, slug });
            const tenant = { ...response.data.data, is_owner: true };
            this.tenants.push(tenant);
            this.select(tenant);

            return tenant;
        },
    },
});


