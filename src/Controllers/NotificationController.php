<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;

class NotificationController extends Controller {
    public function read($id): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Método não permitido'], 405);
            return;
        }

        $user_id = (int)($_SESSION['user_id'] ?? 0);
        require_once __DIR__ . '/../../includes/repositories/NotificationRepository.php';
        $pdo = \App\Core\Database::getInstance();
        $notifRepo = new \NotificationRepository($pdo);
        
        $success = $notifRepo->markAsRead((int)$id, $user_id);
        
        try {
            require_once __DIR__ . '/../../includes/logs.php';
            \Logger::log('debug_notif', "Marcar como lido: ID=" . (string)$id . ", User=" . (string)$user_id . ", Sucesso=" . ($success?'SIM':'NAO'));
        } catch (\Exception $e) {}

        $this->jsonResponse(['success' => $success, 'message' => $success ? 'Lido' : 'Falha']);
    }

    public function readAll(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Método não permitido'], 405);
            return;
        }

        $user_id = (int)($_SESSION['user_id'] ?? 0);
        require_once __DIR__ . '/../../includes/repositories/NotificationRepository.php';
        $pdo = \App\Core\Database::getInstance();
        $notifRepo = new \NotificationRepository($pdo);
        
        $success = $notifRepo->markAllAsRead($user_id);

        try {
            require_once __DIR__ . '/../../includes/logs.php';
            \Logger::log('debug_notif', "Marcar todas como lidas: User=" . (string)$user_id . ", Sucesso=" . ($success?'SIM':'NAO'));
        } catch (\Exception $e) {}

        $this->jsonResponse(['success' => $success, 'message' => 'Todas lidas']);
    }
}
