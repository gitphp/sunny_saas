# Sunny SaaS

多租户企业综合管理平台。当前仓库是一个 Laravel 应用：浏览器界面和 API 由同一个站点提供，按模块划分领域，用共享数据库上的 `tenant_id` 隔离企业数据。

已落地的是租户、身份与访问（IAM）、人事（HR）和审批。OA、考试、财务、文件、通知、审计仍未实现。更细的约束写在 `docs/architecture/`。

## 技术

| 层 | 选择 |
| --- | --- |
| 语言与框架 | PHP 8.3+（运行镜像 PHP 8.4-FPM），Laravel 13 |
| 数据库 | MySQL 8.4，字符集 `utf8mb4` |
| 缓存、会话、队列 | Redis 7。宿主机 PHP 没有 redis 扩展，客户端用 Predis |
| 认证 | Laravel Sanctum。浏览器走 Cookie + CSRF，脚本客户端可拿个人访问令牌 |
| 接口文档 | dedoc/scramble，`GET /docs/api.json` |
| 前端 | Vue 3、TypeScript、Vue Router、Pinia、Element Plus、Vite |
| 样式 | Tailwind CSS 4 随 Vite 接入；业务页面以 Element Plus 为主 |
| 运行 | Docker Compose：Nginx 1.27、PHP-FPM、MySQL、Redis |

前后端不拆成两个项目。Vue 源码在 `resources/js`，Laravel Vite 编译进 `public/build`，`resources/views/app.blade.php` 只负责挂载。Nginx 只把 `public/` 交给 PHP-FPM。

没有平台超级管理员，不使用 Filament，不使用 Spatie Permission，也不使用 stancl/tenancy。

## 架构

这是一个按领域拆开的模块化单体，方便以后再拆服务。每个业务模块保持同一形状：

```
Domain                         状态与领域规则
Application                    用例
Application/Contracts          给其他模块调用的接口
Infrastructure/Persistence     Eloquent 模型
Infrastructure/Migrations      本模块迁移
Presentation/Http              控制器、FormRequest、中间件
```

模块 ServiceProvider 用 `loadMigrationsFrom` 加载自己的迁移。控制器只调用本模块的应用服务。

```
app/Shared/Tenancy          租户上下文、缓存键、路径、队列中间件
app/Shared/Presentation     统一响应与 HTTP 中间件
app/Shared/Exceptions       业务异常、缺少租户上下文
app/Modules/Tenant          租户档案、创建租户、按 X-Tenant 解析
app/Modules/Iam             用户、成员、部门、角色、权限、模块开通、套餐查询
app/Modules/Hr              组织视图、岗位、员工档案、入职离职、合同、考勤、请假
app/Modules/Approval        流程定义、版本、审批单、待办、抄送
resources/js                Vue 3 + TypeScript 壳
```

跨模块只能依赖对方的 `Application\Contracts`。例如租户创建后调用 IAM 的 `TenantProvisioner`，解析成员时调用 `MembershipGateway`，人事读取部门时调用 `DepartmentDirectory`。禁止引用另一个模块的模型、Domain 或 Infrastructure。`tests/Unit/ArchitectureBoundaryTest.php` 会扫描这条边界。

### 租户

所有企业共用一个库，业务表带 `tenant_id`。不按租户切库，也不按租户切 Redis DB。

当前租户只来自请求头 `X-Tenant`（企业 slug）。请求体里的 `tenant_id` 会被丢掉。`BelongsToTenant` 在创建时写入上下文中的租户，更新时不允许改掉它。上下文为空时抛 `MissingTenantContextException`，不会退回全表扫描。

解析顺序是：用 slug 找到租户，再通过 IAM 契约确认当前用户是有效成员。租户不存在返回 404，不是成员返回 403。HTTP 中间件在响应结束后清空上下文，队列中间件在任务结束后同样清空。

Redis 业务键前缀是 `t:{tenantId}:`，外面再套 Laravel 自己的键前缀。以后的文件路径必须经过 `TenantPath`，拒绝空段、`.` 和 `..`。

用户是全局账号，`users` 没有 `tenant_id`。一个人通过 `tenant_users` 加入多家企业。部门属于 IAM，用来做数据范围；人事只保存部门编号。

### 权限

登录用户可以自己创建企业，成为 owner，得到数据范围为 `all` 的 `admin` 角色，并自动开通 `iam`。没有单独的平台后台。

权限目录是全局的，类型为菜单、按钮、接口。菜单就是 `type = menu` 的权限。角色、成员、部门、模块开通记录属于企业。owner 绕过权限码，不绕过租户边界，也不绕过模块是否开通。`iam` 不能关闭。

数据范围：`all`、`department_and_children`、`department`、`self`、`custom`。多个角色取并集。范围外的记录按不存在处理，返回 404。

套餐：

| 编码 | 有效成员上限 | 包含模块 |
| --- | --- | --- |
| `basic` | 20 | `iam` |
| `standard` | 200 | `iam`、`hr`、`approval` |

只能开通行套餐里的模块。换成更小的套餐时，不再包含的模块会被关掉；有效成员超过目标上限时不能换。侧栏只显示套餐包含且已经开通的模块。

### 请求

租户内接口的中间件顺序是：追踪号、Sanctum、租户解析、模块开通、权限码。响应信封统一为：

```json
{ "code": 0, "message": "ok", "data": {}, "meta": {}, "trace_id": "" }
```

`code` 为 0 表示成功。常见业务码：未登录 `40100`，密码错误 `40101`，缺少 `X-Tenant` `40001`，非成员 `40301`，模块未开通 `40302`，无权限码 `40303`，不存在或跨租户 `40400`，校验失败 `42200`。

## 已有功能

**身份与访问**

- 注册、登录、退出。浏览器先取 `/sanctum/csrf-cookie`。
- 选择或创建企业。创建时可选套餐，默认基础版。
- 部门、角色、成员、模块开通、套餐、权限目录。
- 成员可以新建全局账号，也可以把已有邮箱加入当前企业。所有者不能停用或移出。

**人事**（标准版，且要在「模块」里开通）

- 组织架构：部门仍在 IAM 维护，这里展示部门树、岗位和尚未离职的人数。
- 岗位、员工档案。新建档案固定为待入职。
- 入职、离职。离职要先批准，办结后员工变为离职。
- 合同、考勤、请假。合同、考勤和请假只面向尚未离职、且已入职的员工。

**审批**（标准版，且要在「模块」里开通）

- 流程分组、流程定义、表单和流程图。发布后生成不可变版本，进行中的单据停留在发起时的版本。
- 条件分支、或签、会签、抄送。审批人可以是指定成员或发起人。
- 待我审批、我的申请、我收到的、我已审批，以及企业审批数据。
- 其他模块通过 `ApprovalGateway` 按流程编码发起，不引用审批表。发起时可以带上 `source_module` 和 `source_id`。

## 运行

本机 3306 和 6379 已被其他项目占用，所以 Compose 把 MySQL 映射到 **13306**，Redis 映射到 **16379**，站点映射到 **8088**。容器内部仍是 3306 和 6379。

需要 Docker（本机在 WSL 里）、Composer、Node.js，以及宿主机 PHP 8.4（测试用，例如 `D:\wamp64\bin\php\php8.4.15`）。

```bash
composer install
copy .env.example .env
php artisan key:generate
npm install
npm run build
```

Windows 上启动容器：

```bash
wsl -e bash -lc 'cd /mnt/d/app/sunny_saas && docker compose up -d --build'
wsl -e bash -lc 'cd /mnt/d/app/sunny_saas && docker compose exec -T php php artisan migrate --force'
```

浏览器打开 [http://localhost:8088](http://localhost:8088)。注册一个账号，创建企业。要用人事或审批，创建时选标准版，或事后在「套餐」里换到标准版，再在「模块」里开通对应模块。

改 Vue 或 TypeScript 时执行 `npm run dev`，页面仍由这个 Laravel 应用提供。改完要给容器里的 Nginx 使用时，再执行一次 `npm run build`。

容器里的数据库和 Redis 主机名是 `mysql`、`redis`，由 Compose 注入，不依赖 `.env` 里给宿主机用的地址。`storage/framework` 使用独立 Docker 卷，避免 Windows 绑定挂载上 PHP `tempnam()` 导致 Blade 编译失败。

数据库账号见 `.env.example`：库 `sunny_saas`，用户 `sunny`，密码 `sunny`，root 密码 `root`。

## 测试

测试连 MySQL 库 `sunny_saas_test` 和 Redis `127.0.0.1:16379`，地址写在 `phpunit.xml`。测试库由 `docker/mysql/init/01-databases.sql` 在数据卷第一次初始化时创建。不要改成 SQLite。

在仓库根目录、PHP 8.4 下执行：

```bash
php artisan test
npx vue-tsc --noEmit
```

MySQL 或 Redis 没起来时，失败就是失败。

## 文档

| 文件 | 内容 |
| --- | --- |
| `docs/architecture/01-overview.md` | 总览与端口 |
| `docs/architecture/02-directory.md` | 目录和模块边界 |
| `docs/architecture/03-tenancy.md` | 租户上下文、缓存、队列 |
| `docs/architecture/04-iam.md` | 成员、权限、套餐 |
| `docs/architecture/05-database.md` | 表、索引、可空列 |
| `docs/architecture/06-api.md` | 信封、业务码、路由 |
| `docs/architecture/07-decisions.md` | 已经锁定的决定 |
| `docs/architecture/erd.md` | 表关系 |

## 许可

MIT License。
