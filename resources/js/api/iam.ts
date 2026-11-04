/**
 * 身份与访问接口封装
 *
 * @package     resources\js\api
 * @license     MIT License (MIT)
 * @author      Sunny Yang <githup@163.com> QQ4873473
 * @copyright   Copyright (c) 2024 YourCompany. All rights reserved.
 * @link        http://www.budff.com
 */
import { http, type ApiEnvelope } from '@/api/http';

export interface DepartmentRow {
    id: number;
    parent_id: number | null;
    name: string;
    path: string;
    sort: number;
    children?: DepartmentRow[];
}

export interface PermissionRow {
    id: number;
    parent_id: number | null;
    type: 'menu' | 'button' | 'api';
    code: string;
    name: string;
    route: string;
    method: string;
    sort: number;
    children?: PermissionRow[];
}

export interface RoleRow {
    id: number;
    code: string;
    name: string;
    data_scope: string;
    permission_ids: number[];
    department_ids: number[];
}

export interface MemberRole {
    id: number;
    code: string;
    name: string;
    data_scope: string;
}

export interface MemberRow {
    id: number;
    user_id: number;
    name: string;
    email: string;
    department_id: number | null;
    status: number;
    is_owner: number;
    roles: MemberRole[];
}

export interface ModuleRow {
    code: string;
    name: string;
    description: string;
    status: number;
    enabled_at: string | null;
    required: boolean;
}

export interface AccessProfile {
    member: { id: number; is_owner: number; department_id: number | null; status: number };
    permissions: string[];
}

export const dataScopeOptions = [
    { value: 'all', label: '全部' },
    { value: 'department_and_children', label: '本部门及下级' },
    { value: 'department', label: '本部门' },
    { value: 'self', label: '本人' },
    { value: 'custom', label: '自定义部门' },
];

/** 把数据范围编码显示成中文 */
export function dataScopeLabel(value: string): string {
    return dataScopeOptions.find((item) => item.value === value)?.label ?? value;
}

/** 读取当前成员的权限码 */
export async function fetchAccess(): Promise<AccessProfile> {
    const response = await http.get<ApiEnvelope<AccessProfile>>('/api/v1/iam/me');

    return response.data.data;
}

/** 读取部门列表 */
export async function fetchDepartments(): Promise<DepartmentRow[]> {
    const response = await http.get<ApiEnvelope<DepartmentRow[]>>('/api/v1/iam/departments');

    return response.data.data;
}

/** 读取角色列表 */
export async function fetchRoles(): Promise<RoleRow[]> {
    const response = await http.get<ApiEnvelope<RoleRow[]>>('/api/v1/iam/roles');

    return response.data.data;
}

/** 读取成员列表 */
export async function fetchMembers(): Promise<MemberRow[]> {
    const response = await http.get<ApiEnvelope<MemberRow[]>>('/api/v1/iam/members');

    return response.data.data;
}

/** 读取模块开通列表 */
export async function fetchModules(): Promise<ModuleRow[]> {
    const response = await http.get<ApiEnvelope<ModuleRow[]>>('/api/v1/iam/modules');

    return response.data.data;
}

/** 读取权限目录 */
export async function fetchPermissions(): Promise<PermissionRow[]> {
    const response = await http.get<ApiEnvelope<PermissionRow[]>>('/api/v1/iam/permissions');

    return response.data.data;
}

/** 按上级主键把扁平记录组装成树 */
export function nestRows<T extends { id: number; parent_id: number | null; children?: T[] }>(rows: T[]): T[] {
    const nodes = rows.map((row) => ({ ...row, children: [] as T[] }));
    const byId = new Map(nodes.map((node) => [node.id, node]));
    const roots: T[] = [];

    nodes.forEach((node) => {
        const parent = node.parent_id ? byId.get(node.parent_id) : undefined;

        if (parent) {
            parent.children = parent.children ?? [];
            parent.children.push(node);
        } else {
            roots.push(node);
        }
    });

    return roots;
}


