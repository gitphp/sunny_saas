<?php

/**
 * 成员接口
 *
 * @package     App\Modules\Iam\Presentation\Http\Controllers
 * @license     MIT License (MIT)
 * @author      Sunny Yang <githup@163.com> QQ4873473
 * @copyright   Copyright (c) 2024 YourCompany. All rights reserved.
 * @link        http://www.budff.com
 */

namespace App\Modules\Iam\Presentation\Http\Controllers;

use App\Modules\Iam\Application\Member\MemberService;
use App\Modules\Iam\Presentation\Http\Requests\SyncMemberRolesRequest;
use App\Modules\Iam\Presentation\Http\Requests\UpdateMemberRequest;
use App\Shared\Presentation\Http\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * 成员接口
 */
class MemberController
{
    /**
     * 注入依赖
     */
    public function __construct(private readonly MemberService $members) {}

    /**
     * 返回列表
     */
    public function index(Request $request): JsonResponse
    {
        return ApiResponse::success($this->members->list((int) $request->user()->id));
    }

    /**
     * 重建成员的角色分配
     */
    public function syncRoles(SyncMemberRolesRequest $request, int $member): JsonResponse
    {
        return ApiResponse::success(
            $this->members->syncRoles($member, (int) $request->user()->id, $request->validated('role_ids')),
        );
    }

    /**
     * 更新
     */
    public function update(UpdateMemberRequest $request, int $member): JsonResponse
    {
        return ApiResponse::success(
            $this->members->update($member, (int) $request->user()->id, $request->validated()),
        );
    }
}

