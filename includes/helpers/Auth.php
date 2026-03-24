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
            self::clearLocalSession();
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
            self::redirectOrJson('expired');
        }

        // Single Session Enforcement (Navigation block)
        if (self::isSessionReplaced()) {
            self::clearLocalSession();
            self::redirectOrJson('duplicate');
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
            // Log logout to DB first if wanted, but redirectOrJson is the priority
            self::clearLocalSession();
            self::redirectOrJson('expired');
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

    /**
     * Check if the current session ID has been replaced in the DB
     */
    private static function isSessionReplaced(): bool {
        try {
            $pdo = \DB::getInstance();
            $stmt = $pdo->prepare('SELECT current_session_id FROM cp_users WHERE id = ?');
            $stmt->execute([$_SESSION['user_id']]);
            $storedId = $stmt->fetchColumn();
            return (bool)($storedId && $storedId !== session_id());
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Destroys ONLY the local browser session (cookies/files)
     * Leaves the DB current_session_id intact.
     */
    private static function clearLocalSession(): void {
        session_unset();
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params['path'], $params['domain'],
                $params['secure'], $params['httponly']
            );
        }
        session_destroy();
    }

    /**
     * Redirects to login or returns JSON error depending on request type
     */
    private static function redirectOrJson(string $reason): void {
        $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') ||
                  (strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false);

        if ($isAjax) {
            header('Content-Type: application/json');
            http_response_code(401);
            echo json_encode([
                'success' => false, 
                'error'   => $reason,
                'message' => ($reason === 'duplicate' ? 'Sua conta foi acessada em outro local.' : 'Sua sessão expirou.')
            ]);
            exit;
        }

        // Full page load hit the wall: Show the HTML overlay
        $title = ($reason === 'duplicate') ? 'Sua conta foi acessada em outro local.' : 'Sua sessão expirou por inatividade.';
        self::serveDisconnectHtml($title);
    }

    private static function serveDisconnectHtml(string $message): void {
        http_response_code(401);
        $site_url = defined('SITE_URL') ? SITE_URL : '';
        echo "<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <title>Sessão Encerrada</title>
    <link href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css' rel='stylesheet'>
    <link href='https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700&display=swap' rel='stylesheet'>
    <style>
        body { margin: 0; padding: 0; background: #0f1115; font-family: 'Outfit', sans-serif; display: flex; align-items: center; justify-content: center; height: 100vh; overflow: hidden; }
        .disconnect-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; background: rgba(0, 0, 0, 0.85); backdrop-filter: blur(8px); z-index: 99999; }
        .disconnect-card { background: #1a1d24; border: 1px solid rgba(255,255,255,0.05); border-radius: 12px; padding: 40px; text-align: center; max-width: 400px; width: 90%; color: #fff; box-shadow: 0 20px 40px rgba(0,0,0,0.5); animation: popIn 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275); }
        .disconnect-icon { width: 70px; height: 70px; border-radius: 50%; background: rgba(239, 68, 68, 0.1); color: #ef4444; font-size: 30px; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; }
        .disconnect-loader { width: 40px; height: 40px; border: 3px solid rgba(255, 255, 255, 0.1); border-top-color: #ef4444; border-radius: 50%; animation: spin 1s linear infinite; margin: 25px auto 15px; }
        @keyframes spin { to { transform: rotate(360deg); } }
        @keyframes popIn { from { transform: scale(0.9); opacity: 0; } to { transform: scale(1); opacity: 1; } }
        h3 { font-size: 1.5rem; margin-bottom: 10px; font-weight: 700; margin-top: 0; }
        p { color: #9ca3af; font-size: 0.95rem; line-height: 1.5; margin: 0; }
        span { color: #6b7280; font-size: 0.85rem; }
    </style>
</head>
<body>
    <div class='disconnect-overlay'>
        <div class='disconnect-card'>
            <div class='disconnect-icon'><i class='fas fa-plug'></i></div>
            <h3>Sessão Encerrada</h3>
            <p>{$message}</p>
            <div class='disconnect-loader'></div>
            <span>Encerrando sua sessão com segurança...</span>
        </div>
    </div>
    <script>
        setTimeout(function() { window.location.href = '{$site_url}/logout'; }, 3500);
    </script>
</body>
</html>";
        exit;
    }
}

// Global inactivity check
try {
    Auth::checkInactivity();
} catch (\Exception $e) {
    error_log("Auth::checkInactivity failed: " . $e->getMessage());
}
