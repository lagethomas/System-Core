-- Core System Database Schema
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
-- Initial Data
-- admin/admin123
-- ----------------------------
INSERT INTO `cp_users` (`name`, `username`, `email`, `password`, `role`) VALUES 
('Administrador', 'admin', 'admin@admin.com', '$2y$12$P3WwePwHVEpmLvd4MSxVmuHwLFdmeMRKVNxUOrpT1IWs0YIyNbZBG', 'administrador'); -- Pwd: password

INSERT INTO `cp_settings` (`setting_key`, `setting_value`) VALUES 
('system_theme', 'gold-black'),
('system_name', 'SaaSFlow Core'),
('smtp_host', 'localhost'),
('smtp_port', '587'),
('smtp_secure', 'tls'),
('enable_system_logs', '1');

SET FOREIGN_KEY_CHECKS = 1;
