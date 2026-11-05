# 目录

仓库根目录就是 Laravel 应用。Eloquent 模型只放在各自模块的 `Infrastructure/Persistence`。控制器只调用本模块的应用服务。迁移放在模块的 `Infrastructure/Migrations`，由该模块的 ServiceProvider `loadMigrationsFrom` 加载。Laravel 默认的 `users` 迁移已删除，用户表归 IAM。

```
app/Shared/Tenancy          租户上下文、缓存键、路径、队列中间件
app/Shared/Presentation     统一响应与 HTTP 中间件
app/Shared/Exceptions       业务异常与缺少租户上下文
app/Modules/Tenant          租户档案、创建租户、按 X-Tenant 解析
app/Modules/Iam             用户、成员、部门、角色、权限、模块开通
app/Modules/Hr              岗位、员工档案、入职离职、合同、考勤、请假
app/Modules/Approval        流程定义、版本、审批单、任务、抄送
resources/js                Vue 3 + TypeScript 壳
resources/views/app.blade.php
```

每个模块保持同一形状：`Domain`、`Application/Contracts`、`Application`、`Infrastructure/Persistence`、`Infrastructure/Migrations`、`Presentation/Http`，外加模块 ServiceProvider。

跨模块只允许依赖对方的 `Application\Contracts`。Tenant 创建租户后调用 IAM 的 `TenantProvisioner`，按 slug 解析成员时调用 `MembershipGateway`。人事读部门用 `DepartmentDirectory`，审批选人用 `MemberDirectory`，其他模块发起审批用 `ApprovalGateway`。禁止引用对方的模型、Domain 或 Infrastructure。`tests/Unit/ArchitectureBoundaryTest.php` 扫描 `app/Modules` 并执行这条规则。
