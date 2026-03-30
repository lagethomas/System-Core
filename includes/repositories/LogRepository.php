<?php
declare(strict_types=1);

class LogRepository {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Create a log entry
     */
    public function create(array $data): bool {
        $stmt = $this->pdo->prepare("INSERT INTO cp_logs (user_id, action, description, ip_address) VALUES (?, ?, ?, ?)");
        return $stmt->execute([
            $data['user_id'] ?? 0,
            $data['action'],
            $data['description'] ?? null,
            $data['ip_address'] ?? ''
        ]);
    }

    /**
     * Get all logs with filters
     */
    public function getAll(array $filters = [], int $limit = 200): array {
        $sql = "SELECT l.*, u.name as user_name 
                FROM cp_logs l 
                LEFT JOIN cp_users u ON l.user_id = u.id 
                WHERE 1=1 ";
        $params = [];

        if (!empty($filters['start_date'])) {
            $sql .= " AND l.created_at >= ? ";
            $params[] = $filters['start_date'] . ' 00:00:00';
        }
        if (!empty($filters['end_date'])) {
            $sql .= " AND l.created_at <= ? ";
            $params[] = $filters['end_date'] . ' 23:59:59';
        }
        if (!empty($filters['action'])) {
            $sql .= " AND l.action LIKE ? ";
            $params[] = "%" . $filters['action'] . "%";
        }
        if (!empty($filters['user_id'])) {
            $sql .= " AND l.user_id = ? ";
            $params[] = (int)$filters['user_id'];
        }

        $sql .= " ORDER BY l.created_at DESC LIMIT " . (int)$limit;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get logs with pagination
     */
    public function getPaginated(int $page = 1, int $perPage = 20, array $filters = []): array {
        $offset = ($page - 1) * $perPage;
        $sql = "SELECT l.*, u.name as user_name 
                FROM cp_logs l 
                LEFT JOIN cp_users u ON l.user_id = u.id 
                WHERE 1=1 ";
        $params = [];

        if (!empty($filters['start_date'])) {
            $sql .= " AND l.created_at >= ? ";
            $params[] = $filters['start_date'] . ' 00:00:00';
        }
        if (!empty($filters['end_date'])) {
            $sql .= " AND l.created_at <= ? ";
            $params[] = $filters['end_date'] . ' 23:59:59';
        }
        if (!empty($filters['action'])) {
            $sql .= " AND l.action LIKE ? ";
            $params[] = "%" . $filters['action'] . "%";
        }

        $sql .= " ORDER BY l.created_at DESC LIMIT ? OFFSET ?";
        $params[] = $perPage;
        $params[] = $offset;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Count total logs for pagination
     */
    public function countAll(array $filters = []): int {
        $sql = "SELECT COUNT(*) FROM cp_logs l WHERE 1=1 ";
        $params = [];

        if (!empty($filters['start_date'])) {
            $sql .= " AND l.created_at >= ? ";
            $params[] = $filters['start_date'] . ' 00:00:00';
        }
        if (!empty($filters['end_date'])) {
            $sql .= " AND l.created_at <= ? ";
            $params[] = $filters['end_date'] . ' 23:59:59';
        }
        if (!empty($filters['action'])) {
            $sql .= " AND l.action LIKE ? ";
            $params[] = "%" . $filters['action'] . "%";
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    /**
     * Cleanup logs based on retention settings (Rule 39)
     */
    public function cleanup(int $days, int $limit): void {
        // 1. Delete by age
        $stmt = $this->pdo->prepare("DELETE FROM cp_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)");
        $stmt->execute([$days]);

        // 2. Delete by count if exceeds limit (keep newest)
        $currentCount = $this->pdo->query("SELECT COUNT(*) FROM cp_logs")->fetchColumn();
        if ($currentCount > $limit) {
            $diff = $currentCount - $limit;
            $this->pdo->exec("DELETE FROM cp_logs ORDER BY created_at ASC LIMIT " . (int)$diff);
        }
    }
}
