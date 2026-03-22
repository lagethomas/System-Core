<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

class Auth {
    /**
     * Check if user is logged in and session is valid
     */
    public static function isLoggedIn(): bool {
        if (!isset($_SESSION['user_id'])) {
            return false;
        }

        // --- SESSION HIJACKING PROTECTION (Rule 6) ---
        // Validate User-Agent and IP to prevent stolen session usage
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $userIp = $_SERVER['REMOTE_ADDR'] ?? '';

        if (!isset($_SESSION['secure_ua']) || !isset($_SESSION['secure_ip'])) {
            // First time check - should have been set at login
            return false; 
        }

        if ($_SESSION['secure_ua'] !== $userAgent || $_SESSION['secure_ip'] !== $userIp) {
            self::logout();
            return false;
        }

        return true;
    }

    /**
     * Check if user is a global administrator
     */
    public static function isAdmin(): bool {
        return self::isLoggedIn() && isset($_SESSION['user_role']) && strtolower($_SESSION['user_role']) === 'administrador';
    }

    public static function isCaixa(): bool {
        return self::isAdmin() || (isset($_SESSION['user_role']) && strtolower($_SESSION['user_role']) === 'caixa');
    }

    public static function isAtendente(): bool {
        return self::isCaixa() || (isset($_SESSION['user_role']) && strtolower($_SESSION['user_role']) === 'atendente');
    }

    public static function hasPermission(string $roleRequired): bool {
        if ($roleRequired === 'administrador') return self::isAdmin();
        if ($roleRequired === 'caixa') return self::isCaixa();
        if ($roleRequired === 'atendente') return self::isAtendente();
        return false;
    }

    /**
     * Redirect if not logged in
     */
    public static function requireLogin(): void {
        if (!self::isLoggedIn()) {
            header("Location: " . SITE_URL . "/login");
            exit;
        }
    }

    /**
     * Require Specific Role access
     */
    public static function requireRole(string $role): void {
        self::requireLogin();
        if (!self::hasPermission($role)) {
            header("Location: " . SITE_URL . "/dashboard");
            exit;
        }
    }

    /**
     * Require Global Admin access
     */
    public static function requireAdmin(): void {
        self::requireRole('administrador');
    }

    /**
     * Login user and initialize secure session markers
     */
    public static function login(array $user): void {
        // Force session ID regeneration on login
        session_regenerate_id(true);

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['last_activity'] = time();

        // Security markers
        $_SESSION['secure_ua'] = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $_SESSION['secure_ip'] = $_SERVER['REMOTE_ADDR'] ?? '';

        // Update last login and session ID in DB
        global $pdo;
        $stmt = $pdo->prepare("UPDATE cp_users SET last_login = NOW(), current_session_id = ?, last_pulse = NOW() WHERE id = ?");
        $stmt->execute([session_id(), $user['id']]);
    }

    /**
     * Single Session Enforcement: Check if user already has an active session elsewhere
     */
    public static function hasActiveSession(int $userId): bool {
        global $pdo;
        $stmt = $pdo->prepare("SELECT current_session_id, last_pulse FROM cp_users WHERE id = ?");
        $stmt->execute([$userId]);
        $res = $stmt->fetch();

        if ($res && !empty($res['current_session_id'])) {
            // Check if session is actually still active (last pulse < 10 mins)
            $lastPulse = strtotime($res['last_pulse'] ?? '0');
            if ((time() - $lastPulse) < 600) {
                // If it's the SAME session, don't block
                if ($res['current_session_id'] === session_id()) {
                    return false;
                }
                return true;
            }
        }
        return false;
    }

    public static function clearSessionFromDB(int $userId): void {
        global $pdo;
        $stmt = $pdo->prepare("UPDATE cp_users SET current_session_id = NULL WHERE id = ?");
        $stmt->execute([$userId]);
    }

    /**
     * Logout user and clear session
     */
    public static function logout(): void {
        session_unset();
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
        
        if (!headers_sent()) {
            header("Location: " . SITE_URL . "/login.php");
            exit;
        }
    }


    /**
     * Check for session inactivity (2 hours)
     */
    public static function checkInactivity(): void {
        if (!isset($_SESSION['user_id'])) return;

        $timeout = 7200; // 2 hours
        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $timeout)) {
            self::logout();
        }
        $_SESSION['last_activity'] = time();
    }

    /**
     * Get current user ID
     */
    public static function id(): ?int {
        return isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
    }
}

// Global inactivity check
Auth::checkInactivity();
