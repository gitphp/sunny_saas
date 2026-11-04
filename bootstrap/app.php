<?php

/**
 * 应用启动与异常渲染
 *
 * @package     bootstrap
 * @license     MIT License (MIT)
 * @author      Sunny Yang <githup@163.com> QQ4873473
 * @copyright   Copyright (c) 2024 YourCompany. All rights reserved.
 * @link        http://www.budff.com
 */

use App\Modules\Iam\Presentation\Http\Middleware\EnsureModuleEnabled;
use App\Modules\Iam\Presentation\Http\Middleware\EnsurePermission;
use App\Shared\Exceptions\BusinessException;
use App\Shared\Exceptions\ErrorCode;
use App\Shared\Exceptions\MissingTenantContextException;
use App\Shared\Presentation\Http\ApiResponse;
use App\Shared\Presentation\Middleware\TenantMiddleware;
use App\Shared\Presentation\Middleware\TraceIdMiddleware;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        apiPrefix: 'api/v1',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->redirectGuestsTo(fn () => null);
        $middleware->statefulApi();
        $middleware->prependToGroup('api', TraceIdMiddleware::class);
        $middleware->alias([
            'tenant' => TenantMiddleware::class,
            'module' => EnsureModuleEnabled::class,
            'permission' => EnsurePermission::class,
        ]);
        // 租户解析要早于路由模型绑定，否则绑定会在没有租户的情况下查表。
        $middleware->prependToPriorityList(
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            TenantMiddleware::class,
        );
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->dontReport([
            BusinessException::class,
        ]);

        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson()
        );

        $exceptions->render(function (BusinessException $e, Request $request) {
            if (! $request->is('api/*') && ! $request->expectsJson()) {
                return null;
            }

            return ApiResponse::error($e->errorCode, $e->getMessage(), $e->data, $e->httpStatus);
        });

        $exceptions->render(function (ValidationException $e, Request $request) {
            if (! $request->is('api/*') && ! $request->expectsJson()) {
                return null;
            }

            return ApiResponse::error(ErrorCode::VALIDATION, '校验失败', $e->errors(), 422);
        });

        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if (! $request->is('api/*') && ! $request->expectsJson()) {
                return null;
            }

            return ApiResponse::error(ErrorCode::UNAUTHENTICATED, '未登录', null, 401);
        });

        $exceptions->render(function (MissingTenantContextException $e, Request $request) {
            if (! $request->is('api/*') && ! $request->expectsJson()) {
                return null;
            }

            return ApiResponse::error(ErrorCode::SERVER, '缺少租户上下文', null, 500);
        });

        $exceptions->render(function (HttpExceptionInterface $e, Request $request) {
            if (! $request->is('api/*') && ! $request->expectsJson()) {
                return null;
            }

            $status = $e->getStatusCode();
            $code = match ($status) {
                401 => ErrorCode::UNAUTHENTICATED,
                403 => ErrorCode::FORBIDDEN,
                404 => ErrorCode::NOT_FOUND,
                422 => ErrorCode::VALIDATION,
                default => $status >= 500 ? ErrorCode::SERVER : ErrorCode::BAD_REQUEST,
            };
            $message = $e->getMessage() !== '' ? $e->getMessage() : '请求失败';

            return ApiResponse::error($code, $message, null, $status);
        });

        $exceptions->render(function (Throwable $e, Request $request) {
            if (! $request->is('api/*') && ! $request->expectsJson()) {
                return null;
            }

            $message = config('app.debug') ? $e->getMessage() : '服务器错误';

            return ApiResponse::error(ErrorCode::SERVER, $message !== '' ? $message : '服务器错误', null, 500);
        });
    })->create();

