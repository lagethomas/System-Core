<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use Auth;
use PDO;

class DashboardController extends Controller {
    public function index() {
        $user_name = $_SESSION['user_name'] ?? 'Usuário';
        $total_users = 0;
        $total_logs = 0;

        try {
            $total_users = \App\Core\Database::fetch("SELECT COUNT(*) as total FROM cp_users")['total'] ?? 0;
            $total_logs = \App\Core\Database::fetch("SELECT COUNT(*) as total FROM cp_logs")['total'] ?? 0;
        } catch (\Exception $e) {}

        return $this->render('app/dashboard', [
            'user_name' => $user_name,
            'total_users' => $total_users,
            'total_logs' => $total_logs
        ]);
    }
}
