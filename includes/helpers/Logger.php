<?php
declare(strict_types=1);

/**
 * Logger - Global activity logger
 */
class Logger {
    public static function log(string $action, ?string $description = null): void {
        $pdo = DB::getInstance();
        
        $logRepo = new LogRepository($pdo);
        $data = [
            'user_id' => $_SESSION['user_id'] ?? 0,
            'action' => $action,
            'description' => $description,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? ''
        ];
        
        try {
            $logRepo->create($data);
        } catch (Exception $e) {
            // Failure to log shouldn't stop the system
            error_log("Failed to log action '$action': " . $e->getMessage());
        }
    }
}
