<?php
declare(strict_types=1);

namespace App\Modules\Dashboard\Controllers;

use App\Core\Controller;
use Auth;
use App\Core\Database;

/**
 * DashboardController (Modular Architecture)
 * Centralizes the main overview logic for administrators and users.
 */
class DashboardController extends Controller {
    
    /**
     * Main Dashboard View
     */
    public function index(): void {
        $user_name = $_SESSION['user_name'] ?? 'Usuário';
        $total_users = 0;
        $total_logs = 0;

        try {
            $total_users = Database::fetch("SELECT COUNT(*) as total FROM cp_users")['total'] ?? 0;
            $total_logs = Database::fetch("SELECT COUNT(*) as total FROM cp_logs")['total'] ?? 0;
        } catch (\Exception $e) {
            // Error handling for initial setup/database issues
            error_log("Dashboard Data Error: " . $e->getMessage());
        }

        // Module-aware render will look for src/Modules/Dashboard/Views/index.php
        $this->render('index', [
            'user_name'   => $user_name,
            'is_admin'    => Auth::isAdmin(),
            'total_users' => $total_users,
            'total_logs'  => $total_logs
        ]);
    }
}
