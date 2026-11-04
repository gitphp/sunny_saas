/**
 * 登录状态
 *
 * @package     resources\js\stores
 * @license     MIT License (MIT)
 * @author      Sunny Yang <githup@163.com> QQ4873473
 * @copyright   Copyright (c) 2024 YourCompany. All rights reserved.
 * @link        http://www.budff.com
 */
import { defineStore } from 'pinia';
import { ensureCsrfCookie, http, type ApiEnvelope } from '@/api/http';

export interface AuthUser {
    id: number;
    name: string;
    email: string;
    mobile: string | null;
    status: number;
}

export const useAuthStore = defineStore('auth', {
    state: () => ({
        user: null as AuthUser | null,
    }),
    actions: {
        /** 读取当前登录用户。 */
        async fetchMe(): Promise<void> {
            const response = await http.get<ApiEnvelope<AuthUser>>('/api/v1/me');
            this.user = response.data.data;
        },
        /** 注册并保持登录会话。 */
        async register(payload: { name: string; email: string; password: string; password_confirmation: string }): Promise<void> {
            await ensureCsrfCookie();
            const response = await http.post<ApiEnvelope<{ user: AuthUser }>>('/api/v1/auth/register', payload);
            this.user = response.data.data.user;
        },
        /** 登录并保持会话。 */
        async login(email: string, password: string): Promise<void> {
            await ensureCsrfCookie();
            const response = await http.post<ApiEnvelope<{ user: AuthUser }>>('/api/v1/auth/login', {
                email,
                password,
            });
            this.user = response.data.data.user;
        },
        /** 退出并清掉本地租户选择。 */
        async logout(): Promise<void> {
            await http.post('/api/v1/auth/logout');
            this.user = null;
            localStorage.removeItem('tenant_slug');
        },
    },
});


