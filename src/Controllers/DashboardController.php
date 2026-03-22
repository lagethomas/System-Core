<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use Auth;
use PDO;

class DashboardController extends Controller {
    public function index(): void {
        try {
            \App\Core\Database::query("ALTER TABLE cp_produtos ADD COLUMN disponivel_cardapio TINYINT DEFAULT 1");
        } catch (\Exception $e) {}
        try {
            \App\Core\Database::query("ALTER TABLE cp_produtos ADD COLUMN descricao TEXT");
        } catch (\Exception $e) {}
        try {
            \App\Core\Database::query("ALTER TABLE cp_users MODIFY COLUMN role VARCHAR(50) DEFAULT 'usuario'");
        } catch (\Exception $e) {}
        try {
            \App\Core\Database::query("ALTER TABLE cp_financeiro ADD COLUMN taxa_servico DECIMAL(10, 2) DEFAULT 0.00");
        } catch (\Exception $e) {}
        try {
            \App\Core\Database::query("ALTER TABLE cp_financeiro ADD COLUMN cover DECIMAL(10, 2) DEFAULT 0.00");
        } catch (\Exception $e) {}

        $user_name = $_SESSION['user_name'] ?? 'Usuário';
        $total_users = 0;
        $total_logs = 0;

        try {
            $total_users = \App\Core\Database::fetch("SELECT COUNT(*) as total FROM cp_users")['total'] ?? 0;
            $total_logs = \App\Core\Database::fetch("SELECT COUNT(*) as total FROM cp_logs")['total'] ?? 0;
        } catch (\Exception $e) {}

        $this->render('app/dashboard', [
            'user_name' => $user_name,
            'total_users' => $total_users,
            'total_logs' => $total_logs
        ]);
    }
}
