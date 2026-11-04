/**
 * 当前成员权限状态
 *
 * @package     resources\js\stores
 * @license     MIT License (MIT)
 * @author      Sunny Yang <githup@163.com> QQ4873473
 * @copyright   Copyright (c) 2024 YourCompany. All rights reserved.
 * @link        http://www.budff.com
 */
import { defineStore } from 'pinia';
import { fetchAccess } from '@/api/iam';

export const useAccessStore = defineStore('access', {
    state: () => ({
        permissions: [] as string[],
        loaded: false,
    }),
    actions: {
        /** 读取当前成员的权限码。 */
        async load(): Promise<void> {
            const profile = await fetchAccess();
            this.permissions = profile.permissions;
            this.loaded = true;
        },
        /** 判断当前成员是否拥有某个权限码。 */
        can(code: string): boolean {
            return this.permissions.includes(code);
        },
        /** 切换租户或退出时清空权限缓存。 */
        reset(): void {
            this.permissions = [];
            this.loaded = false;
        },
    },
});


