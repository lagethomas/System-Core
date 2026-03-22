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
-- Title: Create Comanda Tables (Mesas & Itens)
-- Description: Adds cp_mesas and cp_comanda_itens for table management.
CREATE TABLE IF NOT EXISTS `cp_mesas` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `numero` INT NOT NULL UNIQUE,
  `status` ENUM('livre', 'ocupada') DEFAULT 'livre',
  `criado_em` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `cp_comanda_itens` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `mesa_id` INT NOT NULL,
  `produto_nome` VARCHAR(255) NOT NULL,
  `preco` DECIMAL(10, 2) NOT NULL,
  `quantidade` INT DEFAULT 1,
  `criado_em` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`mesa_id`) REFERENCES `cp_mesas`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- [MIGRATION #005]
-- Title: Initial Tables Data
-- Description: Adds default 12 tables.
INSERT IGNORE INTO `cp_mesas` (`numero`, `status`) VALUES 
(1, 'livre'), (2, 'livre'), (3, 'livre'), (4, 'livre'),
(5, 'livre'), (6, 'livre'), (7, 'livre'), (8, 'livre'),
(9, 'livre'), (10, 'livre'), (11, 'livre'), (12, 'livre');

-- [MIGRATION #006]
-- Title: Create Products Table
-- Description: Adds cp_produtos for quick order adding.
CREATE TABLE IF NOT EXISTS `cp_produtos` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nome` VARCHAR(255) NOT NULL,
  `preco` DECIMAL(10, 2) NOT NULL,
  `categoria` VARCHAR(100) DEFAULT 'Diversos',
  `criado_em` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO `cp_produtos` (`nome`, `preco`, `categoria`) VALUES 
('Cerveja 600ml', 15.00, 'Bebidas'),
('Refrigerante', 8.00, 'Bebidas'),
('Água Mineral', 5.00, 'Bebidas'),
('Batata Frita', 25.00, 'Porções'),
('Hambúrguer Casa', 35.00, 'Lanches'),
('Pizza Grande', 65.00, 'Pizzas');

-- [MIGRATION #007]
-- Title: Create Finance Table
-- Description: Adds cp_financeiro to track inflows and outflows.
CREATE TABLE IF NOT EXISTS `cp_financeiro` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `descricao` VARCHAR(255) NOT NULL,
  `valor` DECIMAL(10, 2) NOT NULL,
  `tipo` ENUM('entrada', 'saida') NOT NULL,
  `data_movimentacao` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `referencia_id` INT DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- [MIGRATION #008]
-- Title: Advanced Products and Cashier
-- Description: Adds code/image to products, payment methods to finance and cashier control table.
ALTER TABLE `cp_produtos` ADD COLUMN IF NOT EXISTS `codigo` VARCHAR(50) UNIQUE AFTER `id`;
ALTER TABLE `cp_produtos` ADD COLUMN IF NOT EXISTS `imagem` VARCHAR(255) AFTER `categoria`;
ALTER TABLE `cp_financeiro` ADD COLUMN IF NOT EXISTS `metodo_pagamento` VARCHAR(50) DEFAULT 'Diversos' AFTER `tipo`;
ALTER TABLE `cp_financeiro` ADD COLUMN IF NOT EXISTS `caixa_id` INT DEFAULT NULL AFTER `referencia_id`;

CREATE TABLE IF NOT EXISTS `cp_caixas` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `status` ENUM('aberto', 'fechado') DEFAULT 'fechado',
  `saldo_inicial` DECIMAL(10, 2) DEFAULT 0.00,
  `saldo_final` DECIMAL(10, 2) DEFAULT NULL,
  `data_abertura` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `data_fechamento` TIMESTAMP NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- [MIGRATION #009]
-- Title: System Logging
-- Description: Creates the logs table for activity tracking.
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
