<?php
declare(strict_types=1);

class NotificationRepository {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function create(array $data): bool {
        $stmt = $this->pdo->prepare("
            INSERT INTO cp_notifications (user_id, title, message, link, type)
            VALUES (?, ?, ?, ?, ?)
        ");
        return $stmt->execute([
            $data['user_id'],
            $data['title'],
            $data['message'],
            $data['link'] ?? null,
            $data['type'] ?? 'info'
        ]);
    }

    public function getUnreadByUser(int $user_id): array {
        $stmt = $this->pdo->prepare("
            SELECT * FROM cp_notifications 
            WHERE user_id = ? AND is_read = 0 
            ORDER BY created_at DESC 
            LIMIT 20
        ");
        $stmt->execute([$user_id]);
        return $stmt->fetchAll();
    }

    public function markAsRead(int $id, int $user_id): bool {
        $stmt = $this->pdo->prepare("UPDATE cp_notifications SET is_read = 1 WHERE id = ? AND user_id = ?");
        $success = $stmt->execute([$id, $user_id]);
        $rows = $stmt->rowCount();
        
        try {
            if (class_exists('Logger')) {
                \Logger::log('debug_notif', "markAsRead Repository: ID=" . (string)$id . ", User=" . (string)$user_id . ", RowsAffected=" . (string)$rows);
            }
        } catch (\Exception $e) {}
        
        return $success && $rows > 0;
    }

    public function markAllAsRead(int $user_id): bool {
        $stmt = $this->pdo->prepare("UPDATE cp_notifications SET is_read = 1 WHERE user_id = ?");
        return $stmt->execute([$user_id]);
    }

    public function delete(int $id, int $user_id): bool {
        $stmt = $this->pdo->prepare("DELETE FROM cp_notifications WHERE id = ? AND user_id = ?");
        return $stmt->execute([$id, $user_id]);
    }

    public function clearAllByUser(int $user_id): bool {
        $stmt = $this->pdo->prepare("DELETE FROM cp_notifications WHERE user_id = ?");
        return $stmt->execute([$user_id]);
    }
}
