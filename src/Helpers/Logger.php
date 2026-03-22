<?php
declare(strict_types=1);

namespace App\Helpers;

use App\Core\Database;

/**
 * Logger - Global activity logger
 */
class Logger {
    public static function log(string $action, ?string $description = null): void {
        try {
            Database::insert('cp_logs', [
                'user_id' => $_SESSION['user_id'] ?? 0,
                'action' => $action,
                'description' => $description,
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? ''
            ]);
        } catch (\Exception $e) {
            // Failure to log shouldn't stop the system
            error_log("Failed to log action '$action': " . $e->getMessage());
        }
    }
}
