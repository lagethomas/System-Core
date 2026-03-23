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

        // --- SESSION HIJACKING PROTECTION ---
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $userIp = $_SERVER['REMOTE_ADDR'] ?? '';

        if (!isset($_SESSION['secure_ua']) || !isset($_SESSION['secure_ip'])) {
            return false;
        }

        if ($_SESSION['secure_ua'] !== $userAgent || $_SESSION['secure_ip'] !== $userIp) {
            // Destroy LOCAL session only - do NOT clear DB session_id
            // so the hasActiveSession check still works from another device
            session_unset();
            if (ini_get('session.use_cookies')) {
                $params = session_get_cookie_params();
                setcookie(session_name(), '', time() - 42000,
                    $params['path'], $params['domain'],
                    $params['secure'], $params['httponly']
                );
            }
            session_destroy();
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
            header("Location: " . SITE_URL . "/login");
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

    public static function login(array $user): void {
        // 1. Set session data FIRST
        $_SESSION['user_id']       = $user['id'];
        $_SESSION['user_name']     = $user['name'];
        $_SESSION['user_email']    = $user['email'];
        $_SESSION['user_role']     = $user['role'];
        $_SESSION['last_activity'] = time();
        $_SESSION['secure_ua']     = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $_SESSION['secure_ip']     = $_SERVER['REMOTE_ADDR'] ?? '';

        // 2. Regenerate session ID for security (keeps existing data)
        session_regenerate_id(true);

        // 3. Save new session ID to DB for single-session enforcement
        $sessionId = session_id();
        try {
            $pdo = \DB::getInstance();
            $rowsAffected = $pdo->prepare('UPDATE cp_users SET current_session_id = ?, last_pulse = NOW() WHERE id = ?');
            $rowsAffected->execute([$sessionId, $user['id']]);

            try {
                if (class_exists('Logger')) {
                    Logger::log('debug_session', "User ID {$user['id']} logado com Session ID: $sessionId (File: " . __FILE__ . ")");
                }
            } catch (\Exception $e) {}
        } catch (\Exception $e) {
            error_log("Auth::login DB update failed: " . $e->getMessage());
            try {
                if (class_exists('Logger')) {
                    Logger::log('debug_session_error', "Auth::login DB FALHOU para User ID {$user['id']}: " . $e->getMessage());
                }
            } catch (\Exception $ex) {}
        }
    }

    /**
     * Logout user and clear session
     */
    public static function logout(): void {
        // Clear from DB if logged in
        if (isset($_SESSION['user_id'])) {
            self::clearSessionFromDB((int)$_SESSION['user_id']);
        }

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
            header("Location: " . SITE_URL . "/login");
            exit;
        }
    }


    /**
     * Check for session inactivity (from system settings or 2 hours default)
     */
    public static function checkInactivity(): void {
        if (!isset($_SESSION['user_id'])) return;

        global $platform_settings;
        $timeout = (int)($platform_settings['security_session_timeout'] ?? 120) * 60; // Value in minutes

        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $timeout)) {
            self::logout();
        }
        $_SESSION['last_activity'] = time();
    }

    public static function hasActiveSession(int $userId): bool {
        try {
            $pdo = \DB::getInstance();
            // A session is only active if it has pulsed in the last 5 minutes
            $stmt = $pdo->prepare("
                SELECT current_session_id, last_pulse
                FROM cp_users
                WHERE id = ?
                  AND current_session_id IS NOT NULL
                  AND last_pulse >= (NOW() - INTERVAL 5 MINUTE)
            ");
            $stmt->execute([$userId]);
            $res = $stmt->fetch();

            $isActive = (bool)($res && !empty($res['current_session_id']));

            try {
                if (class_exists('Logger')) {
                    $status = $isActive ? "BLOQUEADO" : "LIVRE";
                    $detail = $isActive ? "Sessão detectada: {$res['current_session_id']} (Pulso: {$res['last_pulse']})" : "Nenhuma sessão ativa recente.";
                    \Logger::log('debug_session', "hasActiveSession User ID " . (string)$userId . ": $status. $detail");
                }
            } catch (\Exception $e) {}

            return $isActive;
        } catch (\Exception $e) {
            error_log("Auth::hasActiveSession failed: " . $e->getMessage());
        }
        return false;
    }

    /**
     * Clear session from DB
     */
    public static function clearSessionFromDB(int $userId): void {
        $pdo = \DB::getInstance();
        $stmt = $pdo->prepare('UPDATE cp_users SET current_session_id = NULL WHERE id = ?');
        $stmt->execute([$userId]);
    }

    /**
     * Check if IP is permanently blocked
     */
    public static function isIpBlocked(?string $ip = null): bool {
        $ip = $ip ?? $_SERVER['REMOTE_ADDR'] ?? '';
        $pdo = \DB::getInstance();
        $stmt = $pdo->prepare('SELECT 1 FROM cp_blocked_ips WHERE ip_address = ?');
        $stmt->execute([$ip]);
        return (bool)$stmt->fetch();
    }

    /**
     * Check Brute Force Protection
     */
    public static function checkBruteForce(?string $ip = null): bool {
        global $platform_settings;
        $max = (int)($platform_settings['security_max_attempts'] ?? 5);
        $time = (int)($platform_settings['security_lockout_time'] ?? 15);

        $ip = $ip ?? $_SERVER['REMOTE_ADDR'] ?? '';
        $pdo = \DB::getInstance();
        $stmt = $pdo->prepare('SELECT attempts, last_attempt FROM cp_login_attempts WHERE ip_address = ?');
        $stmt->execute([$ip]);
        $data = $stmt->fetch();

        if ($data && $data['attempts'] >= $max) {
            $lastAttempt = strtotime($data['last_attempt']);
            if ((time() - $lastAttempt) < ($time * 60)) {
                return false; // Blocked
            } else {
                self::resetAttempts($ip);
            }
        }
        return true;
    }

    public static function registerFailedAttempt(?string $ip = null): void {
        $ip = $ip ?? $_SERVER['REMOTE_ADDR'] ?? '';
        $pdo = \DB::getInstance();
        $stmt = $pdo->prepare('INSERT INTO cp_login_attempts (ip_address, attempts) VALUES (?, 1) 
                               ON DUPLICATE KEY UPDATE attempts = attempts + 1, last_attempt = CURRENT_TIMESTAMP');
        $stmt->execute([$ip]);
    }

    public static function resetAttempts(?string $ip = null): void {
        $ip = $ip ?? $_SERVER['REMOTE_ADDR'] ?? '';
        $pdo = \DB::getInstance();
        $stmt = $pdo->prepare('DELETE FROM cp_login_attempts WHERE ip_address = ?');
        $stmt->execute([$ip]);
    }
}

// Global inactivity check
try {
    Auth::checkInactivity();
} catch (\Exception $e) {
    error_log("Auth::checkInactivity failed: " . $e->getMessage());
}
