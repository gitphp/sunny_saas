<?php

/**
 * 统一 API 响应
 *
 * @package     App\Shared\Presentation\Http
 * @license     MIT License (MIT)
 * @author      Sunny Yang <githup@163.com> QQ4873473
 * @copyright   Copyright (c) 2024 YourCompany. All rights reserved.
 * @link        http://www.budff.com
 */

namespace App\Shared\Presentation\Http;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * 统一 API 响应
 */
class ApiResponse
{
    /**
     * 返回成功响应
     */
    public static function success(mixed $data = null, string $message = 'ok', array $meta = [], int $status = 200): JsonResponse
    {
        return self::envelope(0, $message, $data ?? new \stdClass, $meta, $status);
    }

    /**
     * 返回失败响应
     */
    public static function error(int $code, string $message, mixed $data = null, int $status = 400, array $meta = []): JsonResponse
    {
        return self::envelope($code, $message, $data ?? new \stdClass, $meta, $status);
    }

    /**
     * 组装统一响应体
     */
    private static function envelope(int $code, string $message, mixed $data, array $meta, int $status): JsonResponse
    {
        /** @var Request|null $request */
        $request = request();
        $traceId = (string) ($request?->attributes->get('trace_id') ?? '');

        return response()->json([
            'code' => $code,
            'message' => $message,
            'data' => $data,
            'meta' => $meta === [] ? new \stdClass : $meta,
            'trace_id' => $traceId,
        ], $status, [], JSON_UNESCAPED_UNICODE);
    }
}

