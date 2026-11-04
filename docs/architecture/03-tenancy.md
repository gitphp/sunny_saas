# 租户

共享库，行上带 `tenant_id`。不使用 stancl/tenancy，也不按租户切换数据库或 Redis DB。

`TenantContext` 保存当前 `tenant_id` 和 slug。HTTP 中间件在响应结束后 `clear()`，队列中间件在 `handle` 之后 `clear()`。上下文为空时，`BelongsToTenant` 直接抛 `MissingTenantContextException`，不会退回全表。

`TenantResolver` 定义在 Shared，实现在 Tenant 模块：用 `X-Tenant` 查 slug，再通过 IAM 契约核对有效成员。不是成员则不写入上下文，返回 403。租户不存在返回 404。

`BelongsToTenant` 在创建时把 `tenant_id` 写成上下文中的值，忽略请求体里的 `tenant_id`。更新时如果有人改 `tenant_id`，会改回原值。

`TenantCache` 的逻辑键前缀是 `t:{tenantId}:`。Laravel 的 Redis 前缀（`sunny_saas_`）和缓存前缀（`sunny_saas_cache_`）再包在外面。业务代码不要用裸键写租户数据。缓存连接可以使用 Redis DB 1、队列使用 DB 0，这是 Laravel 的连接划分，不是按租户切库。

队列在 `Queue::createPayloadUsing` 里写入 `tenant_id`。`TenantQueueMiddleware` 在执行前恢复上下文，结束后清空。

`TenantPath` 生成 `{tenantId}/...`。空段、`.` 和 `..` 都会拒绝。以后的文件模块只能经由它落盘。

同一租户内的父子部门由应用保证：上级必须能在当前租户作用域里找到，否则 422。跨租户用主键读取部门返回 404。
