# API

前缀 `/api/v1`。成功和失败都使用同一信封：

```json
{ "code": 0, "message": "ok", "data": {}, "meta": {}, "trace_id": "" }
```

`code` 为 0 表示成功。列表的 `data` 是数组。校验错误把字段错误放进 `data`，HTTP 422，业务码 42200。

| 场景 | HTTP | code |
| --- | --- | --- |
| 未登录 | 401 | 40100 |
| 账号或密码错误 | 401 | 40101 |
| 缺少 X-Tenant | 400 | 40001 |
| 不是成员 | 403 | 40301 |
| 模块未开通 | 403 | 40302 |
| 缺少权限码 | 403 | 40303 |
| 资源不存在或跨租户主键 | 404 | 40400 |
| 校验失败 | 422 | 42200 |
| 服务器错误 | 500 | 50000 |

`TraceIdMiddleware` 接受合法的 UUID `X-Request-Id`，否则生成 UUID，并写到响应头 `X-Request-Id`。

## 路由

- `GET /sanctum/csrf-cookie`
- `POST /api/v1/auth/register`
- `POST /api/v1/auth/login`
- `POST /api/v1/auth/logout`
- `GET /api/v1/me`
- `GET /api/v1/me/tenants`
- `GET /api/v1/plans`
- `POST /api/v1/tenants`，可选 `plan_code`，默认 `basic`
- 需要租户：`GET /api/v1/iam/menus`、`GET /api/v1/iam/me`
- 部门、角色的增删改查
- `GET /api/v1/iam/members`、`POST /api/v1/iam/members`、`PUT /api/v1/iam/members/{member}`、`PUT /api/v1/iam/members/{member}/roles`、`DELETE /api/v1/iam/members/{member}`
- `GET /api/v1/iam/plan`、`PUT /api/v1/iam/plan`
- `GET /api/v1/iam/modules`、`PUT /api/v1/iam/modules/{moduleCode}`
- `GET /api/v1/iam/permissions`

OpenAPI 由 dedoc/scramble 根据路由和 FormRequest 生成，地址是 `GET /docs/api.json`。测试断言本阶段 `/api/v1` 路由都出现在文档中。
