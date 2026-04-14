<?php
declare(strict_types=1);

namespace App\Modules\Notifications\Controllers;

use App\Core\Controller;
use App\Core\Database;
use Auth;
use NotificationRepository;

/**
 * Modular Notifications Controller
 */
class NotificationsController extends Controller {

    /**
     * Mark a single notification as read (POST /api/notifications/read/{id})
     */
    public function read($id): void {
        Auth::requireLogin();
        
        $user_id = (int)$_SESSION['user_id'];
        $pdo = Database::getInstance();
        
        require_once dirname(dirname(dirname(dirname(__DIR__)))) . '/includes/repositories/NotificationRepository.php';
        $notifRepo = new NotificationRepository($pdo);
        
        $success = $notifRepo->markAsRead((int)$id, $user_id);
        
        $this->jsonResponse(['success' => $success, 'message' => $success ? 'Notificação lida' : 'Falha ao atualizar']);
    }

    /**
     * Mark all user notifications as read (POST /api/notifications/read-all)
     */
    public function readAll(): void {
        Auth::requireLogin();
        
        $user_id = (int)$_SESSION['user_id'];
        $pdo = Database::getInstance();
        
        require_once dirname(dirname(dirname(dirname(__DIR__)))) . '/includes/repositories/NotificationRepository.php';
        $notifRepo = new NotificationRepository($pdo);
        
        $success = $notifRepo->markAllAsRead($user_id);

        $this->jsonResponse(['success' => $success, 'message' => 'Todas as notificações marcadas como lidas']);
    }

    /**
     * Get unread notifications for active user (GET /api/notifications/unread)
     */
    public function unread(): void {
        Auth::requireLogin();
        
        $user_id = (int)$_SESSION['user_id'];
        $pdo = Database::getInstance();
        
        require_once dirname(dirname(dirname(dirname(__DIR__)))) . '/includes/repositories/NotificationRepository.php';
        $notifRepo = new NotificationRepository($pdo);
        
        $notifications = (array)$notifRepo->getUnreadByUser($user_id);
        
        foreach ($notifications as &$n) {
            $n['time_ago'] = date('d/m H:i', strtotime($n['created_at']));
            $n['icon'] = 'info-circle'; // UI handles Lucide name
            switch($n['type'] ?? '') {
                case 'success': $n['icon'] = 'check-circle'; break;
                case 'warning': $n['icon'] = 'alert-triangle'; break;
                case 'danger':  $n['icon'] = 'alert-circle'; break;
            }
        }

        $this->jsonResponse(['success' => true, 'notifications' => $notifications]);
    }
}
