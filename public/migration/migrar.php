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
    $pdo = \DB::getInstance();

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
                ('login_background', NULL);"
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
