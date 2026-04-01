<?php
declare(strict_types=1);

namespace App\Helpers;

use App\Core\Database;

/**
 * Notification - Global notification helper for Multi-Tenancy (from System-Clinica)
 */
class Notification {
    /**
     * Create a notification for a single user
     */
    public static function create(int $userId, string $title, string $message, ?string $link = null, string $type = 'info'): bool {
        try {
            return Database::insert('cp_notifications', [
                'user_id' => $userId,
                'title' => $title,
                'message' => $message,
                'link' => $link,
                'type' => $type,
                'is_read' => 0,
                'created_at' => date('Y-m-d H:i:s')
            ]) > 0;
        } catch (\Exception $e) {
            error_log("Failed to create notification: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Create notification for all users of a company (except optional current user)
     */
    public static function forCompany(int $companyId, string $title, string $message, ?string $link = null, string $type = 'info', ?int $exceptUserId = null): void {
        try {
            $users = Database::fetchAll("SELECT id FROM cp_users WHERE company_id = :cid", ['cid' => $companyId]);
            if (!$users) return;
            foreach ($users as $u) {
                if ($exceptUserId && (int)$u['id'] === $exceptUserId) continue;
                self::create((int)$u['id'], $title, $message, $link, $type);
            }
        } catch (\Exception $e) {
            error_log("Failed to create company notifications: " . $e->getMessage());
        }
    }
}
