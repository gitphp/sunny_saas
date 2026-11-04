# 决策

这些选择已经锁定，后续模块继续遵守。

- 运行镜像是 PHP 8.4-FPM。Laravel 13，MySQL 8.4，Redis 7。宿主机若没有 PHP 8.4，用 WAMP 的 PHP 8.4 跑测试，容器内仍是 8.4-FPM。
- 一个应用放在仓库根目录。Vue 3、TypeScript、Element Plus、Pinia 在 `resources/js`，Laravel Vite 编译，Blade 挂载。Nginx 只服务 `public/`。没有第二个对外源。
- 浏览器与 API 同源。Sanctum SPA 使用会话 Cookie 和 CSRF。个人访问令牌发给全局 `users`，不绑定单一租户。当前租户只来自 `X-Tenant`。
- 共享库加 `tenant_id`。自研 `TenantContext`、`TenantResolver`、`TenantMiddleware`。不引入 stancl/tenancy。
- IAM 自研。不使用 Spatie Permission，不使用 Filament，没有平台超管。
- 部门属于 IAM。以后 HR 只保存 `department_id` 数值，不引用 IAM 的 Eloquent 模型。本阶段没有 `employees` 表。
- 登录用户自助创建租户，成为 owner，获得管理员角色和 `iam` 模块。创建时选定套餐，默认基础版。owner 绕过权限码，不绕过租户边界。套餐决定可开通模块和有效成员上限。
- 权限目录全局。角色、成员、部门、模块开通记录属于租户。
- 跨模块只依赖 `Application/Contracts`。
- 租户作用域在上下文为空时抛错，禁止退回全表扫描。
- 跨租户主键访问返回 404。非成员 403。模块未开通 403。缺少权限码 403。
- 金额规范只写在数据库文档里。IAM 没有金额列。
- 测试库是 MySQL `sunny_saas_test`。不用 SQLite 顶替后宣称通过。
- 会话、缓存、队列使用 Redis。租户隔离靠键前缀 `t:{tenantId}:`，不靠切换 Redis DB。
- 本机 3306/6379 已被其他项目占用，所以 Compose 发布端口是 13306 和 16379。容器内端口不变。这是环境让步，不是改用 MySQL 8.0 或另一套 Redis。
- 宿主机 PHP 8.4 没有 redis 扩展，缓存和队列客户端使用 predis。不按租户切换 Redis DB。
- 基础镜像已带 mbstring 和 opcache。构建只额外安装 pdo_mysql、zip、bcmath。Composer 装在宿主机，依赖目录挂进容器，镜像里不再下载 Composer。
- Windows 绑定挂载上 PHP 的 `tempnam()` 会退回系统临时目录并告警，Blade 编译因此失败。`storage/framework` 使用独立 Docker 卷。
