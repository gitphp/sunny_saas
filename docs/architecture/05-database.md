# 数据库

主键 `BIGINT UNSIGNED`。业务表有 `tenant_id`、`created_at`、`updated_at`、`deleted_at`。`users` 是全局账号，没有 `tenant_id`，但仍有删除时间。

每张表有表注释，每一列有列注释。列 `NOT NULL` 并给出默认值：字符串 `''`，整数 `0`，布尔用 `TINYINT UNSIGNED` 默认 `0`，时间 `CURRENT_TIMESTAMP`，`updated_at` 带 `ON UPDATE CURRENT_TIMESTAMP`，JSON 使用 `DEFAULT (JSON_OBJECT())` 或令牌能力上的 `DEFAULT (JSON_ARRAY())`。邮箱、slug、编码这类唯一业务字段同样 `NOT NULL DEFAULT ''`，由 FormRequest 拒绝空字符串。

## 允许为空的例外

这些列仍然有注释：

| 列 | 理由 |
| --- | --- |
| `deleted_at` | 未删除时为空 |
| `users.mobile` | 可选联系方式 |
| `departments.parent_id` | 根部门没有上级 |
| `permissions.parent_id` | 顶级权限没有上级 |
| `tenant_users.department_id` | 成员可以尚未归属部门 |
| `personal_access_tokens.expires_at` | Sanctum 允许永不过期 |
| `personal_access_tokens.last_used_at` | 尚未使用时为空 |

`personal_access_tokens` 跟随 Sanctum，不使用 `deleted_at`。令牌主体是 `users`。

## 索引

- `users`：`uk_users_email(email)`、`uk_users_mobile(mobile)`
- `tenants`：`uk_tenants_slug(slug)`、`idx_tenants_status(status)`、`idx_tenants_plan(plan_id)`
- `plans`：`uk_plans_code(code)`、`idx_plans_status(status)`
- `plan_modules`：`uk_plan_modules(plan_id, module_code)`
- `tenant_users`：`uk_tenant_users_tenant_user(tenant_id, user_id)`、`idx_tenant_users_user(user_id)`、`idx_tenant_users_department(tenant_id, department_id)`
- `departments`：`idx_departments_parent(tenant_id, parent_id)`、`idx_departments_path(tenant_id, path)`
- `modules`：`uk_modules_code(code)`
- `tenant_modules`：`uk_tenant_modules(tenant_id, module_code)`、`idx_tenant_modules_status(tenant_id, status)`
- `permissions`：`uk_permissions_code(code)`、`idx_permissions_parent(parent_id)`、`idx_permissions_type(type)`
- `roles`：`uk_roles_tenant_code(tenant_id, code)`、`idx_roles_scope(tenant_id, data_scope)`，`data_scope` 默认 `self`
- `role_permissions`：`uk_role_permissions(role_id, permission_id)`、`idx_role_permissions_permission(permission_id)`
- `role_departments`：`uk_role_departments(role_id, department_id)`、`idx_role_departments_department(department_id)`
- `tenant_user_roles`：`uk_tenant_user_roles(tenant_user_id, role_id)`、`idx_tenant_user_roles_role(role_id)`

外键加在真实父级上：`tenant_id`、`user_id`、`role_id`、`permission_id`，以及部门的 `parent_id`、`department_id`、`module_code`。删除策略是 RESTRICT，避免级联穿出租户。同一租户的父子部门不靠外键保证，由应用和测试保证。

## 以后的金额

本阶段没有财务表。以后的金额列使用 `DECIMAL(18, 2) NOT NULL DEFAULT 0.00`，禁止 `FLOAT` 和 `DOUBLE`。币种另列保存，不用浮点换算结果入库。
