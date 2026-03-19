<?php
declare(strict_types=1);
/**
 * CSRF Protection
 */
class CSRF {
    public static function generateToken() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Verifica o token CSRF.
     * Aceita via:
     *   - $token / $_POST['csrf_token']  (formulários tradicionais)
     *   - Header HTTP X-CSRF-Token       (chamadas fetch/AJAX)
     */
    public static function verifyToken(?string $token = null): bool {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        // Se não passado explicitamente, tenta pegar do header HTTP (AJAX)
        if ($token === null || $token === '') {
            $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        }
        if (empty($_SESSION['csrf_token']) || empty($token)) {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $token);
    }
}
