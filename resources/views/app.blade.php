{{--
/**
 * 挂载 Vue 的页面壳
 *
 * @package     resources\views
 * @license     MIT License (MIT)
 * @author      Sunny Yang <githup@163.com> QQ4873473
 * @copyright   Copyright (c) 2024 YourCompany. All rights reserved.
 * @link        http://www.budff.com
 */
--}}
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Sunny SaaS') }}</title>
    @vite(['resources/js/app.ts'])
</head>
<body>
    <div id="app"></div>
</body>
</html>

