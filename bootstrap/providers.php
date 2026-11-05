<?php

/**
 * 服务提供者清单
 *
 * @package     bootstrap
 * @license     MIT License (MIT)
 * @author      Sunny Yang <githup@163.com> QQ4873473
 * @copyright   Copyright (c) 2024 YourCompany. All rights reserved.
 * @link        http://www.budff.com
 */

use App\Modules\Approval\ApprovalServiceProvider;
use App\Modules\Hr\HrServiceProvider;
use App\Modules\Iam\IamServiceProvider;
use App\Modules\Tenant\TenantServiceProvider;
use App\Providers\AppServiceProvider;
use App\Shared\SharedServiceProvider;

return [
    AppServiceProvider::class,
    SharedServiceProvider::class,
    IamServiceProvider::class,
    TenantServiceProvider::class,
    HrServiceProvider::class,
    ApprovalServiceProvider::class,
];

