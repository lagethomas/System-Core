<?php
declare(strict_types=1);

class UserRepository {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Get user by ID
     */
    public function getById(int $id): ?array {
        $stmt = $this->pdo->prepare("SELECT * FROM cp_users WHERE id = ?");
        $stmt->execute([$id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return $user ?: null;
    }

    /**
     * Get user by Email
     */
    public function getByEmail(string $email): ?array {
        $stmt = $this->pdo->prepare("SELECT * FROM cp_users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return $user ?: null;
    }

    /**
     * Get user by Username
     */
    public function getByUsername(string $username): ?array {
        $stmt = $this->pdo->prepare("SELECT * FROM cp_users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return $user ?: null;
    }

    /**
     * Get all users
     */
    public function getAll(): array {
        $stmt = $this->pdo->prepare("
            SELECT u.*, creator.name as creator_name
            FROM cp_users u 
            LEFT JOIN cp_users creator ON u.created_by = creator.id
            ORDER BY u.name ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Save or update a user
     */
    public function save(array $data): bool {
        if (isset($data['id']) && $data['id']) {
            $sql = "UPDATE cp_users SET name = ?, email = ?, role = ?, city = ?, state = ?, zip_code = ?, street = ?, neighborhood = ?, address_number = ?, phone = ?, avatar = ?";
            $params = [
                $data['name'], 
                $data['email'], 
                $data['role'], 
                $data['city'] ?? null,
                $data['state'] ?? null,
                $data['zip_code'] ?? null,
                $data['street'] ?? null,
                $data['neighborhood'] ?? null,
                $data['address_number'] ?? null,
                $data['phone'] ?? null,
                $data['avatar'] ?? null
            ];
            
            if (!empty($data['password'])) {
                $sql .= ", password = ?";
                $params[] = password_hash($data['password'], PASSWORD_DEFAULT);
            }
            
            $sql .= " WHERE id = ?";
            $params[] = $data['id'];
        } else {
            $sql = "INSERT INTO cp_users (name, username, email, password, role, created_by, city, state, zip_code, street, neighborhood, address_number, phone, avatar) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $params = [
                $data['name'], 
                $data['username'],
                $data['email'], 
                password_hash($data['password'], PASSWORD_DEFAULT), 
                $data['role'], 
                $data['created_by'] ?? null,
                $data['city'] ?? null,
                $data['state'] ?? null,
                $data['zip_code'] ?? null,
                $data['street'] ?? null,
                $data['neighborhood'] ?? null,
                $data['address_number'] ?? null,
                $data['phone'] ?? null,
                $data['avatar'] ?? null
            ];
        }
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Delete a user
     */
    public function delete(int $id): bool {
        $stmt = $this->pdo->prepare("DELETE FROM cp_users WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Update last login
     */
    public function updateLastLogin(int $id): bool {
        $stmt = $this->pdo->prepare("UPDATE cp_users SET last_login = NOW() WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
