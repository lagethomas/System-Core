<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;

class NotificationController extends Controller {
    public function read(int $id) {
        $user_id = (int)$_SESSION['user_id'];
        require_once __DIR__ . '/../../includes/repositories/NotificationRepository.php';
        $notifRepo = new \NotificationRepository(\App\Core\Database::getInstance());
        
        $notifRepo->markAsRead($id, $user_id);
        return $this->jsonResponse(['success' => true, 'message' => 'Lido']);
    }

    public function readAll() {
        $user_id = (int)$_SESSION['user_id'];
        require_once __DIR__ . '/../../includes/repositories/NotificationRepository.php';
        $notifRepo = new \NotificationRepository(\App\Core\Database::getInstance());
        
        $notifRepo->markAllAsRead($user_id);
        return $this->jsonResponse(['success' => true, 'message' => 'Todas lidas']);
    }
}
