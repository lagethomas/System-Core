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
}
