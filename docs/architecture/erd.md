# ER

```mermaid
erDiagram
  users ||--o{ tenant_users : joins
  tenants ||--o{ tenant_users : has
  tenants ||--o{ departments : owns
  departments ||--o{ departments : parent
  departments ||--o{ tenant_users : places
  tenants ||--o{ roles : defines
  plans ||--o{ tenants : assigns
  plans ||--o{ plan_modules : includes
  modules ||--o{ plan_modules : allows
  tenants ||--o{ tenant_modules : enables
  modules ||--o{ tenant_modules : catalogs
  roles ||--o{ role_permissions : grants
  permissions ||--o{ role_permissions : includes
  permissions ||--o{ permissions : parent
  tenant_users ||--o{ tenant_user_roles : has
  roles ||--o{ tenant_user_roles : assigned
  roles ||--o{ role_departments : limits
  departments ||--o{ role_departments : scopes
  tenants ||--o{ hr_positions : owns
  tenants ||--o{ hr_employees : owns
  hr_positions ||--o{ hr_employees : assigns
  hr_employees ||--o{ hr_contracts : signs
  hr_employees ||--o{ hr_attendance_records : clocks
  hr_employees ||--o{ hr_leave_requests : asks
  hr_employees ||--o{ hr_resignations : exits
  tenants ||--o{ approval_groups : owns
  tenants ||--o{ approval_definitions : owns
  approval_groups ||--o{ approval_definitions : groups
  approval_definitions ||--o{ approval_versions : versions
  approval_definitions ||--o{ approval_instances : starts
  approval_versions ||--o{ approval_instances : pins
  approval_instances ||--o{ approval_tasks : assigns
  approval_instances ||--o{ approval_records : logs
```

`hr_employees.department_id`、`hr_positions.department_id` 和 `hr_employees.user_id` 只存编号，图中不画外键。部门仍以 IAM 的 `departments` 为准。

`users`、`modules`、`permissions`、`plans`、`plan_modules` 是全局目录。其余业务表带 `tenant_id`。`personal_access_tokens` 的主体是 `users`，不进入上面的租户关系。
