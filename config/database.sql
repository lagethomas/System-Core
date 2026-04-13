-- SaaSFlow Core - Complete Database Schema
-- Updated with all migrations and indexes
-- Standard password hash algorithm: Argon2id (Rule 19)

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for cp_users
-- ----------------------------
CREATE TABLE IF NOT EXISTS `cp_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(50) DEFAULT 'usuario',
  `company_id` int(11) DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `last_login` datetime DEFAULT NULL,
  `current_session_id` varchar(255) DEFAULT NULL COMMENT 'Active session ID for single-session enforcement',
  `last_pulse` datetime DEFAULT NULL COMMENT 'Last user activity heartbeat',
  `created_by` int(11) DEFAULT NULL,
  `zip_code` varchar(20) DEFAULT NULL,
  `street` varchar(255) DEFAULT NULL,
  `neighborhood` varchar(100) DEFAULT NULL,
  `address_number` varchar(10) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(50) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_email` (`email`),
  UNIQUE KEY `idx_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for cp_settings
-- ----------------------------
CREATE TABLE IF NOT EXISTS `cp_settings` (
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text,
  PRIMARY KEY (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for cp_logs
-- ----------------------------
CREATE TABLE IF NOT EXISTS `cp_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `action` varchar(255) NOT NULL,
  `description` text,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_action` (`action`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for cp_notifications
-- ----------------------------
CREATE TABLE IF NOT EXISTS `cp_notifications` (
    `id` BIGINT(20) NOT NULL AUTO_INCREMENT,
    `user_id` INT(11) NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `message` TEXT NOT NULL,
    `link` VARCHAR(255) DEFAULT NULL,
    `type` VARCHAR(50) DEFAULT 'info',
    `is_read` TINYINT(1) DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `user_id` (`user_id`),
    KEY `is_read` (`is_read`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for cp_login_attempts
-- ----------------------------
CREATE TABLE IF NOT EXISTS `cp_login_attempts` (
  `ip_address` varchar(45) NOT NULL,
  `attempts` int(11) NOT NULL DEFAULT '0',
  `last_attempt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`ip_address`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for cp_blocked_ips
-- ----------------------------
CREATE TABLE IF NOT EXISTS `cp_blocked_ips` (
  `ip_address` varchar(45) NOT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`ip_address`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for cp_email_confirmations
-- ----------------------------
CREATE TABLE IF NOT EXISTS `cp_email_confirmations` (
  `user_id` int(11) NOT NULL,
  `new_email` varchar(255) NOT NULL,
  `token` varchar(100) NOT NULL,
  `expires_at` timestamp NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `idx_token` (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for cp_plans
-- ----------------------------
CREATE TABLE IF NOT EXISTS `cp_plans` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `base_price` DECIMAL(10,2) NOT NULL DEFAULT 40.00,
    `included_users` INT NOT NULL DEFAULT 4,
    `extra_user_price` DECIMAL(10,2) NOT NULL DEFAULT 30.00,
    `trial_days` INT NOT NULL DEFAULT 7,
    `partner_commission_percentage` DECIMAL(5,2) DEFAULT 0.00,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for cp_companies
-- ----------------------------
CREATE TABLE IF NOT EXISTS `cp_companies` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `slug` VARCHAR(255) UNIQUE NOT NULL,
    `document` VARCHAR(255) DEFAULT NULL,
    `cnpj` VARCHAR(20) DEFAULT NULL,
    `phone` VARCHAR(50) DEFAULT NULL,
    `email` VARCHAR(255) DEFAULT NULL,
    `zip_code` VARCHAR(20) DEFAULT NULL,
    `street` VARCHAR(255) DEFAULT NULL,
    `neighborhood` VARCHAR(100) DEFAULT NULL,
    `address_number` VARCHAR(10) DEFAULT NULL,
    `city` VARCHAR(100) DEFAULT NULL,
    `state` VARCHAR(50) DEFAULT NULL,
    `theme_color` VARCHAR(50) DEFAULT '#2563eb',
    `theme` VARCHAR(50) DEFAULT 'default',
    `login_theme` VARCHAR(50) DEFAULT NULL,
    `plan_id` INT DEFAULT NULL,
    `partner_id` INT DEFAULT NULL,
    `base_price` DECIMAL(10,2) DEFAULT 40.00,
    `included_users` INT DEFAULT 4,
    `extra_user_price` DECIMAL(10,2) DEFAULT 30.00,
    `peak_users_count` INT DEFAULT 0,
    `logo` VARCHAR(255) DEFAULT NULL,
    `background_image` VARCHAR(255) DEFAULT NULL,
    `active` TINYINT(1) DEFAULT 1,
    `status` ENUM('active', 'inactive', 'suspended', 'trial') DEFAULT 'active',
    `mp_access_token` TEXT DEFAULT NULL,
    `mp_public_key` TEXT DEFAULT NULL,
    `mp_enabled` TINYINT(1) DEFAULT 0,
    `subscription_status` VARCHAR(50) DEFAULT NULL,
    `inactive_since` DATE DEFAULT NULL,
    `trashed_at` DATETIME DEFAULT NULL,
    `expires_at` DATE DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_slug` (`slug`),
    INDEX `idx_active` (`active`),
    INDEX `idx_expires` (`expires_at`),
    INDEX `idx_partner` (`partner_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for cp_invoices
-- ----------------------------
CREATE TABLE IF NOT EXISTS `cp_invoices` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `company_id` INT NOT NULL,
    `amount` DECIMAL(10,2) NOT NULL,
    `status` ENUM('pending','paid','cancelled','expired') DEFAULT 'pending',
    `type` ENUM('single','recurring') DEFAULT 'single',
    `description` TEXT DEFAULT NULL,
    `due_date` DATE NOT NULL,
    `paid_at` DATETIME DEFAULT NULL,
    `last_reminder_date` DATE DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_company` (`company_id`),
    INDEX `idx_status` (`status`),
    INDEX `idx_due` (`due_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Constraints
-- ----------------------------
ALTER TABLE `cp_users` ADD FOREIGN KEY (`company_id`) REFERENCES `cp_companies`(`id`) ON DELETE SET NULL;

-- ----------------------------
-- Table structure for cp_migrations (Internal tracker)
-- ----------------------------
CREATE TABLE IF NOT EXISTS `cp_migrations` (
    `migration_id` INT PRIMARY KEY,
    `title` VARCHAR(255),
    `executed_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Initial Data
-- admin / admin@1234
-- ----------------------------
INSERT IGNORE INTO `cp_users` (`name`, `username`, `email`, `password`, `role`) VALUES 
('Administrador', 'admin', 'admin@admin.com', '$argon2i$v=19$m=16,t=2,p=1$QkFINVVWNEVxcFZnWDYzSQ$CoSx6PyMbZW1hf5P6RQRgg', 'administrador');

INSERT IGNORE INTO `cp_settings` (`setting_key`, `setting_value`) VALUES 
('system_theme', 'gold-black'),
('system_name', 'Core'),
('smtp_host', 'localhost'),
('smtp_port', '587'),
('smtp_secure', 'tls'),
('enable_system_logs', '1'),
('security_max_attempts', '5'),
('security_lockout_time', '15'),
('security_single_session', '1'),
('security_strong_password', '1'),
('security_session_timeout', '30'),
('security_ip_lockout', '0'),
('security_log_days', '30'),
('security_log_limit', '10000'),
('system_logo', NULL),
('login_background', NULL),
('system_login_theme', 'gold-black');

-- Record that migrations up to #004 are part of base schema
INSERT IGNORE INTO `cp_migrations` (`migration_id`, `title`) VALUES 
(1, 'Add single session support to users table'),
(2, 'Add security configuration settings'),
(3, 'Add heartbeat for single session enforcement'),
(4, 'Add Visual Theme settings'),
(5, 'Add Email Confirmations Table'),
(6, 'Add Security Infrastructure (Login Attempts & Blocked IPs)'),
(12, 'Add individual login theme support to companies and settings');

SET FOREIGN_KEY_CHECKS = 1;
