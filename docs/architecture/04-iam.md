# IAM

用户是全局账号，`users` 没有 `tenant_id`。一个人通过 `tenant_users` 加入多个租户。部门属于 IAM，给数据范围用。人事只保存部门编号，通过 `DepartmentDirectory` 读取可见部门和数据范围，不引用部门模型。

登录用户 `POST /api/v1/tenants` 创建租户，成为 owner，得到预置角色 `admin`（数据范围 `all`），并开通 `iam` 模块。创建时选择套餐，缺省是 `basic`。没有平台超管。owner 绕过权限码，不绕过租户边界，也不绕过模块开通检查。

套餐是全局目录。`basic` 上限 20 名有效成员，只含 `iam`。`standard` 上限 200，含 `iam`、`hr` 与 `approval`。开通模块时，编码必须落在当前套餐里。换套餐后，不再包含的模块会被关掉。有效成员数超过目标上限时不能换过去。成员上限按当前租户 `status = 1` 的成员计数。侧栏只显示套餐包含且已经开通的模块。审批选人通过 `MemberDirectory` 读取当前企业的有效成员。

企业成员：邮箱已有全局账号时只建立 `tenant_users`，不改对方密码。邮箱不存在时创建全局账号。所有者不能停用，也不能移出企业。停用或移出后，该账号不能再以这个租户访问。

权限是全局目录，类型为 `menu`、`button`、`api`。菜单就是 `type = menu` 的权限，侧栏按当前成员的权限码过滤，管理界面只查看并在角色里分配，不单独增删菜单或权限记录。角色、成员、部门、`tenant_modules` 属于租户。种子只写入模块 `iam`，以及本阶段路由对应的菜单和接口权限，并在开租户时授予 `admin`。身份与访问模块不能从界面关闭。

数据范围：`all`、`department_and_children`、`department`、`self`、`custom`。默认 `self`。`custom` 使用 `role_departments`。多个角色取并集；任一角色为 `all`，或成员是 owner，则不再收窄。部门列表和成员列表都套用这个范围。范围外的部门详情返回 404。

中间件顺序：TraceId、Sanctum 会话或 `auth:sanctum`、TenantMiddleware、模块开通、权限码。日志上下文包含 `trace_id`、`tenant_id`、`user_id`。

浏览器走同源 Sanctum：先取 `/sanctum/csrf-cookie`，再带会话 Cookie。个人访问令牌签发给全局用户，不绑定单个租户；非浏览器客户端可以在登录时传 `device_name` 拿到令牌。两种方式的当前租户都只看 `X-Tenant`。
