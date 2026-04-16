<?php
declare(strict_types=1);

namespace App\Modules\Logs\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Pagination;
use Auth;

/**
 * System Logs Management Controller (Modular)
 */
class LogsController extends Controller {
    
    public function index(): void {
        Auth::requireAdmin();
        
        $pdo = Database::getInstance();
        require_once __DIR__ . '/../../../../includes/repositories/LogRepository.php';

        $start_date = $_GET['start_date'] ?? '';
        $end_date = $_GET['end_date'] ?? '';
        $action_filter = $_GET['action'] ?? '';

        $logRepo = new \LogRepository($pdo);
        $filters = [
            'start_date' => $start_date,
            'end_date' => $end_date,
            'action' => $action_filter
        ];

        global $platform_settings;
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = (int)($platform_settings['items_per_page'] ?? 25);
        
        $totalLogs = $logRepo->countAll($filters);
        $logs = $logRepo->getPaginated($page, $perPage, $filters);
        $totalPages = (int)ceil($totalLogs / $perPage);

        $this->render('index', [
            'logs' => $logs,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'action_filter' => $action_filter,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalLogs' => $totalLogs
        ]);
    }

    /**
     * Purge all logs via AJAX
     */
    public function clear(): void {
        Auth::requireAdmin();
        
        try {
            $pdo = Database::getInstance();
            require_once __DIR__ . '/../../../../includes/repositories/LogRepository.php';
            $logRepo = new \LogRepository($pdo);
            $logRepo->truncate();
            
            require_once __DIR__ . '/../../../../includes/logs.php';
            \Logger::log('logs_purge', 'O histórico de logs do sistema foi limpo pelo administrador.');
            
            $this->jsonResponse(['success' => true, 'message' => 'Histórico de logs removido com sucesso!']);
        } catch (\Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => 'Erro ao limpar logs: ' . $e->getMessage()], 500);
        }
    }
}
