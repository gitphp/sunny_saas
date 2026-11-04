<?php

/**
 * 菜单与当前成员接口
 *
 * @package     App\Modules\Iam\Presentation\Http\Controllers
 * @license     MIT License (MIT)
 * @author      Sunny Yang <githup@163.com> QQ4873473
 * @copyright   Copyright (c) 2024 YourCompany. All rights reserved.
 * @link        http://www.budff.com
 */

namespace App\Modules\Iam\Presentation\Http\Controllers;

use App\Modules\Iam\Application\Menu\MenuService;
use App\Shared\Presentation\Http\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * 菜单与当前成员接口
 */
class MenuController
{
    /**
     * 注入依赖
     */
    public function __construct(private readonly MenuService $menus) {}

    /**
     * 返回列表
     */
    public function index(Request $request): JsonResponse
    {
        return ApiResponse::success($this->menus->menus((int) $request->user()->id));
    }

    /**
     * 返回当前用户或成员
     */
    public function me(Request $request): JsonResponse
    {
        return ApiResponse::success($this->menus->me((int) $request->user()->id));
    }
}

