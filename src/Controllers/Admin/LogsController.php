<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use Auth;
use LogRepository;

class LogsController extends Controller {
    public function index(): void {
        Auth::requireAdmin();
        
        global $pdo;
        require_once __DIR__ . '/../../../includes/repositories/LogRepository.php';

        // --- CLEAR LOGS REQUEST (User Request) ---
        if (isset($_GET['clear']) && $_GET['clear'] === '1') {
            $pdo->exec("TRUNCATE TABLE cp_logs");
            header("Location: " . SITE_URL . "/logs?msg=cleared");
            exit;
        }
        
        $start_date = $_GET['start_date'] ?? '';
        $end_date = $_GET['end_date'] ?? '';
        $action_filter = $_GET['action'] ?? '';

        $logRepo = new LogRepository($pdo);
        $filters = [
            'start_date' => $start_date,
            'end_date' => $end_date,
            'action' => $action_filter
        ];

        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = 25;
        
        $totalLogs = $logRepo->countAll($filters);
        $logs = $logRepo->getPaginated($page, $perPage, $filters);
        $totalPages = (int)ceil($totalLogs / $perPage);

        $this->render('admin/logs', [
            'logs' => $logs,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'action_filter' => $action_filter,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalLogs' => $totalLogs
        ]);
    }
}
