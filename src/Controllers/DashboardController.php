<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use Auth;
use PDO;

class DashboardController extends Controller {
    public function index(): void {
        // --- TEMPORARY MIGRATION (Rule 39 Logic) ---
        try {
            $pdo = \App\Core\Database::getInstance();
            // Check if column exists, if not add them
            $stmt = $pdo->query("SHOW COLUMNS FROM cp_users LIKE 'last_pulse'");
            if (!$stmt->fetch()) {
                $pdo->exec("ALTER TABLE cp_users ADD COLUMN current_session_id varchar(255) DEFAULT NULL COMMENT 'Active session ID for single-session enforcement'");
                $pdo->exec("ALTER TABLE cp_users ADD COLUMN last_pulse datetime DEFAULT NULL COMMENT 'Last user activity heartbeat'");
            }
        } catch (\Exception $e) {
            error_log("Dashboard Migration Note: " . $e->getMessage());
        }
        // ------------------------------------------

        $user_name = $_SESSION['user_name'] ?? 'Usuário';
        $total_users = 0;
        $total_logs = 0;

        try {
            $total_users = \App\Core\Database::fetch("SELECT COUNT(*) as total FROM cp_users")['total'] ?? 0;
            $total_logs = \App\Core\Database::fetch("SELECT COUNT(*) as total FROM cp_logs")['total'] ?? 0;
        } catch (\Exception $e) {}

        $this->render('app/dashboard', [
            'user_name' => $user_name,
            'is_admin' => Auth::isAdmin(),
            'total_users' => $total_users,
            'total_logs' => $total_logs
        ]);
    }
}
