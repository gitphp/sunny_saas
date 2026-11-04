<?php

/**
 * 合同接口
 *
 * @package     App\Modules\Hr\Presentation\Http\Controllers
 * @license     MIT License (MIT)
 * @author      Sunny Yang <githup@163.com> QQ4873473
 * @copyright   Copyright (c) 2024 YourCompany. All rights reserved.
 * @link        http://www.budff.com
 */

namespace App\Modules\Hr\Presentation\Http\Controllers;

use App\Modules\Hr\Application\Contract\ContractService;
use App\Modules\Hr\Presentation\Http\Requests\ContractRequest;
use App\Shared\Presentation\Http\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * 合同接口
 */
class ContractController
{
    /**
     * 注入依赖
     */
    public function __construct(private readonly ContractService $contracts) {}

    /**
     * 返回列表
     */
    public function index(Request $request): JsonResponse
    {
        return ApiResponse::success($this->contracts->list((int) $request->user()->id));
    }

    /**
     * 签订
     */
    public function store(ContractRequest $request): JsonResponse
    {
        return ApiResponse::success($this->contracts->store((int) $request->user()->id, $request->validated()), 'ok', [], 201);
    }

    /**
     * 终止
     */
    public function terminate(Request $request, int $contract): JsonResponse
    {
        return ApiResponse::success($this->contracts->terminate($contract, (int) $request->user()->id));
    }
}
