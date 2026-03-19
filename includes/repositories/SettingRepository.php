<?php
declare(strict_types=1);

class SettingRepository {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Get all settings
     */
    public function getAll(): array {
        $stmt = $this->pdo->prepare("SELECT setting_key, setting_value FROM cp_settings");
        $stmt->execute();
        
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $settings = [];
        foreach ($results as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        return $settings;
    }

    /**
     * Get a specific setting by key
     */
    public function getByKey(string $key, $default = null): mixed {
        $stmt = $this->pdo->prepare("SELECT setting_value FROM cp_settings WHERE setting_key = ?");
        $stmt->execute([$key]);
        $value = $stmt->fetchColumn();
        return ($value !== false) ? $value : $default;
    }

    /**
     * Save/Update a setting
     */
    public function save(string $key, string $value): bool {
        $stmt = $this->pdo->prepare("
            INSERT INTO cp_settings (setting_key, setting_value) 
            VALUES (?, ?) 
            ON DUPLICATE KEY UPDATE setting_value = ?
        ");
        return $stmt->execute([$key, $value, $value]);
    }

    /**
     * Save multiple settings at once
     */
    public function saveMultiple(array $settings): bool {
        foreach ($settings as $key => $value) {
            $this->save((string)$key, (string)$value);
        }
        return true;
    }
}
