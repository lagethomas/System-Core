<?php
declare(strict_types=1);

/**
 * Migration: Add Security Infrastructure
 * Adds cp_login_attempts and cp_blocked_ips tables
 */

require_once __DIR__ . '/../includes/DB.php';

$pdo = \DB::getInstance();

try {
    echo "Starting security migration...\n";

    // 1. Create cp_login_attempts
    $pdo->exec("CREATE TABLE IF NOT EXISTS `cp_login_attempts` (
      `ip_address` varchar(45) NOT NULL,
      `attempts` int(11) NOT NULL DEFAULT '0',
      `last_attempt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      PRIMARY KEY (`ip_address`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    echo "- Table cp_login_attempts created/verified.\n";

    // 2. Create cp_blocked_ips
    $pdo->exec("CREATE TABLE IF NOT EXISTS `cp_blocked_ips` (
      `ip_address` varchar(45) NOT NULL,
      `reason` varchar(255) DEFAULT NULL,
      `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (`ip_address`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    echo "- Table cp_blocked_ips created/verified.\n";

    echo "Migration completed successfully!\n";
} catch (Exception $e) {
    echo "ERROR during migration: " . $e->getMessage() . "\n";
    exit(1);
}
