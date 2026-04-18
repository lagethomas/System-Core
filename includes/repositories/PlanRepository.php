<?php
declare(strict_types=1);

class PlanRepository {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Get all plans with pagination
     */
    public function getAll(int $limit = 100, int $offset = 0) {
        $stmt = $this->pdo->prepare("SELECT * FROM cp_plans ORDER BY name ASC LIMIT ? OFFSET ?");
        $stmt->execute([$limit, $offset]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countAll(): int {
        return (int)$this->pdo->query("SELECT COUNT(*) FROM cp_plans")->fetchColumn();
    }

    /**
     * Get plan by ID
     */
    public function getById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM cp_plans WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Save/Update plan
     */
    public function save($data) {
        if (isset($data['id']) && $data['id']) {
            $stmt = $this->pdo->prepare("
                UPDATE cp_plans SET 
                name = ?, base_price = ?, included_users = ?, extra_user_price = ?, trial_days = ?
                WHERE id = ?
            ");
            return $stmt->execute([
                $data['name'], $data['base_price'], $data['included_users'], $data['extra_user_price'], $data['trial_days'] ?? 7, 
                $data['id']
            ]);
        } else {
            $stmt = $this->pdo->prepare("
                INSERT INTO cp_plans (name, base_price, included_users, extra_user_price, trial_days) 
                VALUES (?, ?, ?, ?, ?)
            ");
            return $stmt->execute([
                $data['name'], $data['base_price'], $data['included_users'], $data['extra_user_price'], $data['trial_days'] ?? 7
            ]);
        }
    }

    /**
     * Delete plan
     */
    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM cp_plans WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
