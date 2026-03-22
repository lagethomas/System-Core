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
('enable_system_logs', '1'),
('security_max_attempts', '5'),
('security_lockout_time', '15'),
('security_single_session', '1'),
('security_strong_password', '0'),
('security_session_timeout', '120'),
('security_ip_lockout', '0'),
('security_log_days', '30'),
('security_log_limit', '10000');

-- ----------------------------
-- Table structure for cp_mesas
-- ----------------------------
CREATE TABLE IF NOT EXISTS `cp_mesas` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `numero` INT NOT NULL UNIQUE,
  `status` ENUM('livre', 'ocupada') DEFAULT 'livre',
  `criado_em` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for cp_comanda_itens
-- ----------------------------
CREATE TABLE IF NOT EXISTS `cp_comanda_itens` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `mesa_id` INT NOT NULL,
  `produto_nome` VARCHAR(255) NOT NULL,
  `preco` DECIMAL(10, 2) NOT NULL,
  `quantidade` INT DEFAULT 1,
  `criado_em` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`mesa_id`) REFERENCES `cp_mesas`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for cp_produtos
-- ----------------------------
CREATE TABLE IF NOT EXISTS `cp_produtos` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `codigo` VARCHAR(50) UNIQUE NOT NULL,
  `nome` VARCHAR(255) NOT NULL,
  `preco` DECIMAL(10, 2) NOT NULL,
  `categoria` VARCHAR(100) DEFAULT 'Diversos',
  `imagem` VARCHAR(255) DEFAULT NULL,
  `criado_em` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for cp_financeiro
-- ----------------------------
CREATE TABLE IF NOT EXISTS `cp_financeiro` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `descricao` VARCHAR(255) NOT NULL,
  `valor` DECIMAL(10, 2) NOT NULL,
  `tipo` ENUM('entrada', 'saida') NOT NULL,
  `metodo_pagamento` VARCHAR(50) DEFAULT 'Diversos' COMMENT 'Dinheiro, PIX, Cartão...',
  `data_movimentacao` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `referencia_id` INT DEFAULT NULL COMMENT 'ID da comanda/transação externa se houver',
  `caixa_id` INT DEFAULT NULL COMMENT 'Sessão de caixa vinculada'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for cp_caixas
-- ----------------------------
CREATE TABLE IF NOT EXISTS `cp_caixas` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `status` ENUM('aberto', 'fechado') DEFAULT 'fechado',
  `saldo_inicial` DECIMAL(10, 2) DEFAULT 0.00,
  `saldo_final` DECIMAL(10, 2) DEFAULT NULL,
  `data_abertura` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `data_fechamento` TIMESTAMP NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Initial Data for Produtos
-- ----------------------------
INSERT INTO `cp_produtos` (`nome`, `preco`, `categoria`) VALUES 
('Cerveja 600ml', 15.00, 'Bebidas'),
('Refrigerante', 8.00, 'Bebidas'),
('Água Mineral', 5.00, 'Bebidas'),
('Batata Frita', 25.00, 'Porções'),
('Hambúrguer Casa', 35.00, 'Lanches'),
('Pizza Grande', 65.00, 'Pizzas');

-- ----------------------------
-- Initial Data for Mesas
-- ----------------------------
INSERT INTO `cp_mesas` (`numero`, `status`) VALUES 
(1, 'livre'), (2, 'livre'), (3, 'livre'), (4, 'livre'),
(5, 'livre'), (6, 'livre'), (7, 'livre'), (8, 'livre'),
(9, 'livre'), (10, 'livre'), (11, 'livre'), (12, 'livre');

SET FOREIGN_KEY_CHECKS = 1;
