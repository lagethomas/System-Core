<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use Auth;

class LoginController extends Controller {

    /**
     * Show login form (GET /login)
     */
    public function index(): void {
        global $pdo, $platform_settings;

        // Already logged in → redirect to dashboard
        if (Auth::isLoggedIn()) {
            header('Location: ' . SITE_URL . '/dashboard');
            exit;
        }

        $error = '';
        if (isset($_GET['error']) && $_GET['error'] === 'duplicate') {
            $error = 'Sua conta foi acessada em outro local e esta sessão foi encerrada.';
        }

        $this->renderLogin($error, $platform_settings ?? []);
    }

    /**
     * Handle login form submission (POST /login)
     */
    public function attempt(): void {
        global $pdo, $platform_settings;
        
        try {
            require_once __DIR__ . '/../../includes/logs.php';
            \Logger::log('debug_login_flow', 'LoginController::attempt INICIADO.');
        } catch (\Exception $e) {}

        if (Auth::isLoggedIn()) {
            header('Location: ' . SITE_URL . '/dashboard');
            exit;
        }

        $error        = '';
        $warn_session = false; // flag for "active session" warning
        $username     = trim($_POST['username'] ?? '');
        $password     = $_POST['password'] ?? '';
        $force        = isset($_POST['force_login']); // user chose to force-logout old session

        // ── SECURITY CHECKS (Rule 39) ──────────────────────────
        if (Auth::isIpBlocked()) {
            try {
                require_once __DIR__ . '/../../includes/logs.php';
                \Logger::log('security_block', 'Tentativa de login bloqueada: IP banido permanentemente.');
                
                // Notify Admin (ID 1)
                require_once __DIR__ . '/../../includes/repositories/NotificationRepository.php';
                $notifRepo = new \NotificationRepository($pdo);
                $notifRepo->create([
                    'user_id' => 1,
                    'title'   => '🚫 IP Bloqueado',
                    'message' => 'Um IP banido tentou acessar o sistema.',
                    'link'    => SITE_URL . '/admin/logs',
                    'type'    => 'danger'
                ]);
            } catch (\Exception $e) {}
            
            $this->renderLogin('Seu endereço IP está bloqueado por motivos de segurança.', $platform_settings ?? []);
            return;
        }

        if (!Auth::checkBruteForce()) {
            try {
                require_once __DIR__ . '/../../includes/logs.php';
                \Logger::log('security_block', 'Tentativa de login bloqueada: Muitos erros seguidos (Brute Force).');
                
                // Notify Admin (ID 1)
                require_once __DIR__ . '/../../includes/repositories/NotificationRepository.php';
                $notifRepo = new \NotificationRepository($pdo);
                $notifRepo->create([
                    'user_id' => 1,
                    'title'   => '🛡️ Alerta de Brute Force',
                    'message' => "IP bloqueado temporariamente por excesso de tentativas para o usuário: $username",
                    'link'    => SITE_URL . '/admin/logs',
                    'type'    => 'danger'
                ]);
            } catch (\Exception $e) {}

            $lockout = $platform_settings['security_lockout_time'] ?? 15;
            $this->renderLogin("Muitas tentativas. Seu IP está bloqueado temporariamente por $lockout minutos.", $platform_settings ?? []);
            return;
        }
        // ──────────────────────────────────────────────────────

        if (!$username || !$password) {
            $error = 'Preencha todos os campos.';
        } else {
            $stmt = $pdo->prepare('SELECT * FROM cp_users WHERE username = ?');
            $stmt->execute([$username]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {

                // ── SINGLE SESSION CHECK ───────────────────────────────
                // Always read setting fresh from DB — never rely on cached value for security
                $stmt_ss = $pdo->prepare("SELECT setting_value FROM cp_settings WHERE setting_key = 'security_single_session' LIMIT 1");
                $stmt_ss->execute();
                $ss_val = $stmt_ss->fetchColumn();
                $single_session = ($ss_val === '1');

                if ($single_session && Auth::hasActiveSession((int)$user['id'])) {
                    if ($force) {
                        // User chose to force logout from other locations
                        Auth::clearSessionFromDB((int)$user['id']);
                        try {
                            require_once __DIR__ . '/../../includes/logs.php';
                            \Logger::log('security_session', 'Logout forçado realizado pelo usuário: ' . $user['username']);
                        } catch (\Exception $e) {}
                    } else {
                        try {
                            require_once __DIR__ . '/../../includes/logs.php';
                            \Logger::log('security_session', 'Acesso bloqueado: Usuário já possui uma sessão ativa.');
                            
                            // Notify the existing session about this attempt
                            require_once __DIR__ . '/../../includes/repositories/NotificationRepository.php';
                            $notifRepo = new \NotificationRepository($pdo);
                            $notifRepo->create([
                                'user_id' => $user['id'],
                                'title'   => '⚠️ Alerta de Segurança',
                                'message' => 'Uma nova tentativa de login foi detectada. Se não foi você, recomendamos trocar sua senha.',
                                'link'    => SITE_URL . '/logs',
                                'type'    => 'warning'
                            ]);
                        } catch (\Exception $e) {}

                        // Block login — show the warning with pre-filled inputs
                        $this->renderLogin('', $platform_settings ?? [], true, $username, $password);
                        return;
                    }
                }
                // ── END SINGLE SESSION CHECK ───────────────────────────

                Auth::login($user);
                Auth::resetAttempts(); // Reset failures on success

                try {
                    require_once __DIR__ . '/../../includes/logs.php';
                    \Logger::log('login', 'Autenticação bem-sucedida para o usuário: ' . $user['username']);
                } catch (\Exception $e) {}

                header('Location: ' . SITE_URL . '/dashboard');
                exit;

            } else {
                Auth::registerFailedAttempt(); // Log failure for Brute Force check
                $error = 'Credenciais inválidas.';
            }
        }

        $this->renderLogin($error, $platform_settings ?? []);
    }

    /**
     * Logout (GET /logout)
     */
    public function logout(): void {
        try {
            require_once __DIR__ . '/../../includes/logs.php';
            \Logger::log('logout', 'Logout realizado.');
        } catch (\Exception $e) {}

        Auth::logout();
    }

    /**
     * Renders the login page HTML directly (no layout header/footer).
     */
    private function renderLogin(
        string $error,
        array  $settings,
        bool   $warn_session = false,
        string $pre_username = '',
        string $pre_password = ''
    ): void {
        global $platform_settings;
        $settings     = $platform_settings ?? $settings;
        $system_name  = htmlspecialchars($settings['system_name'] ?? 'SaaSFlow Core');
        $theme_slug   = htmlspecialchars($settings['system_theme'] ?? 'gold-black');
        $csrf_token   = \CSRF::generateToken();
        $v            = (string)time();

        // LoginController is at src/Controllers/LoginController.php
        // View is at src/Views/auth/login.php
        include __DIR__ . '/../Views/auth/login.php';
        exit;
    }

    /**
     * Session Pulse (POST /api/auth/pulse)
     * Rule 5: Keepalive and duplicate session check
     */
    public function pulse(): void {
        try {
            if (!Auth::isLoggedIn()) {
                $this->jsonResponse(['success' => false, 'error' => 'expired']);
                return;
            }

            $pdo = \App\Core\Database::getInstance();
            $userId = (int)$_SESSION['user_id'];

            // Single Session Enforcement (Rule 39)
            $stmt = $pdo->prepare('SELECT current_session_id FROM cp_users WHERE id = ?');
            $stmt->execute([$userId]);
            $storedSessId = $stmt->fetchColumn();

            if ($storedSessId && $storedSessId !== session_id()) {
                try {
                    require_once __DIR__ . '/../../includes/logs.php';
                    \Logger::log('security_session', 'Sessão duplicada detectada e encerrada automaticamente.');
                } catch (\Exception $e) {}

                $this->jsonResponse(['success' => false, 'error' => 'duplicate']);
                return;
            }

            // Update Pulse
            $stmt = $pdo->prepare('UPDATE cp_users SET last_pulse = NOW() WHERE id = ?');
            $stmt->execute([$userId]);

            $this->jsonResponse(['success' => true]);
        } catch (\Exception $e) {
            error_log("Pulse API error (likely missing columns): " . $e->getMessage());
            $this->jsonResponse(['success' => false, 'error' => 'db_error']);
        }
    }
}
