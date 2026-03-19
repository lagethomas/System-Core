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
        return self::isLoggedIn() && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'administrador';
    }

    /**
     * Redirect if not logged in
     */
    public static function requireLogin(): void {
        if (!self::isLoggedIn()) {
            header("Location: " . SITE_URL . "/login.php");
            exit;
        }
    }

    /**
     * Require Global Admin access
     */
    public static function requireAdmin(): void {
        self::requireLogin();
        if (!self::isAdmin()) {
            header("Location: " . SITE_URL . "/dashboard");
            exit;
        }
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
    }

    /**
     * Logout user and clear session
     */
<<<<<<< HEAD:includes/auth.php
    public static function logout() {
=======
    public static function logout(): void {
        $is_subfolder = (strpos($_SERVER['PHP_SELF'], '/admin/') !== false || 
                         strpos($_SERVER['PHP_SELF'], '/api/') !== false || 
                         strpos($_SERVER['PHP_SELF'], '/app/') !== false);
        $prefix = $is_subfolder ? '../' : '';
        
>>>>>>> ab660bf99d6d155d59d9302691d0bc8f9c62eeb9:includes/helpers/Auth.php
        session_unset();
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
        
<<<<<<< HEAD:includes/auth.php
        header("Location: " . SITE_URL . "/login.php");
        exit;
=======
        if (!headers_sent()) {
            header("Location: {$prefix}login.php");
            exit;
        }
>>>>>>> ab660bf99d6d155d59d9302691d0bc8f9c62eeb9:includes/helpers/Auth.php
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
}

// Global inactivity check
Auth::checkInactivity();
