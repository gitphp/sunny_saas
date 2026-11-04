<?php

/**
 * 租户开通端口
 *
 * @package     App\Modules\Iam\Application\Contracts
 * @license     MIT License (MIT)
 * @author      Sunny Yang <githup@163.com> QQ4873473
 * @copyright   Copyright (c) 2024 YourCompany. All rights reserved.
 * @link        http://www.budff.com
 */

namespace App\Modules\Iam\Application\Contracts;

/**
 * 租户开通端口
 */
interface TenantProvisioner
{
    /**
     * 把创建者设为所有者并授予管理员角色
     */
    public function provisionOwner(int $tenantId, int $userId): void;
}

