#!/bin/sh
# 容器启动时补齐可写目录
#
# @package     docker\php
# @license     MIT License (MIT)
# @author      Sunny Yang <githup@163.com> QQ4873473
# @copyright   Copyright (c) 2024 YourCompany. All rights reserved.
# @link        http://www.budff.com

set -e
mkdir -p \
    /var/www/html/storage/framework/views \
    /var/www/html/storage/framework/cache/data \
    /var/www/html/storage/framework/sessions
chown -R www-data:www-data /var/www/html/storage/framework
exec docker-php-entrypoint php-fpm
