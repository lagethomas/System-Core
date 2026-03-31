<?php
declare(strict_types=1);

/**
 * Migration: Add Email Confirmations Table
 */

require_once __DIR__ . '/../includes/DB.php';

$pdo = \DB::getInstance();

try {
    echo "Starting email confirmation migration...\n";

    $pdo->exec("CREATE TABLE IF NOT EXISTS `cp_email_confirmations` (
      `user_id` int(11) NOT NULL,
      `new_email` varchar(255) NOT NULL,
      `token` varchar(100) NOT NULL,
      `expires_at` timestamp NOT NULL,
      `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (`user_id`),
      UNIQUE KEY `idx_token` (`token`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    
    echo "Migration completed successfully!\n";
} catch (Exception $e) {
    echo "ERROR during migration: " . $e->getMessage() . "\n";
    exit(1);
}
