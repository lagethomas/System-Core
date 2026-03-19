<?php
declare(strict_types=1);

namespace App\Core;

use PDO;

class Database {
    private static ?PDO $instance = null;

    public static function getInstance(): PDO {
        global $pdo; // Use the existing global $pdo from includes/db.php
        if (self::$instance === null) {
            self::$instance = $pdo;
        }
        return self::$instance;
    }

    public static function query(string $sql, array $params = []) {
        $stmt = self::getInstance()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public static function fetch(string $sql, array $params = []) {
        return self::query($sql, $params)->fetch();
    }

    public static function fetchAll(string $sql, array $params = []) {
        return self::query($sql, $params)->fetchAll();
    }

    public static function insert(string $table, array $data): string {
        $keys = array_keys($data);
        $fields = implode(', ', $keys);
        $placeholders = ':' . implode(', :', $keys);

        $sql = "INSERT INTO $table ($fields) VALUES ($placeholders)";
        self::query($sql, $data);
        return self::getInstance()->lastInsertId();
    }

    public static function update(string $table, array $data, string $where, array $whereParams = []): bool {
        $fields = '';
        foreach ($data as $key => $value) {
            $fields .= "$key = :$key, ";
        }
        $fields = rtrim($fields, ', ');

        $sql = "UPDATE $table SET $fields WHERE $where";
        $params = array_merge($data, $whereParams);
        return self::query($sql, $params)->rowCount() > 0;
    }

    public static function delete(string $table, string $where, array $params = []): bool {
        $sql = "DELETE FROM $table WHERE $where";
        return self::query($sql, $params)->rowCount() > 0;
    }
}
