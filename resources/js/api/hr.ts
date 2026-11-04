/**
 * 人事接口封装
 *
 * @package     resources\js\api
 * @license     MIT License (MIT)
 * @author      Sunny Yang <githup@163.com> QQ4873473
 * @copyright   Copyright (c) 2024 YourCompany. All rights reserved.
 * @link        http://www.budff.com
 */
import { http, type ApiEnvelope } from '@/api/http';

export interface OrgPosition {
    id: number;
    name: string;
    code: string;
}

export interface OrgNode {
    id: number;
    name: string;
    employee_count: number;
    positions: OrgPosition[];
    children: OrgNode[];
}

export interface PositionRow {
    id: number;
    department_id: number | null;
    department_name: string;
    name: string;
    code: string;
    sort: number;
    status: number;
}

export interface EmployeeRow {
    id: number;
    employee_no: string;
    name: string;
    gender: number;
    mobile: string | null;
    email: string;
    department_id: number | null;
    department_name: string;
    position_id: number | null;
    position_name: string;
    status: number;
    hired_on: string | null;
    left_on: string | null;
}

export const employeeStatusLabel: Record<number, string> = {
    0: '待入职',
    1: '在职',
    2: '离职',
};

/** 读取组织架构 */
export async function fetchOrganization(): Promise<{ departments: OrgNode[]; unassigned_positions: OrgPosition[] }> {
    const response = await http.get<ApiEnvelope<{ departments: OrgNode[]; unassigned_positions: OrgPosition[] }>>('/api/v1/hr/organization');

    return response.data.data;
}

/** 把部门树摊平，供下拉框使用 */
export function flattenDepartments(nodes: OrgNode[], prefix = ''): { id: number; name: string }[] {
    return nodes.flatMap((node) => {
        const name = prefix ? `${prefix} / ${node.name}` : node.name;

        return [{ id: node.id, name }, ...flattenDepartments(node.children, name)];
    });
}

/** 读取员工。status 为空时返回全部可见档案。 */
export async function fetchEmployees(status?: number): Promise<EmployeeRow[]> {
    const response = await http.get<ApiEnvelope<EmployeeRow[]>>('/api/v1/hr/employees', {
        params: status === undefined ? {} : { status },
    });

    return response.data.data;
}

/** 读取岗位 */
export async function fetchPositions(): Promise<PositionRow[]> {
    const response = await http.get<ApiEnvelope<PositionRow[]>>('/api/v1/hr/positions');

    return response.data.data;
}
