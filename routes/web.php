<?php

/**
 * 页面路由
 *
 * @package     routes
 * @license     MIT License (MIT)
 * @author      Sunny Yang <githup@163.com> QQ4873473
 * @copyright   Copyright (c) 2024 YourCompany. All rights reserved.
 * @link        http://www.budff.com
 */

use Illuminate\Support\Facades\Route;

Route::view('/', 'app');
Route::view('/{any}', 'app')->where('any', '^(?!api(?:/|$)|sanctum(?:/|$)|up$|docs(?:/|$)).*$');

