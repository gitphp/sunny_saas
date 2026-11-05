<?php

/**
 * 供其他模块发起和查询审批的端口
 *
 * @package     App\Modules\Approval\Application\Contracts
 * @license     MIT License (MIT)
 * @author      Sunny Yang <githup@163.com> QQ4873473
 * @copyright   Copyright (c) 2024 YourCompany. All rights reserved.
 * @link        http://www.budff.com
 */

namespace App\Modules\Approval\Application\Contracts;

/**
 * 供其他模块发起和查询审批的端口
 *
 * 调用方只传流程编码和表单数据，不接触审批模型。当前租户必须已经写入上下文。
 */
interface ApprovalGateway
{
    /**
     * 按已发布的流程发起一张审批单
     *
     * @param  array<string, mixed>  $formData
     * @return array<string, mixed>
     */
    public function start(string $definitionCode, int $applicantUserId, string $title, array $formData, string $sourceModule = '', int $sourceId = 0): array;

    /**
     * 读取一张审批单。不存在时返回 null，不抛跨租户细节。
     *
     * @return array<string, mixed>|null
     */
    public function find(int $instanceId): ?array;
}
