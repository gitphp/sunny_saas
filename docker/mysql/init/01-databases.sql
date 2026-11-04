-- 初始化业务库和测试库
--
-- @package     docker\mysql\init
-- @license     MIT License (MIT)
-- @author      Sunny Yang <githup@163.com> QQ4873473
-- @copyright   Copyright (c) 2024 YourCompany. All rights reserved.
-- @link        http://www.budff.com

CREATE DATABASE IF NOT EXISTS sunny_saas CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE DATABASE IF NOT EXISTS sunny_saas_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

GRANT ALL PRIVILEGES ON sunny_saas.* TO 'sunny'@'%';
GRANT ALL PRIVILEGES ON sunny_saas_test.* TO 'sunny'@'%';
FLUSH PRIVILEGES;
