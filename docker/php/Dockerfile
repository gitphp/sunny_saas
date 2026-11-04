# PHP 运行镜像
#
# @package     docker\php
# @license     MIT License (MIT)
# @author      Sunny Yang <githup@163.com> QQ4873473
# @copyright   Copyright (c) 2024 YourCompany. All rights reserved.
# @link        http://www.budff.com

FROM php:8.4-fpm

RUN apt-get update \
    && apt-get install -y --no-install-recommends git unzip libzip-dev \
    && docker-php-ext-install pdo_mysql zip bcmath \
    && rm -rf /var/lib/apt/lists/*

COPY php.ini /usr/local/etc/php/conf.d/99-app.ini
COPY www.conf /usr/local/etc/php-fpm.d/zz-clear-env.conf
COPY entrypoint.sh /usr/local/bin/app-entrypoint.sh
RUN chmod +x /usr/local/bin/app-entrypoint.sh

WORKDIR /var/www/html
ENTRYPOINT ["app-entrypoint.sh"]
