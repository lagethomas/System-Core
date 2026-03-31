-- SaaSFlow Core - Migration Index
-- This file contains all database updates structured and indexed.
-- To apply these, run: php scripts/migrate.php

-- [MIGRATION #001]
-- Title: Add single session support to users table
-- Description: Adds current_session_id to tracks active session.
ALTER TABLE `cp_users` ADD COLUMN IF NOT EXISTS `current_session_id` VARCHAR(255) DEFAULT NULL COMMENT 'Active session ID for single-session enforcement';

-- [MIGRATION #002]
-- Title: Add security configuration settings
-- Description: Adds new security settings to the cp_settings table.
INSERT IGNORE INTO `cp_settings` (`setting_key`, `setting_value`) VALUES 
('security_max_attempts', '5'),
('security_lockout_time', '15'),
('security_single_session', '1'),
('security_strong_password', '1'),
('security_session_timeout', '120'),
('security_ip_lockout', '0'),
('security_log_days', '30'),
('security_log_limit', '10000');

-- [MIGRATION #003]
-- Title: Add heartbeat for single session enforcement
-- Description: Adds last_pulse columns to track activity for blocking.
ALTER TABLE `cp_users` ADD COLUMN IF NOT EXISTS `last_pulse` DATETIME DEFAULT NULL COMMENT 'Last user activity heartbeat';

-- [MIGRATION #004]
-- Title: Add Visual Theme settings
-- Description: Adds system_logo and login_background to cp_settings.
INSERT IGNORE INTO `cp_settings` (`setting_key`, `setting_value`) VALUES 
('system_logo', NULL),
('login_background', NULL);
