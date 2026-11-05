/**
 * 审批接口封装
 *
 * @package     resources\js\api
 * @license     MIT License (MIT)
 * @author      Sunny Yang <githup@163.com> QQ4873473
 * @copyright   Copyright (c) 2024 YourCompany. All rights reserved.
 * @link        http://www.budff.com
 */
import { http, type ApiEnvelope } from '@/api/http';

export interface FormField {
    key: string;
    type: string;
    label: string;
    required: boolean;
    options: { label: string; value: string }[];
}

export interface FlowNode {
    key: string;
    type: string;
    name: string;
    assignee_type: string;
    user_ids: number[];
    sign: string;
}

export interface FlowEdge {
    from: string;
    to: string;
    priority: number;
    when: { field: string; op: string; value: string | number }[];
}

export interface FlowSchema {
    nodes: FlowNode[];
    edges: FlowEdge[];
}

export interface CatalogDefinition {
    id: number;
    code: string;
    name: string;
    icon: string;
    description: string;
    allow_revoke: number;
    version_no: number;
    form_schema: { fields: FormField[] };
    flow_schema: FlowSchema;
}

export interface CatalogGroup {
    id: number;
    name: string;
    definitions: CatalogDefinition[];
}

export interface ApprovalMember {
    user_id: number;
    name: string;
    email: string;
}

export interface DefinitionDetail {
    id: number;
    group_id: number | null;
    code: string;
    name: string;
    icon: string;
    description: string;
    status: number;
    allow_revoke: number;
    editing: {
        id: number;
        version_no: number;
        status: number;
        form_schema: { fields: FormField[] };
        flow_schema: FlowSchema;
    } | null;
    versions: { id: number; version_no: number; status: number; published_at: string | null }[];
}

export interface InstanceDetail {
    id: number;
    serial_no: string;
    title: string;
    definition_name: string;
    version_no: number;
    applicant_name: string;
    status: number;
    form_schema: { fields: FormField[] };
    form_data: Record<string, string | number | null>;
    current_node_key: string;
    allow_revoke: number;
    can_approve: boolean;
    can_revoke: boolean;
    submitted_at: string | null;
    records: { id: number; action: string; comment: string; actor_name: string; created_at: string | null }[];
    tasks: { id: number; node_name: string; assignee_name: string; task_type: string; status: number }[];
}

export const instanceStatusLabel: Record<number, string> = {
    1: '审批中',
    2: '已通过',
    3: '已驳回',
    4: '已撤销',
};

export const versionStatusLabel: Record<number, string> = {
    0: '草稿',
    1: '已发布',
    2: '已归档',
};

export const actionLabel: Record<string, string> = {
    submit: '提交',
    approve: '同意',
    reject: '驳回',
    cc: '抄送',
    revoke: '撤销',
    comment: '评论',
};

/** 读取可发起的流程 */
export async function fetchCatalog(): Promise<CatalogGroup[]> {
    const response = await http.get<ApiEnvelope<CatalogGroup[]>>('/api/v1/approval/catalog');

    return response.data.data;
}

/** 读取流程详情 */
export async function fetchDefinition(id: number): Promise<DefinitionDetail> {
    const response = await http.get<ApiEnvelope<DefinitionDetail>>(`/api/v1/approval/definitions/${id}`);

    return response.data.data;
}

/** 读取审批单 */
export async function fetchInstance(id: number): Promise<InstanceDetail> {
    const response = await http.get<ApiEnvelope<InstanceDetail>>(`/api/v1/approval/instances/${id}`);

    return response.data.data;
}
