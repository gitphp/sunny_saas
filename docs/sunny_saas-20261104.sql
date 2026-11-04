/*
 Navicat Premium Dump SQL

 Source Server         : local_8
 Source Server Type    : MySQL
 Source Server Version : 80413 (8.4.13)
 Source Host           : localhost:13306
 Source Schema         : sunny_saas

 Target Server Type    : MySQL
 Target Server Version : 80413 (8.4.13)
 File Encoding         : 65001

 Date: 04/11/2026 17:35:24
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for cache
-- ----------------------------
DROP TABLE IF EXISTS `cache`;
CREATE TABLE `cache`  (
  `key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL,
  PRIMARY KEY (`key`) USING BTREE,
  INDEX `cache_expiration_index`(`expiration` ASC) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of cache
-- ----------------------------

-- ----------------------------
-- Table structure for cache_locks
-- ----------------------------
DROP TABLE IF EXISTS `cache_locks`;
CREATE TABLE `cache_locks`  (
  `key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL,
  PRIMARY KEY (`key`) USING BTREE,
  INDEX `cache_locks_expiration_index`(`expiration` ASC) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of cache_locks
-- ----------------------------

-- ----------------------------
-- Table structure for departments
-- ----------------------------
DROP TABLE IF EXISTS `departments`;
CREATE TABLE `departments`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
  `tenant_id` bigint UNSIGNED NOT NULL DEFAULT 0 COMMENT '租户主键',
  `parent_id` bigint UNSIGNED NULL DEFAULT NULL COMMENT '上级部门，根部门为空',
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '部门名称',
  `path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '物化路径，形如 /1/5',
  `sort` int NOT NULL DEFAULT 0 COMMENT '排序',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT '删除时间，未删除时为空',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_departments_parent`(`tenant_id` ASC, `parent_id` ASC) USING BTREE,
  INDEX `idx_departments_path`(`tenant_id` ASC, `path` ASC) USING BTREE,
  INDEX `fk_departments_parent`(`parent_id` ASC) USING BTREE,
  CONSTRAINT `fk_departments_parent` FOREIGN KEY (`parent_id`) REFERENCES `departments` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `fk_departments_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 1000003 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '部门，隶属租户，供数据范围使用' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of departments
-- ----------------------------
INSERT INTO `departments` VALUES (1000000, 10000, NULL, '人事部', '/1000000', 0, '2026-11-04 07:10:35', '2026-11-04 07:10:35', NULL);
INSERT INTO `departments` VALUES (1000001, 10001, NULL, '研发', '/1000001', 0, '2026-11-04 08:39:33', '2026-11-04 08:39:33', NULL);
INSERT INTO `departments` VALUES (1000002, 10002, NULL, 'IT部', '/1000002', 0, '2026-11-04 08:53:26', '2026-11-04 08:53:26', NULL);

-- ----------------------------
-- Table structure for failed_jobs
-- ----------------------------
DROP TABLE IF EXISTS `failed_jobs`;
CREATE TABLE `failed_jobs`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `failed_jobs_uuid_unique`(`uuid` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of failed_jobs
-- ----------------------------

-- ----------------------------
-- Table structure for hr_attendance_records
-- ----------------------------
DROP TABLE IF EXISTS `hr_attendance_records`;
CREATE TABLE `hr_attendance_records`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
  `tenant_id` bigint UNSIGNED NOT NULL DEFAULT 0 COMMENT '租户主键',
  `employee_id` bigint UNSIGNED NOT NULL DEFAULT 0 COMMENT '员工主键',
  `work_on` date NOT NULL COMMENT '考勤日期',
  `check_in` time NULL DEFAULT NULL COMMENT '上班时间，缺卡时为空',
  `check_out` time NULL DEFAULT NULL COMMENT '下班时间，缺卡时为空',
  `status` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'normal' COMMENT '状态：normal正常 late迟到 absent缺勤 leave请假',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT '删除时间，未删除时为空',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `uk_hr_attendance_day`(`tenant_id` ASC, `employee_id` ASC, `work_on` ASC) USING BTREE,
  INDEX `fk_hr_attendance_employee`(`employee_id` ASC) USING BTREE,
  CONSTRAINT `fk_hr_attendance_employee` FOREIGN KEY (`employee_id`) REFERENCES `hr_employees` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `fk_hr_attendance_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 1010002 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '考勤记录' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of hr_attendance_records
-- ----------------------------
INSERT INTO `hr_attendance_records` VALUES (1010000, 10001, 6000000, '2026-03-02', '09:05:00', '18:00:00', 'late', '2026-11-04 08:39:36', '2026-11-04 08:39:36', NULL);
INSERT INTO `hr_attendance_records` VALUES (1010001, 10002, 6000001, '2026-11-04', '08:58:00', '18:58:00', 'normal', '2026-11-04 08:58:21', '2026-11-04 08:58:21', NULL);

-- ----------------------------
-- Table structure for hr_contracts
-- ----------------------------
DROP TABLE IF EXISTS `hr_contracts`;
CREATE TABLE `hr_contracts`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
  `tenant_id` bigint UNSIGNED NOT NULL DEFAULT 0 COMMENT '租户主键',
  `employee_id` bigint UNSIGNED NOT NULL DEFAULT 0 COMMENT '员工主键',
  `contract_no` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '合同编号',
  `type` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'fixed' COMMENT '类型：fixed固定期限 open无固定期限 intern实习',
  `start_on` date NOT NULL COMMENT '开始日期',
  `end_on` date NULL DEFAULT NULL COMMENT '结束日期，无固定期限时为空',
  `status` tinyint UNSIGNED NOT NULL DEFAULT 1 COMMENT '状态：1履行中 2已终止',
  `signed_on` date NOT NULL COMMENT '签订日期',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT '删除时间，未删除时为空',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `uk_hr_contracts_no`(`tenant_id` ASC, `contract_no` ASC) USING BTREE,
  INDEX `idx_hr_contracts_employee`(`tenant_id` ASC, `employee_id` ASC) USING BTREE,
  INDEX `fk_hr_contracts_employee`(`employee_id` ASC) USING BTREE,
  CONSTRAINT `fk_hr_contracts_employee` FOREIGN KEY (`employee_id`) REFERENCES `hr_employees` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `fk_hr_contracts_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 70001 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '劳动合同' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of hr_contracts
-- ----------------------------
INSERT INTO `hr_contracts` VALUES (70000, 10001, 6000000, 'HT-001', 'fixed', '2026-03-01', '2027-02-28', 1, '2026-03-01', '2026-11-04 08:39:34', '2026-11-04 08:39:34', NULL);

-- ----------------------------
-- Table structure for hr_employees
-- ----------------------------
DROP TABLE IF EXISTS `hr_employees`;
CREATE TABLE `hr_employees`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
  `tenant_id` bigint UNSIGNED NOT NULL DEFAULT 0 COMMENT '租户主键',
  `employee_no` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '工号',
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '姓名',
  `gender` tinyint UNSIGNED NOT NULL DEFAULT 0 COMMENT '性别：0未填 1男 2女',
  `mobile` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '手机号，可选',
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '邮箱',
  `department_id` bigint UNSIGNED NULL DEFAULT NULL COMMENT '部门编号，尚未分配时为空',
  `position_id` bigint UNSIGNED NULL DEFAULT NULL COMMENT '岗位主键，尚未分配时为空',
  `user_id` bigint UNSIGNED NULL DEFAULT NULL COMMENT '绑定的全局账号，没有账号时为空',
  `status` tinyint UNSIGNED NOT NULL DEFAULT 0 COMMENT '状态：0待入职 1在职 2离职',
  `hired_on` date NULL DEFAULT NULL COMMENT '入职日期，尚未入职时为空',
  `left_on` date NULL DEFAULT NULL COMMENT '离职日期，尚未离职时为空',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT '删除时间，未删除时为空',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `uk_hr_employees_no`(`tenant_id` ASC, `employee_no` ASC) USING BTREE,
  INDEX `idx_hr_employees_department`(`tenant_id` ASC, `department_id` ASC) USING BTREE,
  INDEX `idx_hr_employees_status`(`tenant_id` ASC, `status` ASC) USING BTREE,
  INDEX `idx_hr_employees_user`(`user_id` ASC) USING BTREE,
  INDEX `fk_hr_employees_position`(`position_id` ASC) USING BTREE,
  CONSTRAINT `fk_hr_employees_position` FOREIGN KEY (`position_id`) REFERENCES `hr_positions` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `fk_hr_employees_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 6000002 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '员工档案' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of hr_employees
-- ----------------------------
INSERT INTO `hr_employees` VALUES (6000000, 10001, 'E001', '张三', 0, NULL, '', 1000001, 5000000, NULL, 2, '2026-03-01', '2026-06-30', '2026-11-04 08:39:33', '2026-11-04 08:39:38', NULL);
INSERT INTO `hr_employees` VALUES (6000001, 10002, '100001', '李四', 0, '13023666555', 'lisi@163.com', 1000002, 5000001, NULL, 1, '2026-11-04', NULL, '2026-11-04 08:57:40', '2026-11-04 08:57:58', NULL);

-- ----------------------------
-- Table structure for hr_leave_requests
-- ----------------------------
DROP TABLE IF EXISTS `hr_leave_requests`;
CREATE TABLE `hr_leave_requests`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
  `tenant_id` bigint UNSIGNED NOT NULL DEFAULT 0 COMMENT '租户主键',
  `employee_id` bigint UNSIGNED NOT NULL DEFAULT 0 COMMENT '员工主键',
  `leave_type` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'personal' COMMENT '类型：annual年假 sick病假 personal事假 other其他',
  `start_on` date NOT NULL COMMENT '开始日期',
  `end_on` date NOT NULL COMMENT '结束日期',
  `reason` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '事由',
  `status` tinyint UNSIGNED NOT NULL DEFAULT 0 COMMENT '状态：0待审批 1已批准 2已驳回',
  `reviewer_user_id` bigint UNSIGNED NOT NULL DEFAULT 0 COMMENT '审批人用户主键，未审批为 0',
  `reviewed_at` timestamp NULL DEFAULT NULL COMMENT '审批时间，未审批时为空',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT '删除时间，未删除时为空',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_hr_leaves_employee`(`tenant_id` ASC, `employee_id` ASC, `status` ASC) USING BTREE,
  INDEX `fk_hr_leaves_employee`(`employee_id` ASC) USING BTREE,
  CONSTRAINT `fk_hr_leaves_employee` FOREIGN KEY (`employee_id`) REFERENCES `hr_employees` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `fk_hr_leaves_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 1020001 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '请假申请' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of hr_leave_requests
-- ----------------------------
INSERT INTO `hr_leave_requests` VALUES (1020000, 10001, 6000000, 'annual', '2026-03-10', '2026-03-11', '年假', 1, 4000001, '2026-11-04 08:39:36', '2026-11-04 08:39:36', '2026-11-04 08:39:36', NULL);

-- ----------------------------
-- Table structure for hr_positions
-- ----------------------------
DROP TABLE IF EXISTS `hr_positions`;
CREATE TABLE `hr_positions`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
  `tenant_id` bigint UNSIGNED NOT NULL DEFAULT 0 COMMENT '租户主键',
  `department_id` bigint UNSIGNED NULL DEFAULT NULL COMMENT '所属部门编号，不挂部门时为空',
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '岗位名称',
  `code` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '岗位编码',
  `sort` int NOT NULL DEFAULT 0 COMMENT '排序',
  `status` tinyint UNSIGNED NOT NULL DEFAULT 1 COMMENT '状态：1启用 0停用',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT '删除时间，未删除时为空',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `uk_hr_positions_code`(`tenant_id` ASC, `code` ASC) USING BTREE,
  INDEX `idx_hr_positions_department`(`tenant_id` ASC, `department_id` ASC) USING BTREE,
  CONSTRAINT `fk_hr_positions_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 5000002 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '岗位' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of hr_positions
-- ----------------------------
INSERT INTO `hr_positions` VALUES (5000000, 10001, 1000001, '工程师', 'eng', 0, 1, '2026-11-04 08:39:33', '2026-11-04 08:39:33', NULL);
INSERT INTO `hr_positions` VALUES (5000001, 10002, 1000002, '前端工程师', 'frontend', 0, 1, '2026-11-04 08:53:10', '2026-11-04 08:53:35', NULL);

-- ----------------------------
-- Table structure for hr_resignations
-- ----------------------------
DROP TABLE IF EXISTS `hr_resignations`;
CREATE TABLE `hr_resignations`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
  `tenant_id` bigint UNSIGNED NOT NULL DEFAULT 0 COMMENT '租户主键',
  `employee_id` bigint UNSIGNED NOT NULL DEFAULT 0 COMMENT '员工主键',
  `reason` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '离职原因',
  `apply_on` date NOT NULL COMMENT '申请日期',
  `last_on` date NOT NULL COMMENT '最后工作日',
  `status` tinyint UNSIGNED NOT NULL DEFAULT 0 COMMENT '状态：0待审批 1已批准 2已驳回 3已办结',
  `reviewer_user_id` bigint UNSIGNED NOT NULL DEFAULT 0 COMMENT '审批人用户主键，未审批为 0',
  `reviewed_at` timestamp NULL DEFAULT NULL COMMENT '审批时间，未审批时为空',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT '删除时间，未删除时为空',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_hr_resignations_employee`(`tenant_id` ASC, `employee_id` ASC, `status` ASC) USING BTREE,
  INDEX `fk_hr_resignations_employee`(`employee_id` ASC) USING BTREE,
  CONSTRAINT `fk_hr_resignations_employee` FOREIGN KEY (`employee_id`) REFERENCES `hr_employees` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `fk_hr_resignations_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 1030001 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '离职申请' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of hr_resignations
-- ----------------------------
INSERT INTO `hr_resignations` VALUES (1030000, 10001, 6000000, '个人发展', '2026-06-01', '2026-06-30', 3, 4000001, '2026-11-04 08:39:38', '2026-11-04 08:39:37', '2026-11-04 08:39:38', NULL);

-- ----------------------------
-- Table structure for job_batches
-- ----------------------------
DROP TABLE IF EXISTS `job_batches`;
CREATE TABLE `job_batches`  (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `cancelled_at` int NULL DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of job_batches
-- ----------------------------

-- ----------------------------
-- Table structure for jobs
-- ----------------------------
DROP TABLE IF EXISTS `jobs`;
CREATE TABLE `jobs`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint UNSIGNED NOT NULL,
  `reserved_at` int UNSIGNED NULL DEFAULT NULL,
  `available_at` int UNSIGNED NOT NULL,
  `created_at` int UNSIGNED NOT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `jobs_queue_index`(`queue` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of jobs
-- ----------------------------

-- ----------------------------
-- Table structure for migrations
-- ----------------------------
DROP TABLE IF EXISTS `migrations`;
CREATE TABLE `migrations`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 19 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of migrations
-- ----------------------------
INSERT INTO `migrations` VALUES (1, '0001_01_01_000001_create_cache_table', 1);
INSERT INTO `migrations` VALUES (2, '0001_01_01_000002_create_jobs_table', 1);
INSERT INTO `migrations` VALUES (3, '2026_01_01_000001_create_users_table', 1);
INSERT INTO `migrations` VALUES (4, '2026_01_01_000002_create_tenants_table', 1);
INSERT INTO `migrations` VALUES (5, '2026_01_01_000003_create_modules_table', 1);
INSERT INTO `migrations` VALUES (6, '2026_01_01_000004_create_departments_table', 1);
INSERT INTO `migrations` VALUES (7, '2026_01_01_000005_create_tenant_users_table', 1);
INSERT INTO `migrations` VALUES (8, '2026_01_01_000006_create_tenant_modules_table', 1);
INSERT INTO `migrations` VALUES (9, '2026_01_01_000007_create_permissions_table', 1);
INSERT INTO `migrations` VALUES (10, '2026_01_01_000008_create_roles_table', 1);
INSERT INTO `migrations` VALUES (11, '2026_01_01_000009_create_role_permissions_table', 1);
INSERT INTO `migrations` VALUES (12, '2026_01_01_000010_create_role_departments_table', 1);
INSERT INTO `migrations` VALUES (13, '2026_01_01_000011_create_tenant_user_roles_table', 1);
INSERT INTO `migrations` VALUES (14, '2026_01_01_000012_create_personal_access_tokens_table', 1);
INSERT INTO `migrations` VALUES (15, '2026_01_01_000013_sync_management_permissions', 2);
INSERT INTO `migrations` VALUES (16, '2026_01_01_000014_create_plans', 3);
INSERT INTO `migrations` VALUES (17, '2026_01_01_000015_sync_plan_and_member_permissions', 3);
INSERT INTO `migrations` VALUES (18, '2026_01_01_000016_create_hr_tables', 4);

-- ----------------------------
-- Table structure for modules
-- ----------------------------
DROP TABLE IF EXISTS `modules`;
CREATE TABLE `modules`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
  `code` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '模块编码',
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '模块名称',
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '模块说明',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT '删除时间，未删除时为空',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `uk_modules_code`(`code` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 2000002 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '全局模块目录' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of modules
-- ----------------------------
INSERT INTO `modules` VALUES (2000000, 'iam', '身份与访问', '成员、部门、角色、权限与模块开通', '2026-11-04 04:53:56', '2026-11-04 07:09:42', NULL);
INSERT INTO `modules` VALUES (2000001, 'hr', '人事', '组织、员工档案、入离职、合同、考勤与请假', '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);

-- ----------------------------
-- Table structure for permissions
-- ----------------------------
DROP TABLE IF EXISTS `permissions`;
CREATE TABLE `permissions`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
  `parent_id` bigint UNSIGNED NULL DEFAULT NULL COMMENT '上级权限，顶级为空',
  `type` enum('menu','button','api') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'api' COMMENT '权限类型：menu菜单 button按钮 api接口',
  `code` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '权限编码',
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '权限名称',
  `route` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '前端路由或接口路径',
  `method` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'HTTP 方法，菜单和按钮为空',
  `icon` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '图标',
  `sort` int NOT NULL DEFAULT 0 COMMENT '排序',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT '删除时间，未删除时为空',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `uk_permissions_code`(`code` ASC) USING BTREE,
  INDEX `idx_permissions_parent`(`parent_id` ASC) USING BTREE,
  INDEX `idx_permissions_type`(`type` ASC) USING BTREE,
  CONSTRAINT `fk_permissions_parent` FOREIGN KEY (`parent_id`) REFERENCES `permissions` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 67 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '全局权限目录，含菜单、按钮与接口' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of permissions
-- ----------------------------
INSERT INTO `permissions` VALUES (1, NULL, 'menu', 'iam', '组织权限', '/iam', '', 'setting', 10, '2026-11-04 04:53:58', '2026-11-04 04:53:58', NULL);
INSERT INTO `permissions` VALUES (2, 1, 'menu', 'iam.departments.menu', '部门', '/iam/departments', '', 'office-building', 10, '2026-11-04 04:53:58', '2026-11-04 04:53:58', NULL);
INSERT INTO `permissions` VALUES (3, 1, 'menu', 'iam.roles.menu', '角色', '/iam/roles', '', 'user', 20, '2026-11-04 04:53:58', '2026-11-04 04:53:58', NULL);
INSERT INTO `permissions` VALUES (4, 1, 'menu', 'iam.members.menu', '成员', '/iam/members', '', 'avatar', 30, '2026-11-04 04:53:58', '2026-11-04 04:53:58', NULL);
INSERT INTO `permissions` VALUES (5, 1, 'menu', 'iam.modules.menu', '模块', '/iam/modules', '', 'grid', 40, '2026-11-04 04:53:58', '2026-11-04 04:53:58', NULL);
INSERT INTO `permissions` VALUES (6, 2, 'button', 'iam.departments.create', '新建部门', '', '', '', 10, '2026-11-04 04:53:58', '2026-11-04 04:53:58', NULL);
INSERT INTO `permissions` VALUES (7, 3, 'button', 'iam.roles.create', '新建角色', '', '', '', 10, '2026-11-04 04:53:58', '2026-11-04 04:53:58', NULL);
INSERT INTO `permissions` VALUES (8, 4, 'button', 'iam.members.assign', '分配角色', '', '', '', 10, '2026-11-04 04:53:58', '2026-11-04 04:53:58', NULL);
INSERT INTO `permissions` VALUES (9, 1, 'api', 'iam.menus.index', '菜单列表', '/api/v1/iam/menus', 'GET', '', 10, '2026-11-04 04:53:58', '2026-11-04 04:53:58', NULL);
INSERT INTO `permissions` VALUES (10, 1, 'api', 'iam.me.show', '当前成员', '/api/v1/iam/me', 'GET', '', 20, '2026-11-04 04:53:58', '2026-11-04 04:53:58', NULL);
INSERT INTO `permissions` VALUES (11, 2, 'api', 'iam.departments.index', '部门列表', '/api/v1/iam/departments', 'GET', '', 10, '2026-11-04 04:53:58', '2026-11-04 04:53:58', NULL);
INSERT INTO `permissions` VALUES (12, 2, 'api', 'iam.departments.store', '创建部门', '/api/v1/iam/departments', 'POST', '', 20, '2026-11-04 04:53:58', '2026-11-04 04:53:58', NULL);
INSERT INTO `permissions` VALUES (13, 2, 'api', 'iam.departments.show', '部门详情', '/api/v1/iam/departments/{department}', 'GET', '', 30, '2026-11-04 04:53:58', '2026-11-04 04:53:58', NULL);
INSERT INTO `permissions` VALUES (14, 2, 'api', 'iam.departments.update', '更新部门', '/api/v1/iam/departments/{department}', 'PUT', '', 40, '2026-11-04 04:53:58', '2026-11-04 04:53:58', NULL);
INSERT INTO `permissions` VALUES (15, 2, 'api', 'iam.departments.destroy', '删除部门', '/api/v1/iam/departments/{department}', 'DELETE', '', 50, '2026-11-04 04:53:58', '2026-11-04 04:53:58', NULL);
INSERT INTO `permissions` VALUES (16, 3, 'api', 'iam.roles.index', '角色列表', '/api/v1/iam/roles', 'GET', '', 10, '2026-11-04 04:53:58', '2026-11-04 04:53:58', NULL);
INSERT INTO `permissions` VALUES (17, 3, 'api', 'iam.roles.store', '创建角色', '/api/v1/iam/roles', 'POST', '', 20, '2026-11-04 04:53:58', '2026-11-04 04:53:58', NULL);
INSERT INTO `permissions` VALUES (18, 3, 'api', 'iam.roles.show', '角色详情', '/api/v1/iam/roles/{role}', 'GET', '', 30, '2026-11-04 04:53:58', '2026-11-04 04:53:58', NULL);
INSERT INTO `permissions` VALUES (19, 3, 'api', 'iam.roles.update', '更新角色', '/api/v1/iam/roles/{role}', 'PUT', '', 40, '2026-11-04 04:53:58', '2026-11-04 04:53:58', NULL);
INSERT INTO `permissions` VALUES (20, 3, 'api', 'iam.roles.destroy', '删除角色', '/api/v1/iam/roles/{role}', 'DELETE', '', 50, '2026-11-04 04:53:58', '2026-11-04 04:53:58', NULL);
INSERT INTO `permissions` VALUES (21, 4, 'api', 'iam.members.index', '成员列表', '/api/v1/iam/members', 'GET', '', 10, '2026-11-04 04:53:58', '2026-11-04 04:53:58', NULL);
INSERT INTO `permissions` VALUES (22, 4, 'api', 'iam.members.roles.sync', '分配成员角色', '/api/v1/iam/members/{member}/roles', 'PUT', '', 20, '2026-11-04 04:53:58', '2026-11-04 04:53:58', NULL);
INSERT INTO `permissions` VALUES (23, 5, 'api', 'iam.modules.index', '模块开通列表', '/api/v1/iam/modules', 'GET', '', 10, '2026-11-04 04:53:58', '2026-11-04 04:53:58', NULL);
INSERT INTO `permissions` VALUES (24, 1, 'menu', 'iam.permissions.menu', '权限', '/iam/permissions', '', 'key', 50, '2026-11-04 06:46:06', '2026-11-04 06:46:06', NULL);
INSERT INTO `permissions` VALUES (25, 4, 'api', 'iam.members.update', '调整成员部门', '/api/v1/iam/members/{member}', 'PUT', '', 30, '2026-11-04 06:46:06', '2026-11-04 06:46:06', NULL);
INSERT INTO `permissions` VALUES (26, 5, 'api', 'iam.modules.update', '更新模块开通', '/api/v1/iam/modules/{moduleCode}', 'PUT', '', 20, '2026-11-04 06:46:06', '2026-11-04 06:46:06', NULL);
INSERT INTO `permissions` VALUES (27, 24, 'api', 'iam.permissions.index', '权限目录', '/api/v1/iam/permissions', 'GET', '', 10, '2026-11-04 06:46:06', '2026-11-04 06:46:06', NULL);
INSERT INTO `permissions` VALUES (28, 1, 'menu', 'iam.plans.menu', '套餐', '/iam/plan', '', 'ticket', 60, '2026-11-04 07:50:39', '2026-11-04 07:50:39', NULL);
INSERT INTO `permissions` VALUES (29, 4, 'button', 'iam.members.create', '添加成员', '', '', '', 20, '2026-11-04 07:50:39', '2026-11-04 07:50:39', NULL);
INSERT INTO `permissions` VALUES (30, 4, 'api', 'iam.members.store', '添加成员', '/api/v1/iam/members', 'POST', '', 40, '2026-11-04 07:50:39', '2026-11-04 07:50:39', NULL);
INSERT INTO `permissions` VALUES (31, 4, 'api', 'iam.members.destroy', '移除成员', '/api/v1/iam/members/{member}', 'DELETE', '', 50, '2026-11-04 07:50:39', '2026-11-04 07:50:39', NULL);
INSERT INTO `permissions` VALUES (32, 28, 'api', 'iam.plans.show', '当前套餐', '/api/v1/iam/plan', 'GET', '', 10, '2026-11-04 07:50:39', '2026-11-04 07:50:39', NULL);
INSERT INTO `permissions` VALUES (33, 28, 'api', 'iam.plans.update', '更换套餐', '/api/v1/iam/plan', 'PUT', '', 20, '2026-11-04 07:50:39', '2026-11-04 07:50:39', NULL);
INSERT INTO `permissions` VALUES (34, NULL, 'menu', 'hr', '人事', '/hr', '', 'user', 20, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `permissions` VALUES (35, 34, 'menu', 'hr.organization.menu', '组织架构', '/hr/organization', '', '', 10, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `permissions` VALUES (36, 34, 'menu', 'hr.positions.menu', '岗位', '/hr/positions', '', '', 20, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `permissions` VALUES (37, 34, 'menu', 'hr.employees.menu', '员工档案', '/hr/employees', '', '', 30, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `permissions` VALUES (38, 34, 'menu', 'hr.onboarding.menu', '入职', '/hr/onboarding', '', '', 40, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `permissions` VALUES (39, 34, 'menu', 'hr.resignations.menu', '离职', '/hr/resignations', '', '', 50, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `permissions` VALUES (40, 34, 'menu', 'hr.contracts.menu', '合同', '/hr/contracts', '', '', 60, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `permissions` VALUES (41, 34, 'menu', 'hr.attendance.menu', '考勤', '/hr/attendance', '', '', 70, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `permissions` VALUES (42, 34, 'menu', 'hr.leaves.menu', '请假', '/hr/leaves', '', '', 80, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `permissions` VALUES (43, 35, 'api', 'hr.organization.index', '组织架构', '/api/v1/hr/organization', 'GET', '', 10, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `permissions` VALUES (44, 36, 'api', 'hr.positions.index', '岗位列表', '/api/v1/hr/positions', 'GET', '', 10, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `permissions` VALUES (45, 36, 'api', 'hr.positions.store', '创建岗位', '/api/v1/hr/positions', 'POST', '', 20, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `permissions` VALUES (46, 36, 'api', 'hr.positions.update', '更新岗位', '/api/v1/hr/positions/{position}', 'PUT', '', 30, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `permissions` VALUES (47, 36, 'api', 'hr.positions.destroy', '删除岗位', '/api/v1/hr/positions/{position}', 'DELETE', '', 40, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `permissions` VALUES (48, 37, 'api', 'hr.employees.index', '员工列表', '/api/v1/hr/employees', 'GET', '', 10, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `permissions` VALUES (49, 37, 'api', 'hr.employees.store', '建立员工档案', '/api/v1/hr/employees', 'POST', '', 20, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `permissions` VALUES (50, 37, 'api', 'hr.employees.show', '员工详情', '/api/v1/hr/employees/{employee}', 'GET', '', 30, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `permissions` VALUES (51, 37, 'api', 'hr.employees.update', '更新员工档案', '/api/v1/hr/employees/{employee}', 'PUT', '', 40, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `permissions` VALUES (52, 38, 'api', 'hr.employees.onboard', '办理入职', '/api/v1/hr/employees/{employee}/onboard', 'POST', '', 10, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `permissions` VALUES (53, 39, 'api', 'hr.resignations.index', '离职列表', '/api/v1/hr/resignations', 'GET', '', 10, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `permissions` VALUES (54, 39, 'api', 'hr.resignations.store', '发起离职', '/api/v1/hr/resignations', 'POST', '', 20, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `permissions` VALUES (55, 39, 'api', 'hr.resignations.approve', '批准离职', '/api/v1/hr/resignations/{resignation}/approve', 'POST', '', 30, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `permissions` VALUES (56, 39, 'api', 'hr.resignations.reject', '驳回离职', '/api/v1/hr/resignations/{resignation}/reject', 'POST', '', 40, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `permissions` VALUES (57, 39, 'api', 'hr.resignations.complete', '办结离职', '/api/v1/hr/resignations/{resignation}/complete', 'POST', '', 50, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `permissions` VALUES (58, 40, 'api', 'hr.contracts.index', '合同列表', '/api/v1/hr/contracts', 'GET', '', 10, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `permissions` VALUES (59, 40, 'api', 'hr.contracts.store', '签订合同', '/api/v1/hr/contracts', 'POST', '', 20, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `permissions` VALUES (60, 40, 'api', 'hr.contracts.terminate', '终止合同', '/api/v1/hr/contracts/{contract}/terminate', 'POST', '', 30, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `permissions` VALUES (61, 41, 'api', 'hr.attendance.index', '考勤列表', '/api/v1/hr/attendance', 'GET', '', 10, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `permissions` VALUES (62, 41, 'api', 'hr.attendance.store', '登记考勤', '/api/v1/hr/attendance', 'POST', '', 20, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `permissions` VALUES (63, 42, 'api', 'hr.leaves.index', '请假列表', '/api/v1/hr/leaves', 'GET', '', 10, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `permissions` VALUES (64, 42, 'api', 'hr.leaves.store', '提交请假', '/api/v1/hr/leaves', 'POST', '', 20, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `permissions` VALUES (65, 42, 'api', 'hr.leaves.approve', '批准请假', '/api/v1/hr/leaves/{leave}/approve', 'POST', '', 30, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `permissions` VALUES (66, 42, 'api', 'hr.leaves.reject', '驳回请假', '/api/v1/hr/leaves/{leave}/reject', 'POST', '', 40, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);

-- ----------------------------
-- Table structure for personal_access_tokens
-- ----------------------------
DROP TABLE IF EXISTS `personal_access_tokens`;
CREATE TABLE `personal_access_tokens`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
  `tokenable_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '令牌主体类型',
  `tokenable_id` bigint UNSIGNED NOT NULL DEFAULT 0 COMMENT '令牌主体主键',
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '令牌名称',
  `token` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '令牌哈希',
  `abilities` json NOT NULL COMMENT '令牌能力',
  `last_used_at` timestamp NULL DEFAULT NULL COMMENT '最近使用时间，未使用时为空',
  `expires_at` timestamp NULL DEFAULT NULL COMMENT '过期时间，不设过期时为空',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `uk_personal_access_tokens_token`(`token` ASC) USING BTREE,
  INDEX `idx_pat_tokenable`(`tokenable_type` ASC, `tokenable_id` ASC) USING BTREE,
  INDEX `idx_personal_access_tokens_expires_at`(`expires_at` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = 'Sanctum 个人访问令牌，主体为全局用户' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of personal_access_tokens
-- ----------------------------

-- ----------------------------
-- Table structure for plan_modules
-- ----------------------------
DROP TABLE IF EXISTS `plan_modules`;
CREATE TABLE `plan_modules`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
  `plan_id` bigint UNSIGNED NOT NULL DEFAULT 0 COMMENT '套餐主键',
  `module_code` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '模块编码',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT '删除时间，未删除时为空',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `uk_plan_modules`(`plan_id` ASC, `module_code` ASC) USING BTREE,
  INDEX `fk_plan_modules_module`(`module_code` ASC) USING BTREE,
  CONSTRAINT `fk_plan_modules_module` FOREIGN KEY (`module_code`) REFERENCES `modules` (`code`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `fk_plan_modules_plan` FOREIGN KEY (`plan_id`) REFERENCES `plans` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 4 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '套餐包含的模块' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of plan_modules
-- ----------------------------
INSERT INTO `plan_modules` VALUES (1, 1, 'iam', '2026-11-04 07:50:39', '2026-11-04 07:50:39', NULL);
INSERT INTO `plan_modules` VALUES (2, 2, 'iam', '2026-11-04 07:50:39', '2026-11-04 07:50:39', NULL);
INSERT INTO `plan_modules` VALUES (3, 2, 'hr', '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);

-- ----------------------------
-- Table structure for plans
-- ----------------------------
DROP TABLE IF EXISTS `plans`;
CREATE TABLE `plans`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
  `code` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '套餐编码',
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '套餐名称',
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '套餐说明',
  `member_limit` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '有效成员上限，0 表示不限制',
  `status` tinyint UNSIGNED NOT NULL DEFAULT 1 COMMENT '状态：1可售 0停售',
  `sort` int NOT NULL DEFAULT 0 COMMENT '排序',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT '删除时间，未删除时为空',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `uk_plans_code`(`code` ASC) USING BTREE,
  INDEX `idx_plans_status`(`status` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 3 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '全局套餐目录，不隶属单一租户' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of plans
-- ----------------------------
INSERT INTO `plans` VALUES (1, 'basic', '基础版', '适合开始使用的企业。包含身份与访问，有效成员上限 20 人。', 20, 1, 10, '2026-11-04 07:50:39', '2026-11-04 07:50:39', NULL);
INSERT INTO `plans` VALUES (2, 'standard', '标准版', '适合持续协作的企业。包含身份与访问和人事，有效成员上限 200 人。', 200, 1, 20, '2026-11-04 07:50:39', '2026-11-04 08:24:32', NULL);

-- ----------------------------
-- Table structure for role_departments
-- ----------------------------
DROP TABLE IF EXISTS `role_departments`;
CREATE TABLE `role_departments`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
  `tenant_id` bigint UNSIGNED NOT NULL DEFAULT 0 COMMENT '租户主键',
  `role_id` bigint UNSIGNED NOT NULL DEFAULT 0 COMMENT '角色主键',
  `department_id` bigint UNSIGNED NOT NULL DEFAULT 0 COMMENT '部门主键',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT '删除时间，未删除时为空',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `uk_role_departments`(`role_id` ASC, `department_id` ASC) USING BTREE,
  INDEX `idx_role_departments_department`(`department_id` ASC) USING BTREE,
  INDEX `fk_role_departments_tenant`(`tenant_id` ASC) USING BTREE,
  CONSTRAINT `fk_role_departments_department` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `fk_role_departments_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `fk_role_departments_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '自定义数据范围的角色部门' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of role_departments
-- ----------------------------

-- ----------------------------
-- Table structure for role_permissions
-- ----------------------------
DROP TABLE IF EXISTS `role_permissions`;
CREATE TABLE `role_permissions`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
  `tenant_id` bigint UNSIGNED NOT NULL DEFAULT 0 COMMENT '租户主键',
  `role_id` bigint UNSIGNED NOT NULL DEFAULT 0 COMMENT '角色主键',
  `permission_id` bigint UNSIGNED NOT NULL DEFAULT 0 COMMENT '权限主键',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT '删除时间，未删除时为空',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `uk_role_permissions`(`role_id` ASC, `permission_id` ASC) USING BTREE,
  INDEX `idx_role_permissions_permission`(`permission_id` ASC) USING BTREE,
  INDEX `fk_role_permissions_tenant`(`tenant_id` ASC) USING BTREE,
  CONSTRAINT `fk_role_permissions_permission` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `fk_role_permissions_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `fk_role_permissions_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 397 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '角色与权限关联' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of role_permissions
-- ----------------------------
INSERT INTO `role_permissions` VALUES (1, 1, 1, 1, '2026-11-04 04:57:58', '2026-11-04 04:57:58', NULL);
INSERT INTO `role_permissions` VALUES (2, 1, 1, 2, '2026-11-04 04:57:58', '2026-11-04 04:57:58', NULL);
INSERT INTO `role_permissions` VALUES (3, 1, 1, 3, '2026-11-04 04:57:58', '2026-11-04 04:57:58', NULL);
INSERT INTO `role_permissions` VALUES (4, 1, 1, 4, '2026-11-04 04:57:58', '2026-11-04 04:57:58', NULL);
INSERT INTO `role_permissions` VALUES (5, 1, 1, 5, '2026-11-04 04:57:58', '2026-11-04 04:57:58', NULL);
INSERT INTO `role_permissions` VALUES (6, 1, 1, 6, '2026-11-04 04:57:58', '2026-11-04 04:57:58', NULL);
INSERT INTO `role_permissions` VALUES (7, 1, 1, 7, '2026-11-04 04:57:58', '2026-11-04 04:57:58', NULL);
INSERT INTO `role_permissions` VALUES (8, 1, 1, 8, '2026-11-04 04:57:58', '2026-11-04 04:57:58', NULL);
INSERT INTO `role_permissions` VALUES (9, 1, 1, 9, '2026-11-04 04:57:58', '2026-11-04 04:57:58', NULL);
INSERT INTO `role_permissions` VALUES (10, 1, 1, 10, '2026-11-04 04:57:59', '2026-11-04 04:57:59', NULL);
INSERT INTO `role_permissions` VALUES (11, 1, 1, 11, '2026-11-04 04:57:59', '2026-11-04 04:57:59', NULL);
INSERT INTO `role_permissions` VALUES (12, 1, 1, 12, '2026-11-04 04:57:59', '2026-11-04 04:57:59', NULL);
INSERT INTO `role_permissions` VALUES (13, 1, 1, 13, '2026-11-04 04:57:59', '2026-11-04 04:57:59', NULL);
INSERT INTO `role_permissions` VALUES (14, 1, 1, 14, '2026-11-04 04:57:59', '2026-11-04 04:57:59', NULL);
INSERT INTO `role_permissions` VALUES (15, 1, 1, 15, '2026-11-04 04:57:59', '2026-11-04 04:57:59', NULL);
INSERT INTO `role_permissions` VALUES (16, 1, 1, 16, '2026-11-04 04:57:59', '2026-11-04 04:57:59', NULL);
INSERT INTO `role_permissions` VALUES (17, 1, 1, 17, '2026-11-04 04:57:59', '2026-11-04 04:57:59', NULL);
INSERT INTO `role_permissions` VALUES (18, 1, 1, 18, '2026-11-04 04:57:59', '2026-11-04 04:57:59', NULL);
INSERT INTO `role_permissions` VALUES (19, 1, 1, 19, '2026-11-04 04:57:59', '2026-11-04 04:57:59', NULL);
INSERT INTO `role_permissions` VALUES (20, 1, 1, 20, '2026-11-04 04:57:59', '2026-11-04 04:57:59', NULL);
INSERT INTO `role_permissions` VALUES (21, 1, 1, 21, '2026-11-04 04:57:59', '2026-11-04 04:57:59', NULL);
INSERT INTO `role_permissions` VALUES (22, 1, 1, 22, '2026-11-04 04:57:59', '2026-11-04 04:57:59', NULL);
INSERT INTO `role_permissions` VALUES (23, 1, 1, 23, '2026-11-04 04:57:59', '2026-11-04 04:57:59', NULL);
INSERT INTO `role_permissions` VALUES (24, 2, 2, 1, '2026-11-04 06:31:35', '2026-11-04 06:31:35', NULL);
INSERT INTO `role_permissions` VALUES (25, 2, 2, 2, '2026-11-04 06:31:35', '2026-11-04 06:31:35', NULL);
INSERT INTO `role_permissions` VALUES (26, 2, 2, 3, '2026-11-04 06:31:35', '2026-11-04 06:31:35', NULL);
INSERT INTO `role_permissions` VALUES (27, 2, 2, 4, '2026-11-04 06:31:35', '2026-11-04 06:31:35', NULL);
INSERT INTO `role_permissions` VALUES (28, 2, 2, 5, '2026-11-04 06:31:35', '2026-11-04 06:31:35', NULL);
INSERT INTO `role_permissions` VALUES (29, 2, 2, 6, '2026-11-04 06:31:35', '2026-11-04 06:31:35', NULL);
INSERT INTO `role_permissions` VALUES (30, 2, 2, 7, '2026-11-04 06:31:35', '2026-11-04 06:31:35', NULL);
INSERT INTO `role_permissions` VALUES (31, 2, 2, 8, '2026-11-04 06:31:35', '2026-11-04 06:31:35', NULL);
INSERT INTO `role_permissions` VALUES (32, 2, 2, 9, '2026-11-04 06:31:35', '2026-11-04 06:31:35', NULL);
INSERT INTO `role_permissions` VALUES (33, 2, 2, 10, '2026-11-04 06:31:35', '2026-11-04 06:31:35', NULL);
INSERT INTO `role_permissions` VALUES (34, 2, 2, 11, '2026-11-04 06:31:35', '2026-11-04 06:31:35', NULL);
INSERT INTO `role_permissions` VALUES (35, 2, 2, 12, '2026-11-04 06:31:35', '2026-11-04 06:31:35', NULL);
INSERT INTO `role_permissions` VALUES (36, 2, 2, 13, '2026-11-04 06:31:35', '2026-11-04 06:31:35', NULL);
INSERT INTO `role_permissions` VALUES (37, 2, 2, 14, '2026-11-04 06:31:35', '2026-11-04 06:31:35', NULL);
INSERT INTO `role_permissions` VALUES (38, 2, 2, 15, '2026-11-04 06:31:35', '2026-11-04 06:31:35', NULL);
INSERT INTO `role_permissions` VALUES (39, 2, 2, 16, '2026-11-04 06:31:35', '2026-11-04 06:31:35', NULL);
INSERT INTO `role_permissions` VALUES (40, 2, 2, 17, '2026-11-04 06:31:35', '2026-11-04 06:31:35', NULL);
INSERT INTO `role_permissions` VALUES (41, 2, 2, 18, '2026-11-04 06:31:35', '2026-11-04 06:31:35', NULL);
INSERT INTO `role_permissions` VALUES (42, 2, 2, 19, '2026-11-04 06:31:35', '2026-11-04 06:31:35', NULL);
INSERT INTO `role_permissions` VALUES (43, 2, 2, 20, '2026-11-04 06:31:35', '2026-11-04 06:31:35', NULL);
INSERT INTO `role_permissions` VALUES (44, 2, 2, 21, '2026-11-04 06:31:35', '2026-11-04 06:31:35', NULL);
INSERT INTO `role_permissions` VALUES (45, 2, 2, 22, '2026-11-04 06:31:35', '2026-11-04 06:31:35', NULL);
INSERT INTO `role_permissions` VALUES (46, 2, 2, 23, '2026-11-04 06:31:35', '2026-11-04 06:31:35', NULL);
INSERT INTO `role_permissions` VALUES (47, 1, 1, 24, '2026-11-04 06:46:06', '2026-11-04 06:46:06', NULL);
INSERT INTO `role_permissions` VALUES (48, 1, 1, 25, '2026-11-04 06:46:06', '2026-11-04 06:46:06', NULL);
INSERT INTO `role_permissions` VALUES (49, 1, 1, 26, '2026-11-04 06:46:06', '2026-11-04 06:46:06', NULL);
INSERT INTO `role_permissions` VALUES (50, 1, 1, 27, '2026-11-04 06:46:06', '2026-11-04 06:46:06', NULL);
INSERT INTO `role_permissions` VALUES (51, 2, 2, 24, '2026-11-04 06:46:06', '2026-11-04 06:46:06', NULL);
INSERT INTO `role_permissions` VALUES (52, 2, 2, 25, '2026-11-04 06:46:06', '2026-11-04 06:46:06', NULL);
INSERT INTO `role_permissions` VALUES (53, 2, 2, 26, '2026-11-04 06:46:06', '2026-11-04 06:46:06', NULL);
INSERT INTO `role_permissions` VALUES (54, 2, 2, 27, '2026-11-04 06:46:06', '2026-11-04 06:46:06', NULL);
INSERT INTO `role_permissions` VALUES (55, 3, 3, 1, '2026-11-04 06:50:54', '2026-11-04 06:50:54', NULL);
INSERT INTO `role_permissions` VALUES (56, 3, 3, 2, '2026-11-04 06:50:54', '2026-11-04 06:50:54', NULL);
INSERT INTO `role_permissions` VALUES (57, 3, 3, 3, '2026-11-04 06:50:54', '2026-11-04 06:50:54', NULL);
INSERT INTO `role_permissions` VALUES (58, 3, 3, 4, '2026-11-04 06:50:54', '2026-11-04 06:50:54', NULL);
INSERT INTO `role_permissions` VALUES (59, 3, 3, 5, '2026-11-04 06:50:54', '2026-11-04 06:50:54', NULL);
INSERT INTO `role_permissions` VALUES (60, 3, 3, 6, '2026-11-04 06:50:54', '2026-11-04 06:50:54', NULL);
INSERT INTO `role_permissions` VALUES (61, 3, 3, 7, '2026-11-04 06:50:54', '2026-11-04 06:50:54', NULL);
INSERT INTO `role_permissions` VALUES (62, 3, 3, 8, '2026-11-04 06:50:54', '2026-11-04 06:50:54', NULL);
INSERT INTO `role_permissions` VALUES (63, 3, 3, 9, '2026-11-04 06:50:55', '2026-11-04 06:50:55', NULL);
INSERT INTO `role_permissions` VALUES (64, 3, 3, 10, '2026-11-04 06:50:55', '2026-11-04 06:50:55', NULL);
INSERT INTO `role_permissions` VALUES (65, 3, 3, 11, '2026-11-04 06:50:55', '2026-11-04 06:50:55', NULL);
INSERT INTO `role_permissions` VALUES (66, 3, 3, 12, '2026-11-04 06:50:55', '2026-11-04 06:50:55', NULL);
INSERT INTO `role_permissions` VALUES (67, 3, 3, 13, '2026-11-04 06:50:55', '2026-11-04 06:50:55', NULL);
INSERT INTO `role_permissions` VALUES (68, 3, 3, 14, '2026-11-04 06:50:55', '2026-11-04 06:50:55', NULL);
INSERT INTO `role_permissions` VALUES (69, 3, 3, 15, '2026-11-04 06:50:55', '2026-11-04 06:50:55', NULL);
INSERT INTO `role_permissions` VALUES (70, 3, 3, 16, '2026-11-04 06:50:55', '2026-11-04 06:50:55', NULL);
INSERT INTO `role_permissions` VALUES (71, 3, 3, 17, '2026-11-04 06:50:55', '2026-11-04 06:50:55', NULL);
INSERT INTO `role_permissions` VALUES (72, 3, 3, 18, '2026-11-04 06:50:55', '2026-11-04 06:50:55', NULL);
INSERT INTO `role_permissions` VALUES (73, 3, 3, 19, '2026-11-04 06:50:55', '2026-11-04 06:50:55', NULL);
INSERT INTO `role_permissions` VALUES (74, 3, 3, 20, '2026-11-04 06:50:55', '2026-11-04 06:50:55', NULL);
INSERT INTO `role_permissions` VALUES (75, 3, 3, 21, '2026-11-04 06:50:55', '2026-11-04 06:50:55', NULL);
INSERT INTO `role_permissions` VALUES (76, 3, 3, 22, '2026-11-04 06:50:55', '2026-11-04 06:50:55', NULL);
INSERT INTO `role_permissions` VALUES (77, 3, 3, 23, '2026-11-04 06:50:55', '2026-11-04 06:50:55', NULL);
INSERT INTO `role_permissions` VALUES (78, 3, 3, 24, '2026-11-04 06:50:55', '2026-11-04 06:50:55', NULL);
INSERT INTO `role_permissions` VALUES (79, 3, 3, 25, '2026-11-04 06:50:55', '2026-11-04 06:50:55', NULL);
INSERT INTO `role_permissions` VALUES (80, 3, 3, 26, '2026-11-04 06:50:55', '2026-11-04 06:50:55', NULL);
INSERT INTO `role_permissions` VALUES (81, 3, 3, 27, '2026-11-04 06:50:55', '2026-11-04 06:50:55', NULL);
INSERT INTO `role_permissions` VALUES (82, 10000, 3000000, 1, '2026-11-04 07:07:40', '2026-11-04 07:07:40', NULL);
INSERT INTO `role_permissions` VALUES (83, 10000, 3000000, 2, '2026-11-04 07:07:40', '2026-11-04 07:07:40', NULL);
INSERT INTO `role_permissions` VALUES (84, 10000, 3000000, 3, '2026-11-04 07:07:40', '2026-11-04 07:07:40', NULL);
INSERT INTO `role_permissions` VALUES (85, 10000, 3000000, 4, '2026-11-04 07:07:40', '2026-11-04 07:07:40', NULL);
INSERT INTO `role_permissions` VALUES (86, 10000, 3000000, 5, '2026-11-04 07:07:40', '2026-11-04 07:07:40', NULL);
INSERT INTO `role_permissions` VALUES (87, 10000, 3000000, 6, '2026-11-04 07:07:40', '2026-11-04 07:07:40', NULL);
INSERT INTO `role_permissions` VALUES (88, 10000, 3000000, 7, '2026-11-04 07:07:40', '2026-11-04 07:07:40', NULL);
INSERT INTO `role_permissions` VALUES (89, 10000, 3000000, 8, '2026-11-04 07:07:40', '2026-11-04 07:07:40', NULL);
INSERT INTO `role_permissions` VALUES (90, 10000, 3000000, 9, '2026-11-04 07:07:40', '2026-11-04 07:07:40', NULL);
INSERT INTO `role_permissions` VALUES (91, 10000, 3000000, 10, '2026-11-04 07:07:40', '2026-11-04 07:07:40', NULL);
INSERT INTO `role_permissions` VALUES (92, 10000, 3000000, 11, '2026-11-04 07:07:40', '2026-11-04 07:07:40', NULL);
INSERT INTO `role_permissions` VALUES (93, 10000, 3000000, 12, '2026-11-04 07:07:40', '2026-11-04 07:07:40', NULL);
INSERT INTO `role_permissions` VALUES (94, 10000, 3000000, 13, '2026-11-04 07:07:40', '2026-11-04 07:07:40', NULL);
INSERT INTO `role_permissions` VALUES (95, 10000, 3000000, 14, '2026-11-04 07:07:40', '2026-11-04 07:07:40', NULL);
INSERT INTO `role_permissions` VALUES (96, 10000, 3000000, 15, '2026-11-04 07:07:40', '2026-11-04 07:07:40', NULL);
INSERT INTO `role_permissions` VALUES (97, 10000, 3000000, 16, '2026-11-04 07:07:40', '2026-11-04 07:07:40', NULL);
INSERT INTO `role_permissions` VALUES (98, 10000, 3000000, 17, '2026-11-04 07:07:40', '2026-11-04 07:07:40', NULL);
INSERT INTO `role_permissions` VALUES (99, 10000, 3000000, 18, '2026-11-04 07:07:40', '2026-11-04 07:07:40', NULL);
INSERT INTO `role_permissions` VALUES (100, 10000, 3000000, 19, '2026-11-04 07:07:40', '2026-11-04 07:07:40', NULL);
INSERT INTO `role_permissions` VALUES (101, 10000, 3000000, 20, '2026-11-04 07:07:40', '2026-11-04 07:07:40', NULL);
INSERT INTO `role_permissions` VALUES (102, 10000, 3000000, 21, '2026-11-04 07:07:40', '2026-11-04 07:07:40', NULL);
INSERT INTO `role_permissions` VALUES (103, 10000, 3000000, 22, '2026-11-04 07:07:40', '2026-11-04 07:07:40', NULL);
INSERT INTO `role_permissions` VALUES (104, 10000, 3000000, 23, '2026-11-04 07:07:40', '2026-11-04 07:07:40', NULL);
INSERT INTO `role_permissions` VALUES (105, 10000, 3000000, 24, '2026-11-04 07:07:40', '2026-11-04 07:07:40', NULL);
INSERT INTO `role_permissions` VALUES (106, 10000, 3000000, 25, '2026-11-04 07:07:40', '2026-11-04 07:07:40', NULL);
INSERT INTO `role_permissions` VALUES (107, 10000, 3000000, 26, '2026-11-04 07:07:40', '2026-11-04 07:07:40', NULL);
INSERT INTO `role_permissions` VALUES (108, 10000, 3000000, 27, '2026-11-04 07:07:40', '2026-11-04 07:07:40', NULL);
INSERT INTO `role_permissions` VALUES (109, 1, 1, 28, '2026-11-04 07:50:39', '2026-11-04 07:50:39', NULL);
INSERT INTO `role_permissions` VALUES (110, 1, 1, 29, '2026-11-04 07:50:39', '2026-11-04 07:50:39', NULL);
INSERT INTO `role_permissions` VALUES (111, 1, 1, 30, '2026-11-04 07:50:39', '2026-11-04 07:50:39', NULL);
INSERT INTO `role_permissions` VALUES (112, 1, 1, 31, '2026-11-04 07:50:39', '2026-11-04 07:50:39', NULL);
INSERT INTO `role_permissions` VALUES (113, 1, 1, 32, '2026-11-04 07:50:39', '2026-11-04 07:50:39', NULL);
INSERT INTO `role_permissions` VALUES (114, 1, 1, 33, '2026-11-04 07:50:39', '2026-11-04 07:50:39', NULL);
INSERT INTO `role_permissions` VALUES (115, 2, 2, 28, '2026-11-04 07:50:39', '2026-11-04 07:50:39', NULL);
INSERT INTO `role_permissions` VALUES (116, 2, 2, 29, '2026-11-04 07:50:39', '2026-11-04 07:50:39', NULL);
INSERT INTO `role_permissions` VALUES (117, 2, 2, 30, '2026-11-04 07:50:39', '2026-11-04 07:50:39', NULL);
INSERT INTO `role_permissions` VALUES (118, 2, 2, 31, '2026-11-04 07:50:39', '2026-11-04 07:50:39', NULL);
INSERT INTO `role_permissions` VALUES (119, 2, 2, 32, '2026-11-04 07:50:39', '2026-11-04 07:50:39', NULL);
INSERT INTO `role_permissions` VALUES (120, 2, 2, 33, '2026-11-04 07:50:39', '2026-11-04 07:50:39', NULL);
INSERT INTO `role_permissions` VALUES (121, 3, 3, 28, '2026-11-04 07:50:39', '2026-11-04 07:50:39', NULL);
INSERT INTO `role_permissions` VALUES (122, 3, 3, 29, '2026-11-04 07:50:39', '2026-11-04 07:50:39', NULL);
INSERT INTO `role_permissions` VALUES (123, 3, 3, 30, '2026-11-04 07:50:39', '2026-11-04 07:50:39', NULL);
INSERT INTO `role_permissions` VALUES (124, 3, 3, 31, '2026-11-04 07:50:39', '2026-11-04 07:50:39', NULL);
INSERT INTO `role_permissions` VALUES (125, 3, 3, 32, '2026-11-04 07:50:39', '2026-11-04 07:50:39', NULL);
INSERT INTO `role_permissions` VALUES (126, 3, 3, 33, '2026-11-04 07:50:39', '2026-11-04 07:50:39', NULL);
INSERT INTO `role_permissions` VALUES (127, 10000, 3000000, 28, '2026-11-04 07:50:39', '2026-11-04 07:50:39', NULL);
INSERT INTO `role_permissions` VALUES (128, 10000, 3000000, 29, '2026-11-04 07:50:39', '2026-11-04 07:50:39', NULL);
INSERT INTO `role_permissions` VALUES (129, 10000, 3000000, 30, '2026-11-04 07:50:39', '2026-11-04 07:50:39', NULL);
INSERT INTO `role_permissions` VALUES (130, 10000, 3000000, 31, '2026-11-04 07:50:39', '2026-11-04 07:50:39', NULL);
INSERT INTO `role_permissions` VALUES (131, 10000, 3000000, 32, '2026-11-04 07:50:39', '2026-11-04 07:50:39', NULL);
INSERT INTO `role_permissions` VALUES (132, 10000, 3000000, 33, '2026-11-04 07:50:39', '2026-11-04 07:50:39', NULL);
INSERT INTO `role_permissions` VALUES (133, 10001, 3000001, 1, '2026-11-04 08:01:38', '2026-11-04 08:01:38', NULL);
INSERT INTO `role_permissions` VALUES (134, 10001, 3000001, 2, '2026-11-04 08:01:38', '2026-11-04 08:01:38', NULL);
INSERT INTO `role_permissions` VALUES (135, 10001, 3000001, 3, '2026-11-04 08:01:38', '2026-11-04 08:01:38', NULL);
INSERT INTO `role_permissions` VALUES (136, 10001, 3000001, 4, '2026-11-04 08:01:38', '2026-11-04 08:01:38', NULL);
INSERT INTO `role_permissions` VALUES (137, 10001, 3000001, 5, '2026-11-04 08:01:38', '2026-11-04 08:01:38', NULL);
INSERT INTO `role_permissions` VALUES (138, 10001, 3000001, 6, '2026-11-04 08:01:38', '2026-11-04 08:01:38', NULL);
INSERT INTO `role_permissions` VALUES (139, 10001, 3000001, 7, '2026-11-04 08:01:38', '2026-11-04 08:01:38', NULL);
INSERT INTO `role_permissions` VALUES (140, 10001, 3000001, 8, '2026-11-04 08:01:38', '2026-11-04 08:01:38', NULL);
INSERT INTO `role_permissions` VALUES (141, 10001, 3000001, 9, '2026-11-04 08:01:38', '2026-11-04 08:01:38', NULL);
INSERT INTO `role_permissions` VALUES (142, 10001, 3000001, 10, '2026-11-04 08:01:38', '2026-11-04 08:01:38', NULL);
INSERT INTO `role_permissions` VALUES (143, 10001, 3000001, 11, '2026-11-04 08:01:38', '2026-11-04 08:01:38', NULL);
INSERT INTO `role_permissions` VALUES (144, 10001, 3000001, 12, '2026-11-04 08:01:38', '2026-11-04 08:01:38', NULL);
INSERT INTO `role_permissions` VALUES (145, 10001, 3000001, 13, '2026-11-04 08:01:38', '2026-11-04 08:01:38', NULL);
INSERT INTO `role_permissions` VALUES (146, 10001, 3000001, 14, '2026-11-04 08:01:38', '2026-11-04 08:01:38', NULL);
INSERT INTO `role_permissions` VALUES (147, 10001, 3000001, 15, '2026-11-04 08:01:38', '2026-11-04 08:01:38', NULL);
INSERT INTO `role_permissions` VALUES (148, 10001, 3000001, 16, '2026-11-04 08:01:38', '2026-11-04 08:01:38', NULL);
INSERT INTO `role_permissions` VALUES (149, 10001, 3000001, 17, '2026-11-04 08:01:38', '2026-11-04 08:01:38', NULL);
INSERT INTO `role_permissions` VALUES (150, 10001, 3000001, 18, '2026-11-04 08:01:38', '2026-11-04 08:01:38', NULL);
INSERT INTO `role_permissions` VALUES (151, 10001, 3000001, 19, '2026-11-04 08:01:38', '2026-11-04 08:01:38', NULL);
INSERT INTO `role_permissions` VALUES (152, 10001, 3000001, 20, '2026-11-04 08:01:38', '2026-11-04 08:01:38', NULL);
INSERT INTO `role_permissions` VALUES (153, 10001, 3000001, 21, '2026-11-04 08:01:38', '2026-11-04 08:01:38', NULL);
INSERT INTO `role_permissions` VALUES (154, 10001, 3000001, 22, '2026-11-04 08:01:38', '2026-11-04 08:01:38', NULL);
INSERT INTO `role_permissions` VALUES (155, 10001, 3000001, 23, '2026-11-04 08:01:38', '2026-11-04 08:01:38', NULL);
INSERT INTO `role_permissions` VALUES (156, 10001, 3000001, 24, '2026-11-04 08:01:38', '2026-11-04 08:01:38', NULL);
INSERT INTO `role_permissions` VALUES (157, 10001, 3000001, 25, '2026-11-04 08:01:38', '2026-11-04 08:01:38', NULL);
INSERT INTO `role_permissions` VALUES (158, 10001, 3000001, 26, '2026-11-04 08:01:38', '2026-11-04 08:01:38', NULL);
INSERT INTO `role_permissions` VALUES (159, 10001, 3000001, 27, '2026-11-04 08:01:38', '2026-11-04 08:01:38', NULL);
INSERT INTO `role_permissions` VALUES (160, 10001, 3000001, 28, '2026-11-04 08:01:38', '2026-11-04 08:01:38', NULL);
INSERT INTO `role_permissions` VALUES (161, 10001, 3000001, 29, '2026-11-04 08:01:38', '2026-11-04 08:01:38', NULL);
INSERT INTO `role_permissions` VALUES (162, 10001, 3000001, 30, '2026-11-04 08:01:38', '2026-11-04 08:01:38', NULL);
INSERT INTO `role_permissions` VALUES (163, 10001, 3000001, 31, '2026-11-04 08:01:38', '2026-11-04 08:01:38', NULL);
INSERT INTO `role_permissions` VALUES (164, 10001, 3000001, 32, '2026-11-04 08:01:38', '2026-11-04 08:01:38', NULL);
INSERT INTO `role_permissions` VALUES (165, 10001, 3000001, 33, '2026-11-04 08:01:38', '2026-11-04 08:01:38', NULL);
INSERT INTO `role_permissions` VALUES (166, 1, 1, 34, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `role_permissions` VALUES (167, 1, 1, 35, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `role_permissions` VALUES (168, 1, 1, 36, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `role_permissions` VALUES (169, 1, 1, 37, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `role_permissions` VALUES (170, 1, 1, 38, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `role_permissions` VALUES (171, 1, 1, 39, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `role_permissions` VALUES (172, 1, 1, 40, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `role_permissions` VALUES (173, 1, 1, 41, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `role_permissions` VALUES (174, 1, 1, 42, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `role_permissions` VALUES (175, 1, 1, 43, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `role_permissions` VALUES (176, 1, 1, 44, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `role_permissions` VALUES (177, 1, 1, 45, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `role_permissions` VALUES (178, 1, 1, 46, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `role_permissions` VALUES (179, 1, 1, 47, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `role_permissions` VALUES (180, 1, 1, 48, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `role_permissions` VALUES (181, 1, 1, 49, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `role_permissions` VALUES (182, 1, 1, 50, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `role_permissions` VALUES (183, 1, 1, 51, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `role_permissions` VALUES (184, 1, 1, 52, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `role_permissions` VALUES (185, 1, 1, 53, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `role_permissions` VALUES (186, 1, 1, 54, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `role_permissions` VALUES (187, 1, 1, 55, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `role_permissions` VALUES (188, 1, 1, 56, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `role_permissions` VALUES (189, 1, 1, 57, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `role_permissions` VALUES (190, 1, 1, 58, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `role_permissions` VALUES (191, 1, 1, 59, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `role_permissions` VALUES (192, 1, 1, 60, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `role_permissions` VALUES (193, 1, 1, 61, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `role_permissions` VALUES (194, 1, 1, 62, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `role_permissions` VALUES (195, 1, 1, 63, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `role_permissions` VALUES (196, 1, 1, 64, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `role_permissions` VALUES (197, 1, 1, 65, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `role_permissions` VALUES (198, 1, 1, 66, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `role_permissions` VALUES (199, 2, 2, 34, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `role_permissions` VALUES (200, 2, 2, 35, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `role_permissions` VALUES (201, 2, 2, 36, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `role_permissions` VALUES (202, 2, 2, 37, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `role_permissions` VALUES (203, 2, 2, 38, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `role_permissions` VALUES (204, 2, 2, 39, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `role_permissions` VALUES (205, 2, 2, 40, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `role_permissions` VALUES (206, 2, 2, 41, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `role_permissions` VALUES (207, 2, 2, 42, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `role_permissions` VALUES (208, 2, 2, 43, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `role_permissions` VALUES (209, 2, 2, 44, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `role_permissions` VALUES (210, 2, 2, 45, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `role_permissions` VALUES (211, 2, 2, 46, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `role_permissions` VALUES (212, 2, 2, 47, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `role_permissions` VALUES (213, 2, 2, 48, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `role_permissions` VALUES (214, 2, 2, 49, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `role_permissions` VALUES (215, 2, 2, 50, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `role_permissions` VALUES (216, 2, 2, 51, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `role_permissions` VALUES (217, 2, 2, 52, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `role_permissions` VALUES (218, 2, 2, 53, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `role_permissions` VALUES (219, 2, 2, 54, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `role_permissions` VALUES (220, 2, 2, 55, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `role_permissions` VALUES (221, 2, 2, 56, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `role_permissions` VALUES (222, 2, 2, 57, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `role_permissions` VALUES (223, 2, 2, 58, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `role_permissions` VALUES (224, 2, 2, 59, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `role_permissions` VALUES (225, 2, 2, 60, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `role_permissions` VALUES (226, 2, 2, 61, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `role_permissions` VALUES (227, 2, 2, 62, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `role_permissions` VALUES (228, 2, 2, 63, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `role_permissions` VALUES (229, 2, 2, 64, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `role_permissions` VALUES (230, 2, 2, 65, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `role_permissions` VALUES (231, 2, 2, 66, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `role_permissions` VALUES (232, 3, 3, 34, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `role_permissions` VALUES (233, 3, 3, 35, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `role_permissions` VALUES (234, 3, 3, 36, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `role_permissions` VALUES (235, 3, 3, 37, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `role_permissions` VALUES (236, 3, 3, 38, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `role_permissions` VALUES (237, 3, 3, 39, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `role_permissions` VALUES (238, 3, 3, 40, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `role_permissions` VALUES (239, 3, 3, 41, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `role_permissions` VALUES (240, 3, 3, 42, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `role_permissions` VALUES (241, 3, 3, 43, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `role_permissions` VALUES (242, 3, 3, 44, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `role_permissions` VALUES (243, 3, 3, 45, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `role_permissions` VALUES (244, 3, 3, 46, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `role_permissions` VALUES (245, 3, 3, 47, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `role_permissions` VALUES (246, 3, 3, 48, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `role_permissions` VALUES (247, 3, 3, 49, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `role_permissions` VALUES (248, 3, 3, 50, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `role_permissions` VALUES (249, 3, 3, 51, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `role_permissions` VALUES (250, 3, 3, 52, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `role_permissions` VALUES (251, 3, 3, 53, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `role_permissions` VALUES (252, 3, 3, 54, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `role_permissions` VALUES (253, 3, 3, 55, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `role_permissions` VALUES (254, 3, 3, 56, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `role_permissions` VALUES (255, 3, 3, 57, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `role_permissions` VALUES (256, 3, 3, 58, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `role_permissions` VALUES (257, 3, 3, 59, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `role_permissions` VALUES (258, 3, 3, 60, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `role_permissions` VALUES (259, 3, 3, 61, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `role_permissions` VALUES (260, 3, 3, 62, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `role_permissions` VALUES (261, 3, 3, 63, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `role_permissions` VALUES (262, 3, 3, 64, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `role_permissions` VALUES (263, 3, 3, 65, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `role_permissions` VALUES (264, 3, 3, 66, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `role_permissions` VALUES (265, 10000, 3000000, 34, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `role_permissions` VALUES (266, 10000, 3000000, 35, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `role_permissions` VALUES (267, 10000, 3000000, 36, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `role_permissions` VALUES (268, 10000, 3000000, 37, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `role_permissions` VALUES (269, 10000, 3000000, 38, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `role_permissions` VALUES (270, 10000, 3000000, 39, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `role_permissions` VALUES (271, 10000, 3000000, 40, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `role_permissions` VALUES (272, 10000, 3000000, 41, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `role_permissions` VALUES (273, 10000, 3000000, 42, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `role_permissions` VALUES (274, 10000, 3000000, 43, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `role_permissions` VALUES (275, 10000, 3000000, 44, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `role_permissions` VALUES (276, 10000, 3000000, 45, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `role_permissions` VALUES (277, 10000, 3000000, 46, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `role_permissions` VALUES (278, 10000, 3000000, 47, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `role_permissions` VALUES (279, 10000, 3000000, 48, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `role_permissions` VALUES (280, 10000, 3000000, 49, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `role_permissions` VALUES (281, 10000, 3000000, 50, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `role_permissions` VALUES (282, 10000, 3000000, 51, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `role_permissions` VALUES (283, 10000, 3000000, 52, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `role_permissions` VALUES (284, 10000, 3000000, 53, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `role_permissions` VALUES (285, 10000, 3000000, 54, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `role_permissions` VALUES (286, 10000, 3000000, 55, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `role_permissions` VALUES (287, 10000, 3000000, 56, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `role_permissions` VALUES (288, 10000, 3000000, 57, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `role_permissions` VALUES (289, 10000, 3000000, 58, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `role_permissions` VALUES (290, 10000, 3000000, 59, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `role_permissions` VALUES (291, 10000, 3000000, 60, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `role_permissions` VALUES (292, 10000, 3000000, 61, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `role_permissions` VALUES (293, 10000, 3000000, 62, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `role_permissions` VALUES (294, 10000, 3000000, 63, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `role_permissions` VALUES (295, 10000, 3000000, 64, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `role_permissions` VALUES (296, 10000, 3000000, 65, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `role_permissions` VALUES (297, 10000, 3000000, 66, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `role_permissions` VALUES (298, 10001, 3000001, 34, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `role_permissions` VALUES (299, 10001, 3000001, 35, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `role_permissions` VALUES (300, 10001, 3000001, 36, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `role_permissions` VALUES (301, 10001, 3000001, 37, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `role_permissions` VALUES (302, 10001, 3000001, 38, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `role_permissions` VALUES (303, 10001, 3000001, 39, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `role_permissions` VALUES (304, 10001, 3000001, 40, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `role_permissions` VALUES (305, 10001, 3000001, 41, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `role_permissions` VALUES (306, 10001, 3000001, 42, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `role_permissions` VALUES (307, 10001, 3000001, 43, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `role_permissions` VALUES (308, 10001, 3000001, 44, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `role_permissions` VALUES (309, 10001, 3000001, 45, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `role_permissions` VALUES (310, 10001, 3000001, 46, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `role_permissions` VALUES (311, 10001, 3000001, 47, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `role_permissions` VALUES (312, 10001, 3000001, 48, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `role_permissions` VALUES (313, 10001, 3000001, 49, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `role_permissions` VALUES (314, 10001, 3000001, 50, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `role_permissions` VALUES (315, 10001, 3000001, 51, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `role_permissions` VALUES (316, 10001, 3000001, 52, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `role_permissions` VALUES (317, 10001, 3000001, 53, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `role_permissions` VALUES (318, 10001, 3000001, 54, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `role_permissions` VALUES (319, 10001, 3000001, 55, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `role_permissions` VALUES (320, 10001, 3000001, 56, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `role_permissions` VALUES (321, 10001, 3000001, 57, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `role_permissions` VALUES (322, 10001, 3000001, 58, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `role_permissions` VALUES (323, 10001, 3000001, 59, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `role_permissions` VALUES (324, 10001, 3000001, 60, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `role_permissions` VALUES (325, 10001, 3000001, 61, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `role_permissions` VALUES (326, 10001, 3000001, 62, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `role_permissions` VALUES (327, 10001, 3000001, 63, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `role_permissions` VALUES (328, 10001, 3000001, 64, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `role_permissions` VALUES (329, 10001, 3000001, 65, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `role_permissions` VALUES (330, 10001, 3000001, 66, '2026-11-04 08:24:32', '2026-11-04 08:24:32', NULL);
INSERT INTO `role_permissions` VALUES (331, 10002, 3000002, 1, '2026-11-04 08:51:12', '2026-11-04 08:51:12', NULL);
INSERT INTO `role_permissions` VALUES (332, 10002, 3000002, 2, '2026-11-04 08:51:12', '2026-11-04 08:51:12', NULL);
INSERT INTO `role_permissions` VALUES (333, 10002, 3000002, 3, '2026-11-04 08:51:12', '2026-11-04 08:51:12', NULL);
INSERT INTO `role_permissions` VALUES (334, 10002, 3000002, 4, '2026-11-04 08:51:12', '2026-11-04 08:51:12', NULL);
INSERT INTO `role_permissions` VALUES (335, 10002, 3000002, 5, '2026-11-04 08:51:12', '2026-11-04 08:51:12', NULL);
INSERT INTO `role_permissions` VALUES (336, 10002, 3000002, 6, '2026-11-04 08:51:12', '2026-11-04 08:51:12', NULL);
INSERT INTO `role_permissions` VALUES (337, 10002, 3000002, 7, '2026-11-04 08:51:12', '2026-11-04 08:51:12', NULL);
INSERT INTO `role_permissions` VALUES (338, 10002, 3000002, 8, '2026-11-04 08:51:12', '2026-11-04 08:51:12', NULL);
INSERT INTO `role_permissions` VALUES (339, 10002, 3000002, 9, '2026-11-04 08:51:12', '2026-11-04 08:51:12', NULL);
INSERT INTO `role_permissions` VALUES (340, 10002, 3000002, 10, '2026-11-04 08:51:12', '2026-11-04 08:51:12', NULL);
INSERT INTO `role_permissions` VALUES (341, 10002, 3000002, 11, '2026-11-04 08:51:12', '2026-11-04 08:51:12', NULL);
INSERT INTO `role_permissions` VALUES (342, 10002, 3000002, 12, '2026-11-04 08:51:12', '2026-11-04 08:51:12', NULL);
INSERT INTO `role_permissions` VALUES (343, 10002, 3000002, 13, '2026-11-04 08:51:12', '2026-11-04 08:51:12', NULL);
INSERT INTO `role_permissions` VALUES (344, 10002, 3000002, 14, '2026-11-04 08:51:12', '2026-11-04 08:51:12', NULL);
INSERT INTO `role_permissions` VALUES (345, 10002, 3000002, 15, '2026-11-04 08:51:13', '2026-11-04 08:51:13', NULL);
INSERT INTO `role_permissions` VALUES (346, 10002, 3000002, 16, '2026-11-04 08:51:13', '2026-11-04 08:51:13', NULL);
INSERT INTO `role_permissions` VALUES (347, 10002, 3000002, 17, '2026-11-04 08:51:13', '2026-11-04 08:51:13', NULL);
INSERT INTO `role_permissions` VALUES (348, 10002, 3000002, 18, '2026-11-04 08:51:13', '2026-11-04 08:51:13', NULL);
INSERT INTO `role_permissions` VALUES (349, 10002, 3000002, 19, '2026-11-04 08:51:13', '2026-11-04 08:51:13', NULL);
INSERT INTO `role_permissions` VALUES (350, 10002, 3000002, 20, '2026-11-04 08:51:13', '2026-11-04 08:51:13', NULL);
INSERT INTO `role_permissions` VALUES (351, 10002, 3000002, 21, '2026-11-04 08:51:13', '2026-11-04 08:51:13', NULL);
INSERT INTO `role_permissions` VALUES (352, 10002, 3000002, 22, '2026-11-04 08:51:13', '2026-11-04 08:51:13', NULL);
INSERT INTO `role_permissions` VALUES (353, 10002, 3000002, 23, '2026-11-04 08:51:13', '2026-11-04 08:51:13', NULL);
INSERT INTO `role_permissions` VALUES (354, 10002, 3000002, 24, '2026-11-04 08:51:13', '2026-11-04 08:51:13', NULL);
INSERT INTO `role_permissions` VALUES (355, 10002, 3000002, 25, '2026-11-04 08:51:13', '2026-11-04 08:51:13', NULL);
INSERT INTO `role_permissions` VALUES (356, 10002, 3000002, 26, '2026-11-04 08:51:13', '2026-11-04 08:51:13', NULL);
INSERT INTO `role_permissions` VALUES (357, 10002, 3000002, 27, '2026-11-04 08:51:13', '2026-11-04 08:51:13', NULL);
INSERT INTO `role_permissions` VALUES (358, 10002, 3000002, 28, '2026-11-04 08:51:13', '2026-11-04 08:51:13', NULL);
INSERT INTO `role_permissions` VALUES (359, 10002, 3000002, 29, '2026-11-04 08:51:13', '2026-11-04 08:51:13', NULL);
INSERT INTO `role_permissions` VALUES (360, 10002, 3000002, 30, '2026-11-04 08:51:13', '2026-11-04 08:51:13', NULL);
INSERT INTO `role_permissions` VALUES (361, 10002, 3000002, 31, '2026-11-04 08:51:13', '2026-11-04 08:51:13', NULL);
INSERT INTO `role_permissions` VALUES (362, 10002, 3000002, 32, '2026-11-04 08:51:13', '2026-11-04 08:51:13', NULL);
INSERT INTO `role_permissions` VALUES (363, 10002, 3000002, 33, '2026-11-04 08:51:13', '2026-11-04 08:51:13', NULL);
INSERT INTO `role_permissions` VALUES (364, 10002, 3000002, 34, '2026-11-04 08:51:13', '2026-11-04 08:51:13', NULL);
INSERT INTO `role_permissions` VALUES (365, 10002, 3000002, 35, '2026-11-04 08:51:13', '2026-11-04 08:51:13', NULL);
INSERT INTO `role_permissions` VALUES (366, 10002, 3000002, 36, '2026-11-04 08:51:13', '2026-11-04 08:51:13', NULL);
INSERT INTO `role_permissions` VALUES (367, 10002, 3000002, 37, '2026-11-04 08:51:13', '2026-11-04 08:51:13', NULL);
INSERT INTO `role_permissions` VALUES (368, 10002, 3000002, 38, '2026-11-04 08:51:13', '2026-11-04 08:51:13', NULL);
INSERT INTO `role_permissions` VALUES (369, 10002, 3000002, 39, '2026-11-04 08:51:13', '2026-11-04 08:51:13', NULL);
INSERT INTO `role_permissions` VALUES (370, 10002, 3000002, 40, '2026-11-04 08:51:13', '2026-11-04 08:51:13', NULL);
INSERT INTO `role_permissions` VALUES (371, 10002, 3000002, 41, '2026-11-04 08:51:13', '2026-11-04 08:51:13', NULL);
INSERT INTO `role_permissions` VALUES (372, 10002, 3000002, 42, '2026-11-04 08:51:13', '2026-11-04 08:51:13', NULL);
INSERT INTO `role_permissions` VALUES (373, 10002, 3000002, 43, '2026-11-04 08:51:13', '2026-11-04 08:51:13', NULL);
INSERT INTO `role_permissions` VALUES (374, 10002, 3000002, 44, '2026-11-04 08:51:13', '2026-11-04 08:51:13', NULL);
INSERT INTO `role_permissions` VALUES (375, 10002, 3000002, 45, '2026-11-04 08:51:13', '2026-11-04 08:51:13', NULL);
INSERT INTO `role_permissions` VALUES (376, 10002, 3000002, 46, '2026-11-04 08:51:13', '2026-11-04 08:51:13', NULL);
INSERT INTO `role_permissions` VALUES (377, 10002, 3000002, 47, '2026-11-04 08:51:13', '2026-11-04 08:51:13', NULL);
INSERT INTO `role_permissions` VALUES (378, 10002, 3000002, 48, '2026-11-04 08:51:13', '2026-11-04 08:51:13', NULL);
INSERT INTO `role_permissions` VALUES (379, 10002, 3000002, 49, '2026-11-04 08:51:13', '2026-11-04 08:51:13', NULL);
INSERT INTO `role_permissions` VALUES (380, 10002, 3000002, 50, '2026-11-04 08:51:13', '2026-11-04 08:51:13', NULL);
INSERT INTO `role_permissions` VALUES (381, 10002, 3000002, 51, '2026-11-04 08:51:13', '2026-11-04 08:51:13', NULL);
INSERT INTO `role_permissions` VALUES (382, 10002, 3000002, 52, '2026-11-04 08:51:13', '2026-11-04 08:51:13', NULL);
INSERT INTO `role_permissions` VALUES (383, 10002, 3000002, 53, '2026-11-04 08:51:13', '2026-11-04 08:51:13', NULL);
INSERT INTO `role_permissions` VALUES (384, 10002, 3000002, 54, '2026-11-04 08:51:13', '2026-11-04 08:51:13', NULL);
INSERT INTO `role_permissions` VALUES (385, 10002, 3000002, 55, '2026-11-04 08:51:13', '2026-11-04 08:51:13', NULL);
INSERT INTO `role_permissions` VALUES (386, 10002, 3000002, 56, '2026-11-04 08:51:13', '2026-11-04 08:51:13', NULL);
INSERT INTO `role_permissions` VALUES (387, 10002, 3000002, 57, '2026-11-04 08:51:13', '2026-11-04 08:51:13', NULL);
INSERT INTO `role_permissions` VALUES (388, 10002, 3000002, 58, '2026-11-04 08:51:13', '2026-11-04 08:51:13', NULL);
INSERT INTO `role_permissions` VALUES (389, 10002, 3000002, 59, '2026-11-04 08:51:13', '2026-11-04 08:51:13', NULL);
INSERT INTO `role_permissions` VALUES (390, 10002, 3000002, 60, '2026-11-04 08:51:13', '2026-11-04 08:51:13', NULL);
INSERT INTO `role_permissions` VALUES (391, 10002, 3000002, 61, '2026-11-04 08:51:13', '2026-11-04 08:51:13', NULL);
INSERT INTO `role_permissions` VALUES (392, 10002, 3000002, 62, '2026-11-04 08:51:13', '2026-11-04 08:51:13', NULL);
INSERT INTO `role_permissions` VALUES (393, 10002, 3000002, 63, '2026-11-04 08:51:13', '2026-11-04 08:51:13', NULL);
INSERT INTO `role_permissions` VALUES (394, 10002, 3000002, 64, '2026-11-04 08:51:13', '2026-11-04 08:51:13', NULL);
INSERT INTO `role_permissions` VALUES (395, 10002, 3000002, 65, '2026-11-04 08:51:13', '2026-11-04 08:51:13', NULL);
INSERT INTO `role_permissions` VALUES (396, 10002, 3000002, 66, '2026-11-04 08:51:13', '2026-11-04 08:51:13', NULL);

-- ----------------------------
-- Table structure for roles
-- ----------------------------
DROP TABLE IF EXISTS `roles`;
CREATE TABLE `roles`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
  `tenant_id` bigint UNSIGNED NOT NULL DEFAULT 0 COMMENT '租户主键',
  `code` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '角色编码',
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '角色名称',
  `data_scope` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'self' COMMENT '数据范围：all、department_and_children、department、self、custom',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT '删除时间，未删除时为空',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `uk_roles_tenant_code`(`tenant_id` ASC, `code` ASC) USING BTREE,
  INDEX `idx_roles_scope`(`tenant_id` ASC, `data_scope` ASC) USING BTREE,
  CONSTRAINT `fk_roles_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 3000004 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '租户角色' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of roles
-- ----------------------------
INSERT INTO `roles` VALUES (1, 1, 'admin', '管理员', 'all', '2026-11-04 04:57:58', '2026-11-04 04:57:58', NULL);
INSERT INTO `roles` VALUES (2, 2, 'admin', '管理员', 'all', '2026-11-04 06:31:35', '2026-11-04 06:31:35', NULL);
INSERT INTO `roles` VALUES (3, 3, 'admin', '管理员', 'all', '2026-11-04 06:50:54', '2026-11-04 06:50:54', NULL);
INSERT INTO `roles` VALUES (3000000, 10000, 'admin', '管理员', 'all', '2026-11-04 07:07:40', '2026-11-04 07:07:40', NULL);
INSERT INTO `roles` VALUES (3000001, 10001, 'admin', '管理员', 'all', '2026-11-04 08:01:38', '2026-11-04 08:01:38', NULL);
INSERT INTO `roles` VALUES (3000002, 10002, 'admin', '管理员', 'all', '2026-11-04 08:51:12', '2026-11-04 08:51:12', NULL);
INSERT INTO `roles` VALUES (3000003, 10002, 'yunying', '运营管理', 'self', '2026-11-04 08:56:47', '2026-11-04 08:56:47', NULL);

-- ----------------------------
-- Table structure for tenant_modules
-- ----------------------------
DROP TABLE IF EXISTS `tenant_modules`;
CREATE TABLE `tenant_modules`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
  `tenant_id` bigint UNSIGNED NOT NULL DEFAULT 0 COMMENT '租户主键',
  `module_code` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '模块编码',
  `status` tinyint UNSIGNED NOT NULL DEFAULT 0 COMMENT '开通状态：1已开通 0未开通',
  `enabled_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '开通时间',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT '删除时间，未删除时为空',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `uk_tenant_modules`(`tenant_id` ASC, `module_code` ASC) USING BTREE,
  INDEX `idx_tenant_modules_status`(`tenant_id` ASC, `status` ASC) USING BTREE,
  INDEX `fk_tenant_modules_module`(`module_code` ASC) USING BTREE,
  CONSTRAINT `fk_tenant_modules_module` FOREIGN KEY (`module_code`) REFERENCES `modules` (`code`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `fk_tenant_modules_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 20006 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '租户模块开通记录' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of tenant_modules
-- ----------------------------
INSERT INTO `tenant_modules` VALUES (1, 1, 'iam', 1, '2026-11-04 04:57:59', '2026-11-04 04:57:59', '2026-11-04 04:57:59', NULL);
INSERT INTO `tenant_modules` VALUES (2, 2, 'iam', 1, '2026-11-04 06:31:35', '2026-11-04 06:31:35', '2026-11-04 06:31:35', NULL);
INSERT INTO `tenant_modules` VALUES (3, 3, 'iam', 1, '2026-11-04 06:50:55', '2026-11-04 06:50:55', '2026-11-04 06:50:55', NULL);
INSERT INTO `tenant_modules` VALUES (20000, 10000, 'iam', 1, '2026-11-04 07:07:40', '2026-11-04 07:07:40', '2026-11-04 07:07:40', NULL);
INSERT INTO `tenant_modules` VALUES (20001, 10001, 'iam', 1, '2026-11-04 08:01:38', '2026-11-04 08:01:38', '2026-11-04 08:01:38', NULL);
INSERT INTO `tenant_modules` VALUES (20002, 10001, 'hr', 1, '2026-11-04 08:38:36', '2026-11-04 08:38:36', '2026-11-04 08:38:36', NULL);
INSERT INTO `tenant_modules` VALUES (20003, 10000, 'hr', 1, '2026-11-04 08:46:36', '2026-11-04 08:46:36', '2026-11-04 08:46:36', NULL);
INSERT INTO `tenant_modules` VALUES (20004, 10002, 'iam', 1, '2026-11-04 08:51:13', '2026-11-04 08:51:13', '2026-11-04 08:51:13', NULL);
INSERT INTO `tenant_modules` VALUES (20005, 10002, 'hr', 1, '2026-11-04 08:51:58', '2026-11-04 08:51:58', '2026-11-04 08:51:58', NULL);

-- ----------------------------
-- Table structure for tenant_user_roles
-- ----------------------------
DROP TABLE IF EXISTS `tenant_user_roles`;
CREATE TABLE `tenant_user_roles`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
  `tenant_id` bigint UNSIGNED NOT NULL DEFAULT 0 COMMENT '租户主键',
  `tenant_user_id` bigint UNSIGNED NOT NULL DEFAULT 0 COMMENT '成员主键',
  `role_id` bigint UNSIGNED NOT NULL DEFAULT 0 COMMENT '角色主键',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT '删除时间，未删除时为空',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `uk_tenant_user_roles`(`tenant_user_id` ASC, `role_id` ASC) USING BTREE,
  INDEX `idx_tenant_user_roles_role`(`role_id` ASC) USING BTREE,
  INDEX `fk_tenant_user_roles_tenant`(`tenant_id` ASC) USING BTREE,
  CONSTRAINT `fk_tenant_user_roles_member` FOREIGN KEY (`tenant_user_id`) REFERENCES `tenant_users` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `fk_tenant_user_roles_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `fk_tenant_user_roles_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 7 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '成员与角色关联' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of tenant_user_roles
-- ----------------------------
INSERT INTO `tenant_user_roles` VALUES (1, 1, 1, 1, '2026-11-04 04:57:59', '2026-11-04 04:57:59', NULL);
INSERT INTO `tenant_user_roles` VALUES (2, 2, 2, 2, '2026-11-04 06:31:35', '2026-11-04 06:31:35', NULL);
INSERT INTO `tenant_user_roles` VALUES (3, 3, 3, 3, '2026-11-04 06:50:55', '2026-11-04 06:50:55', NULL);
INSERT INTO `tenant_user_roles` VALUES (4, 10000, 4, 3000000, '2026-11-04 07:07:40', '2026-11-04 07:07:40', NULL);
INSERT INTO `tenant_user_roles` VALUES (5, 10001, 5, 3000001, '2026-11-04 08:01:38', '2026-11-04 08:01:38', NULL);
INSERT INTO `tenant_user_roles` VALUES (6, 10002, 6, 3000002, '2026-11-04 08:51:13', '2026-11-04 08:51:13', NULL);

-- ----------------------------
-- Table structure for tenant_users
-- ----------------------------
DROP TABLE IF EXISTS `tenant_users`;
CREATE TABLE `tenant_users`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
  `tenant_id` bigint UNSIGNED NOT NULL DEFAULT 0 COMMENT '租户主键',
  `user_id` bigint UNSIGNED NOT NULL DEFAULT 0 COMMENT '用户主键',
  `department_id` bigint UNSIGNED NULL DEFAULT NULL COMMENT '所属部门，尚未归属时为空',
  `status` tinyint UNSIGNED NOT NULL DEFAULT 1 COMMENT '状态：1有效 0停用',
  `is_owner` tinyint UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否租户所有者：1是 0否',
  `joined_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '加入时间',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT '删除时间，未删除时为空',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `uk_tenant_users_tenant_user`(`tenant_id` ASC, `user_id` ASC) USING BTREE,
  INDEX `idx_tenant_users_user`(`user_id` ASC) USING BTREE,
  INDEX `idx_tenant_users_department`(`tenant_id` ASC, `department_id` ASC) USING BTREE,
  INDEX `fk_tenant_users_department`(`department_id` ASC) USING BTREE,
  CONSTRAINT `fk_tenant_users_department` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `fk_tenant_users_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `fk_tenant_users_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 8 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '租户成员，一个用户可加入多个租户' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of tenant_users
-- ----------------------------
INSERT INTO `tenant_users` VALUES (1, 1, 1, NULL, 1, 1, '2026-11-04 04:57:59', '2026-11-04 04:57:59', '2026-11-04 04:57:59', NULL);
INSERT INTO `tenant_users` VALUES (2, 2, 2, NULL, 1, 1, '2026-11-04 06:31:35', '2026-11-04 06:31:35', '2026-11-04 06:31:35', NULL);
INSERT INTO `tenant_users` VALUES (3, 3, 3, NULL, 1, 1, '2026-11-04 06:50:55', '2026-11-04 06:50:55', '2026-11-04 06:50:55', NULL);
INSERT INTO `tenant_users` VALUES (4, 10000, 4000000, NULL, 1, 1, '2026-11-04 07:07:40', '2026-11-04 07:07:40', '2026-11-04 07:07:40', NULL);
INSERT INTO `tenant_users` VALUES (5, 10001, 4000001, NULL, 1, 1, '2026-11-04 08:01:38', '2026-11-04 08:01:38', '2026-11-04 08:01:38', NULL);
INSERT INTO `tenant_users` VALUES (6, 10002, 4000001, NULL, 1, 1, '2026-11-04 08:51:13', '2026-11-04 08:51:13', '2026-11-04 08:51:13', NULL);
INSERT INTO `tenant_users` VALUES (7, 10002, 4000002, 1000002, 1, 0, '2026-11-04 08:56:27', '2026-11-04 08:56:27', '2026-11-04 08:56:27', NULL);

-- ----------------------------
-- Table structure for tenants
-- ----------------------------
DROP TABLE IF EXISTS `tenants`;
CREATE TABLE `tenants`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '租户名称',
  `slug` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '租户标识，请求头 X-Tenant 使用',
  `status` tinyint UNSIGNED NOT NULL DEFAULT 1 COMMENT '状态：1启用 0停用',
  `plan_id` bigint UNSIGNED NOT NULL COMMENT '套餐主键',
  `settings` json NOT NULL COMMENT '租户设置',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT '删除时间，未删除时为空',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `uk_tenants_slug`(`slug` ASC) USING BTREE,
  INDEX `idx_tenants_status`(`status` ASC) USING BTREE,
  INDEX `idx_tenants_plan`(`plan_id` ASC) USING BTREE,
  CONSTRAINT `fk_tenants_plan` FOREIGN KEY (`plan_id`) REFERENCES `plans` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 10003 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '租户' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of tenants
-- ----------------------------
INSERT INTO `tenants` VALUES (1, '演示租户', 'demo', 1, 1, '[]', '2026-11-04 04:57:58', '2026-11-04 07:50:39', NULL);
INSERT INTO `tenants` VALUES (2, '曼雅艺', 'manyayi', 1, 1, '[]', '2026-11-04 06:31:35', '2026-11-04 07:50:39', NULL);
INSERT INTO `tenants` VALUES (3, '界面演示', 'ui-demo', 1, 1, '[]', '2026-11-04 06:50:54', '2026-11-04 07:50:39', NULL);
INSERT INTO `tenants` VALUES (10000, '森格条码', 'senge', 1, 1, '[]', '2026-11-04 07:07:40', '2026-11-04 07:50:39', NULL);
INSERT INTO `tenants` VALUES (10001, '阶段企业', 'phase-co', 1, 2, '[]', '2026-11-04 08:01:38', '2026-11-04 08:27:50', NULL);
INSERT INTO `tenants` VALUES (10002, '融易投', 'rongyitou', 1, 2, '[]', '2026-11-04 08:51:12', '2026-11-04 08:51:12', NULL);

-- ----------------------------
-- Table structure for users
-- ----------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '姓名',
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '邮箱，业务必填，空字符串由校验拒绝',
  `mobile` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '手机号，可选联系方式',
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '密码哈希',
  `status` tinyint UNSIGNED NOT NULL DEFAULT 1 COMMENT '状态：1启用 0停用',
  `remember_token` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '记住登录令牌',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT '删除时间，未删除时为空',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `uk_users_email`(`email` ASC) USING BTREE,
  UNIQUE INDEX `uk_users_mobile`(`mobile` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 4000003 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '全局用户账号，不隶属单一租户' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of users
-- ----------------------------
INSERT INTO `users` VALUES (1, '验证用户', 'shell-check@example.com', NULL, '$2y$12$ZqveY2DZuaBrMD8WEnnTI.BW/Oicvlzm/SpviOA0rNCnNyNkGpRnq', 1, '', '2026-11-04 04:57:38', '2026-11-04 04:57:38', NULL);
INSERT INTO `users` VALUES (2, 'admin', 'itpeeg@gmail.com', NULL, '$2y$12$xxWkMJZvWS9IMgcfENAQuune7MJ1.XkMR4kIoZ5SKPsNjS5e1RTlO', 1, '', '2026-11-04 06:31:01', '2026-11-04 06:31:01', NULL);
INSERT INTO `users` VALUES (3, '界面管理员', 'iam-ui@example.com', NULL, '$2y$12$g4RXLAtEHv3KeZZHXEeGculHFAU3wJPrZ9btp3hj5FcOPYS8K0wxG', 1, '', '2026-11-04 06:47:46', '2026-11-04 06:47:46', NULL);
INSERT INTO `users` VALUES (4000000, 'sunny', 'sunny@163.com', NULL, '$2y$12$A5meS1V59b2fUZjh7b9VsOg.lNgInWNk.DheAgVfLGwVG8uKW8flK', 1, '', '2026-11-04 07:07:21', '2026-11-04 07:07:21', NULL);
INSERT INTO `users` VALUES (4000001, '阶段检查', 'phase02@example.com', NULL, '$2y$12$dermS6bnrhuLLn7aRAllk.CmRACElL4rJvLV08iMiidiC83V2WZEa', 1, '', '2026-11-04 08:01:13', '2026-11-04 08:01:13', NULL);
INSERT INTO `users` VALUES (4000002, '李四', 'lisi@qq.com', NULL, '$2y$12$5PucLSKlWwZ35v7Pt9/tlOS5dEb3TpqL7Fhznva4ERE3W95uu/OuG', 1, '', '2026-11-04 08:56:27', '2026-11-04 08:56:27', NULL);

SET FOREIGN_KEY_CHECKS = 1;
