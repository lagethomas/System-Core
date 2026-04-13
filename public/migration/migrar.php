<?php
declare(strict_types=1);

/**
 * SaaSFlow Core – Secure Migration Runner (Consolidated)
 * Rule 26: Centralizar em funções que verificam se a alteração já foi executada (idempotência).
 * Rule 27: Migrations via URL exigem parâmetro ?token= validando contra DB_MIGRATION_TOKEN no .env.
 */

// Load configuration and environment
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/DB.php';

// --- SECURITY CHECK (Rule 27) ---
$envToken   = $_ENV['DB_MIGRATION_TOKEN'] ?? '';
$inputToken = $_GET['token'] ?? '';

// If running in CLI, skip token check 
$isCLI = (php_sapi_name() === 'cli');

if (!$isCLI) {
    // Web Access Security
    if (empty($envToken)) {
        die("❌ Erro: DB_MIGRATION_TOKEN não está definido no .env. Migração bloqueada via WEB por segurança.\n");
    }

    if ($inputToken !== $envToken) {
        header('HTTP/1.1 403 Forbidden');
        die("❌ Acesso Negado: Token de migração inválido.\n");
    }
}

echo "<pre>"; // For browser readability
echo "🚀 Iniciando Migrações do Sistema...\n";

try {
    global $pdo;

    // ── Pre-check: Migration Tracking Table ────────────────────
    $pdo->exec("CREATE TABLE IF NOT EXISTS `cp_migrations` (
        `migration_id` INT PRIMARY KEY,
        `title` VARCHAR(255),
        `executed_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB;");

    /**
     * Define migrations array
     * Each entry: 'id' => integer, 'title' => string, 'sql' => array of SQL statements
     */
    $migrations = [
        [
            'id' => 1,
            'title' => 'Add single session support to users table',
            'sql' => [
                "ALTER TABLE `cp_users` ADD COLUMN IF NOT EXISTS `current_session_id` VARCHAR(255) DEFAULT NULL COMMENT 'Active session ID for single-session enforcement';"
            ]
        ],
        [
            'id' => 2,
            'title' => 'Add security configuration settings',
            'sql' => [
                "INSERT IGNORE INTO `cp_settings` (`setting_key`, `setting_value`) VALUES 
                ('security_max_attempts', '5'),
                ('security_lockout_time', '15'),
                ('security_single_session', '1'),
                ('security_strong_password', '1'),
                ('security_session_timeout', '30'),
                ('security_ip_lockout', '0'),
                ('security_log_days', '30'),
                ('security_log_limit', '10000');"
            ]
        ],
        [
            'id' => 3,
            'title' => 'Add heartbeat for single session enforcement',
            'sql' => [
                "ALTER TABLE `cp_users` ADD COLUMN IF NOT EXISTS `last_pulse` DATETIME DEFAULT NULL COMMENT 'Last user activity heartbeat';"
            ]
        ],
        [
            'id' => 4,
            'title' => 'Add Visual Theme settings',
            'sql' => [
                "INSERT IGNORE INTO `cp_settings` (`setting_key`, `setting_value`) VALUES 
                ('system_logo', NULL),
                ('login_background', NULL),
                ('system_login_theme', 'gold-black');"
            ]
        ],
        [
            'id' => 5,
            'title' => 'Add Email Confirmations Table',
            'sql' => [
                "CREATE TABLE IF NOT EXISTS `cp_email_confirmations` (
                  `user_id` int(11) NOT NULL,
                  `new_email` varchar(255) NOT NULL,
                  `token` varchar(100) NOT NULL,
                  `expires_at` timestamp NOT NULL,
                  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
                  PRIMARY KEY (`user_id`),
                  UNIQUE KEY `idx_token` (`token`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;"
            ]
        ],
        [
            'id' => 6,
            'title' => 'Add Security Infrastructure (Login Attempts & Blocked IPs)',
            'sql' => [
                "CREATE TABLE IF NOT EXISTS `cp_login_attempts` (
                  `ip_address` varchar(45) NOT NULL,
                  `attempts` int(11) NOT NULL DEFAULT '0',
                  `last_attempt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                  PRIMARY KEY (`ip_address`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
                "CREATE TABLE IF NOT EXISTS `cp_blocked_ips` (
                  `ip_address` varchar(45) NOT NULL,
                  `reason` varchar(255) DEFAULT NULL,
                  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
                  PRIMARY KEY (`ip_address`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;"
            ]
        ],
        [
            'id' => 7,
            'title' => 'Create SaaS Plans Table',
            'sql' => [
                "CREATE TABLE IF NOT EXISTS `cp_plans` (
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `name` VARCHAR(100) NOT NULL,
                    `base_price` DECIMAL(10,2) NOT NULL DEFAULT 40.00,
                    `included_users` INT NOT NULL DEFAULT 4,
                    `extra_user_price` DECIMAL(10,2) NOT NULL DEFAULT 30.00,
                    `trial_days` INT NOT NULL DEFAULT 7,
                    `partner_commission_percentage` DECIMAL(5,2) DEFAULT 0.00,
                    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;"
            ]
        ],
        [
            'id' => 8,
            'title' => 'Insert Initial SaaS Plans',
            'sql' => [
                "INSERT IGNORE INTO `cp_plans` (id, name, base_price, included_users, extra_user_price, trial_days) VALUES 
                (1, 'Bronze', 49.90, 2, 20.00, 15),
                (2, 'Prata', 89.90, 5, 15.00, 15),
                (3, 'Ouro', 149.90, 10, 10.00, 15);"
            ]
        ],
        [
            'id' => 9,
            'title' => 'Create/Update SaaS Companies Table with support for Custom Domains',
            'sql' => [
                "CREATE TABLE IF NOT EXISTS `cp_companies` (
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `name` VARCHAR(255) NOT NULL,
                    `slug` VARCHAR(255) UNIQUE NOT NULL,
                    `document` VARCHAR(255) DEFAULT NULL,
                    `cnpj` VARCHAR(20) DEFAULT NULL,
                    `custom_domain` VARCHAR(255) DEFAULT NULL,
                    `phone` VARCHAR(50) DEFAULT NULL,
                    `email` VARCHAR(255) DEFAULT NULL,
                    `theme_color` VARCHAR(50) DEFAULT '#2563eb',
                    `theme` VARCHAR(50) DEFAULT 'default',
                    `login_theme` VARCHAR(50) DEFAULT NULL,
                    `plan_id` INT DEFAULT NULL,
                    `partner_id` INT DEFAULT NULL,
                    `peak_users_count` INT DEFAULT 0,
                    `active` TINYINT(1) DEFAULT 1,
                    `expires_at` DATE DEFAULT NULL,
                    `inactive_since` DATE DEFAULT NULL,
                    `trashed_at` DATETIME DEFAULT NULL,
                    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    INDEX `idx_slug` (`slug`),
                    INDEX `idx_active` (`active`),
                    INDEX `idx_expires` (`expires_at`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",
                // Ensure columns exist if table was already created by another source
                "ALTER TABLE `cp_companies` ADD COLUMN IF NOT EXISTS `custom_domain` VARCHAR(255) DEFAULT NULL AFTER `cnpj`;",
                "ALTER TABLE `cp_companies` ADD COLUMN IF NOT EXISTS `document` VARCHAR(255) DEFAULT NULL AFTER `slug`;",
                "ALTER TABLE `cp_companies` ADD COLUMN IF NOT EXISTS `theme_color` VARCHAR(50) DEFAULT '#d4af37' AFTER `email`;",
                "ALTER TABLE `cp_companies` ADD COLUMN IF NOT EXISTS `theme` VARCHAR(50) DEFAULT 'gold-black' AFTER `theme_color`;",
                "ALTER TABLE `cp_companies` ADD COLUMN IF NOT EXISTS `peak_users_count` INT DEFAULT 0 AFTER `partner_id`;",
                "ALTER TABLE `cp_companies` ADD COLUMN IF NOT EXISTS `inactive_since` DATE DEFAULT NULL AFTER `expires_at`;",
                "ALTER TABLE `cp_companies` ADD COLUMN IF NOT EXISTS `trashed_at` DATETIME DEFAULT NULL AFTER `inactive_since`;"
            ]
        ],
        [
            'id' => 10,
            'title' => 'Create SaaS Invoices Table',
            'sql' => [
                "CREATE TABLE IF NOT EXISTS `cp_invoices` (
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
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;"
            ]
        ],
        [
            'id' => 11,
            'title' => 'Adding Logo and Background to Companies',
            'sql' => [
                "ALTER TABLE `cp_companies` ADD COLUMN IF NOT EXISTS `logo` VARCHAR(255) DEFAULT NULL AFTER `theme`;",
                "ALTER TABLE `cp_companies` ADD COLUMN IF NOT EXISTS `background_image` VARCHAR(255) DEFAULT NULL AFTER `logo`;"
            ]
        ],
        [
            'id' => 12,
            'title' => 'Add individual login theme support to companies and settings',
            'sql' => [
                "ALTER TABLE `cp_companies` ADD COLUMN IF NOT EXISTS `login_theme` VARCHAR(50) DEFAULT NULL AFTER `theme`;",
                "INSERT IGNORE INTO `cp_settings` (`setting_key`, `setting_value`) VALUES ('system_login_theme', 'gold-black');"
            ]
        ]
    ];

    // ── Execution Loop ───────────────────────────────────────────
    foreach ($migrations as $mig) {
        $mid    = $mig['id'];
        $title  = $mig['title'];

        // Check if already executed
        $check = $pdo->prepare("SELECT COUNT(*) FROM cp_migrations WHERE migration_id = ?");
        $check->execute([$mid]);
        if ($check->fetchColumn() > 0) {
            echo sprintf("⏭️  MIG #%d [%s] já executada. Pulando.\n", $mid, $title);
            continue;
        }

        try {
            echo sprintf("⚙️  Executando MIG #%d [%s]...", $mid, $title);
            
            // Execute each statement
            foreach ($mig['sql'] as $stmt) {
                if (!empty(trim($stmt))) {
                    $pdo->exec($stmt);
                }
            }

            // Record execution
            $pdo->prepare("INSERT INTO cp_migrations (migration_id, title) VALUES (?, ?)")
                ->execute([$mid, $title]);

            echo " ✅ SUCESSO!\n";
        } catch (\PDOException $e) {
            echo " ❌ ERRO: " . $e->getMessage() . "\n";
            // If one migration fails, we continue to others, but you might want to stop
        }
    }

    echo "\n🏆 Todas as migrações concluídas.\n";
    echo "</pre>";

} catch (\Exception $e) {
    echo "🚨 Erro fatal durante a migração: " . $e->getMessage() . "\n";
}
