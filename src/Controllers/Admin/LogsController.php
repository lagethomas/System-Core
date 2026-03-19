<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use Auth;
use LogRepository;

class LogsController extends Controller {
    public function index() {
        Auth::requireAdmin();
        
        global $pdo;
        require_once __DIR__ . '/../../../includes/repositories/LogRepository.php';
        
        $start_date = $_GET['start_date'] ?? '';
        $end_date = $_GET['end_date'] ?? '';
        $action_filter = $_GET['action'] ?? '';

        $logRepo = new LogRepository($pdo);
        $filters = [
            'start_date' => $start_date,
            'end_date' => $end_date,
            'action' => $action_filter
        ];

        $logs = $logRepo->getAll($filters, 500);

        return $this->render('admin/logs', [
            'logs' => $logs,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'action_filter' => $action_filter
        ]);
    }
}
