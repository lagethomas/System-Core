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
  `role` enum('administrador','usuario') DEFAULT 'usuario',
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
-- Table structure for cp_migrations (Internal tracker)
-- ----------------------------
CREATE TABLE IF NOT EXISTS `cp_migrations` (
    `migration_id` INT PRIMARY KEY,
    `title` VARCHAR(255),
    `executed_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Initial Data
-- admin / admin123
-- ----------------------------
INSERT IGNORE INTO `cp_users` (`name`, `username`, `email`, `password`, `role`) VALUES 
('Administrador', 'admin', 'admin@admin.com', '$argon2id$v=19$m=65536,t=4,p=1$ekx3REpPWWx4M1QyTjVROA$vG0tQ6G0fRzX2m7w9P8uXQ', 'administrador');

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
('login_background', NULL);

-- Record that migrations up to #004 are part of base schema
INSERT IGNORE INTO `cp_migrations` (`migration_id`, `title`) VALUES 
(1, 'Add single session support to users table'),
(2, 'Add security configuration settings'),
(3, 'Add heartbeat for single session enforcement'),
(4, 'Add Visual Theme settings'),
(5, 'Add Email Confirmations Table'),
(6, 'Add Security Infrastructure (Login Attempts & Blocked IPs)');

SET FOREIGN_KEY_CHECKS = 1;
