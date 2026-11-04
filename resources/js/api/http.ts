/**
 * 浏览器请求客户端
 *
 * @package     resources\js\api
 * @license     MIT License (MIT)
 * @author      Sunny Yang <githup@163.com> QQ4873473
 * @copyright   Copyright (c) 2024 YourCompany. All rights reserved.
 * @link        http://www.budff.com
 */
import axios from 'axios';

export interface ApiEnvelope<T> {
    code: number;
    message: string;
    data: T;
    meta: Record<string, unknown>;
    trace_id: string;
}

export const http = axios.create({
    baseURL: '/',
    withCredentials: true,
    xsrfCookieName: 'XSRF-TOKEN',
    xsrfHeaderName: 'X-XSRF-TOKEN',
    headers: {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
    },
});

http.interceptors.request.use((config) => {
    const slug = localStorage.getItem('tenant_slug');

    if (slug) {
        config.headers['X-Tenant'] = slug;
    }

    return config;
});

/** 登录前先领取 CSRF Cookie */
export async function ensureCsrfCookie(): Promise<void> {
    await http.get('/sanctum/csrf-cookie');
}


