<?php

/**
 * 审批端口的持久化实现
 *
 * @package     App\Modules\Approval\Infrastructure\Persistence
 * @license     MIT License (MIT)
 * @author      Sunny Yang <githup@163.com> QQ4873473
 * @copyright   Copyright (c) 2024 YourCompany. All rights reserved.
 * @link        http://www.budff.com
 */

namespace App\Modules\Approval\Infrastructure\Persistence;

use App\Modules\Approval\Application\Contracts\ApprovalGateway;
use App\Modules\Approval\Application\Runtime\InstanceService;

/**
 * 审批端口的持久化实现
 */
class EloquentApprovalGateway implements ApprovalGateway
{
    /**
     * 注入依赖
     */
    public function __construct(private readonly InstanceService $instances) {}

    /**
     * 按已发布的流程发起一张审批单
     *
     * @param  array<string, mixed>  $formData
     * @return array<string, mixed>
     */
    public function start(string $definitionCode, int $applicantUserId, string $title, array $formData, string $sourceModule = '', int $sourceId = 0): array
    {
        return $this->instances->start($definitionCode, $applicantUserId, $title, $formData, $sourceModule, $sourceId);
    }

    /**
     * 读取一张审批单
     *
     * @return array<string, mixed>|null
     */
    public function find(int $instanceId): ?array
    {
        return $this->instances->find($instanceId);
    }
}
