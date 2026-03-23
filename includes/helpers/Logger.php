<?php
declare(strict_types=1);

/**
 * Logger - Global activity logger
 */
class Logger {
    public static function log(string $action, ?string $description = null): void {
        // --- REDIRECT DEBUG LOGS TO DISK (logs/php_errors.log via error_log) ---
        if (strpos($action, 'debug_') === 0) {
            $user_id = $_SESSION['user_id'] ?? 'Guest';
            $ip = $_SERVER['REMOTE_ADDR'] ?? 'CLI';
            error_log("DEBUG: [Action: $action] [User: $user_id] [IP: $ip] Description: $description");
            return;
        }

        $pdo = \DB::getInstance();
        $logRepo = new LogRepository($pdo);
        $data = [
            'user_id' => $_SESSION['user_id'] ?? 0,
            'action' => $action,
            'description' => $description,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? ''
        ];
        
        try {
            $logRepo->create($data);

            // AUTO-CLEANUP (Rule 39): Run once every ~100 log operations
            if (mt_rand(1, 100) === 1) {
                global $platform_settings;
                $days = (int)($platform_settings['security_log_days'] ?? 30);
                $limit = (int)($platform_settings['security_log_limit'] ?? 10000);
                $logRepo->cleanup($days, $limit);
            }
        } catch (Exception $e) {
            error_log("Failed to log action '$action' or cleanup: " . $e->getMessage());
        }
    }
}
