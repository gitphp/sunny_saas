# 总览

Sunny SaaS 第一阶段是仓库根目录上的一个 Laravel 13 应用。浏览器壳是 `resources/js` 里的 Vue 3，由 Laravel Vite 编译，Blade 只负责挂载。Nginx 只把 `public/` 交给 PHP-FPM。没有独立前端站点。人事模块已经落地。OA、Exam、Finance、Workflow、File、Notification、Audit 仍未实现。

请求经过追踪、Sanctum 会话或个人访问令牌、租户解析、成员校验、模块开通和权限码。当前租户只来自请求头 `X-Tenant`（slug）。请求体里的 `tenant_id` 会被丢掉。

## 启动

本机 3306 和 6379 已被其他容器占用，Compose 把 MySQL 8.4 映射到 **13306**，Redis 7 映射到 **16379**，Nginx 映射到 **8088**。容器内部仍是 3306 和 6379。Windows 上的 Docker 在 WSL 里：

```bash
wsl -e bash -lc 'cd /mnt/d/app/sunny_saas && docker compose up -d --build'
wsl -e bash -lc 'cd /mnt/d/app/sunny_saas && docker compose exec php php artisan migrate'
```

浏览器打开 `http://localhost:8088`。前端资源：

```bash
npm install
npm run build
```

开发热更新使用 `npm run dev`，仍然由这一个 Laravel 应用提供页面。

`.env` 在容器里使用服务名 `mysql` 和 `redis`。宿主机上的 PHPUnit 不读这组地址，见下文。

## 测试

测试库 `sunny_saas_test` 由 `docker/mysql/init/01-databases.sql` 在 MySQL 数据卷首次初始化时创建，并授权给用户 `sunny`。`phpunit.xml` 固定连接发布端口：

- host `127.0.0.1`
- port `13306`
- database `sunny_saas_test`
- Redis `127.0.0.1:16379`

在仓库根目录、PHP 8.4 下执行：

```bash
php artisan test
```

不要把断言改到 SQLite。MySQL 或 Redis 没起来时，失败就是失败。
