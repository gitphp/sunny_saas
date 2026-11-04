/**
 * 接口错误文案
 *
 * @package     resources\js\api
 * @license     MIT License (MIT)
 * @author      Sunny Yang <githup@163.com> QQ4873473
 * @copyright   Copyright (c) 2024 YourCompany. All rights reserved.
 * @link        http://www.budff.com
 */
export function apiErrorMessage(caught: unknown): string {
    if (typeof caught !== 'object' || caught === null || !('response' in caught)) {
        return '请求失败';
    }

    const data = (caught as { response?: { data?: { message?: string; data?: Record<string, string[]> } } }).response?.data;
    const errors = data?.data;

    if (errors && typeof errors === 'object') {
        const first = Object.values(errors).find((value) => Array.isArray(value) && value.length > 0);

        if (first && typeof first[0] === 'string') {
            return first[0];
        }
    }

    return data?.message || '请求失败';
}


