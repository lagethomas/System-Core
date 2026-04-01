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
     * Show company-specific login form (GET /{slug}/login)
     */
    public function companyLogin(string $slug): void {
        global $pdo, $platform_settings;

        if (Auth::isLoggedIn()) {
            header('Location: ' . SITE_URL . '/dashboard');
            exit;
        }
        $company = $this->getCompanyBySlug($slug);
        if (!$company) {
            header('Location: ' . SITE_URL . '/login');
            exit;
        }
        $this->renderLogin('', [], false, '', '', $company);
    }

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
            $this->renderLogin('Seu endereço IP está bloqueado por motivos de segurança.', $platform_settings ?? []);
            return;
        }

        if (!Auth::checkBruteForce()) {
            $lockout = $platform_settings['security_lockout_time'] ?? 15;
            $this->renderLogin("Muitas tentativas. Seu IP está bloqueado temporariamente por $lockout minutos.", $platform_settings ?? []);
            return;
        }
        // ──────────────────────────────────────────────────────

        if (!$username || !$password) {
            $error = 'Preencha todos os campos.';
        } else {
            $stmt = $pdo->prepare('
                SELECT u.*, c.slug as company_slug, c.active as company_active
                FROM cp_users u 
                LEFT JOIN cp_companies c ON u.company_id = c.id 
                WHERE u.username = ?
            ');
            $stmt->execute([$username]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                
                $posted_company_id = !empty($_POST['company_id']) ? (int)$_POST['company_id'] : null;

                // ── TENANT VALIDATION ──────────────────────────────────
                if (empty($posted_company_id) && strtolower($user['role'] ?? '') !== 'administrador') {
                    $error = 'Acesso restrito. Utilize o link da sua empresa para logar.';
                    $user = null; // Block progress
                } elseif (!empty($posted_company_id) && ($user['company_id'] ?? 0) != $posted_company_id) {
                    $error = 'Estas credenciais não pertencem a esta empresa.';
                    $user = null; // Block progress
                } elseif (!empty($user['company_id']) && ($user['company_active'] ?? 1) == 0) {
                    $error = 'Acesso bloqueado: Esta empresa está inativa.';
                    $user = null;
                }
                
                if ($user && ($user['active'] ?? 1) == 0) {
                    $error = 'Sua conta de usuário está desativada.';
                    $user = null;
                }
                
                if ($user) {
                    // ── SINGLE SESSION CHECK ───────────────────────────────
                    $stmt_ss = $pdo->prepare("SELECT setting_value FROM cp_settings WHERE setting_key = 'security_single_session' LIMIT 1");
                    $stmt_ss->execute();
                    $ss_val = $stmt_ss->fetchColumn();
                    $single_session = ($ss_val === '1');

                    if ($single_session && Auth::hasActiveSession((int)$user['id'])) {
                        if (!$force) {
                            $this->renderLogin('', $platform_settings ?? [], true, $username, $password, $posted_company_id ? $this->getCompanyById($posted_company_id) : null);
                            return;
                        }
                        Auth::clearSessionFromDB((int)$user['id']);
                    }

                    Auth::login($user);
                    Auth::resetAttempts();

                    try {
                        require_once __DIR__ . '/../../includes/repositories/UserRepository.php';
                        $userRepo = new \UserRepository($pdo);
                        $userRepo->updateLastLogin((int)$user['id']);
                    } catch (\Exception $e) {}

                    header('Location: ' . SITE_URL . '/dashboard');
                    exit;
                }
            } else {
                Auth::registerFailedAttempt();
                $error = 'Credenciais inválidas.';
            }
        }

        $this->renderLogin($error, $platform_settings ?? [], false, $username, $password, !empty($_POST['company_id']) ? $this->getCompanyById((int)$_POST['company_id']) : null);
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
        string $pre_password = '',
        ?array $company = null
    ): void {
        $system_name      = $company ? $company['name'] : htmlspecialchars($settings['system_name'] ?? 'SaaSFlow Core');
        $system_logo      = ($company && !empty($company['logo'])) ? $company['logo'] : ($settings['system_logo'] ?? '');
        $login_background = ($company && !empty($company['background_image'])) ? $company['background_image'] : ($settings['login_background'] ?? '');
        
        // Prioritize company theme if available
        $theme_slug = 'gold-black';
        if ($company && !empty($company['theme'])) {
            $theme_slug = $company['theme'];
        } else {
            $theme_slug = $settings['system_theme'] ?? 'gold-black';
        }
        $theme_slug = htmlspecialchars($theme_slug);

        $csrf_token   = \CSRF::generateToken();
        $v            = (string)time();

        // LoginController is at src/Controllers/LoginController.php
        // View is at src/Views/auth/login.php
        include __DIR__ . '/../Views/auth/login.php';
        exit;
    }

    /**
     * Helper to fetch company by slug
     */
    private function getCompanyBySlug(string $slug): ?array {
        $pdo = \App\Core\Database::getInstance();
        $stmt = $pdo->prepare("SELECT * FROM cp_companies WHERE slug = ? AND active = 1 LIMIT 1");
        $stmt->execute([$slug]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Helper to fetch company by id
     */
    private function getCompanyById(int $id): ?array {
        $pdo = \App\Core\Database::getInstance();
        $stmt = $pdo->prepare("SELECT * FROM cp_companies WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Session Pulse (POST /api/auth/pulse)
     * Rule 5: Keepalive and duplicate session check
     * Resets $_SESSION['last_activity'] so active users are never disconnected early.
     */
    public function pulse(): void {
        try {
            require_once __DIR__ . '/../../includes/logs.php';

            if (!Auth::isLoggedIn()) {
                \Logger::log('debug_session', '[pulse] REJECTED — not logged in. session_id=' . session_id());
                $this->jsonResponse(['success' => false, 'error' => 'expired']);
                return;
            }

            $pdo    = \App\Core\Database::getInstance();
            $userId = (int)$_SESSION['user_id'];
            $sessId = session_id();
            $lastAct = $_SESSION['last_activity'] ?? null;
            $idleSecs = $lastAct ? (time() - (int)$lastAct) : -1;

            \Logger::log('debug_session',
                "[pulse] user=" . (string)$userId . " session={$sessId} " .
                "idle=" . (string)$idleSecs . "s " .
                "last_activity=" . ($lastAct ? date('H:i:s', (int)$lastAct) : 'NULL')
            );

            // Single Session Enforcement (Rule 39)
            $stmt = $pdo->prepare('SELECT current_session_id FROM cp_users WHERE id = ?');
            $stmt->execute([$userId]);
            $storedSessId = $stmt->fetchColumn();

            if ($storedSessId && $storedSessId !== $sessId) {
                \Logger::log('debug_session',
                    "[pulse] DUPLICATE detected user=" . (string)$userId . " stored={$storedSessId} current={$sessId}"
                );
                \Logger::log('security_session', 'Sessão duplicada detectada e encerrada automaticamente.');
                $this->jsonResponse(['success' => false, 'error' => 'duplicate']);
                return;
            }

            // ── Reset inactivity timer (KEY FIX) ──────────────────
            $_SESSION['last_activity'] = time();
            // ────────────────────────────────────────────────────────

            // Update DB pulse
            $stmt = $pdo->prepare('UPDATE cp_users SET last_pulse = NOW() WHERE id = ?');
            $stmt->execute([$userId]);

            \Logger::log('debug_session', "[pulse] OK user=" . (string)$userId . " last_activity reset to " . date('H:i:s'));

            global $platform_settings;
            $timeoutMin  = (int)($platform_settings['security_session_timeout'] ?? 120);
            $timeoutSecs = $timeoutMin * 60;

            $this->jsonResponse([
                'success'           => true,
                'remaining_seconds' => $timeoutSecs
            ]);
        } catch (\Exception $e) {
            error_log("Pulse API error: " . $e->getMessage());
            $this->jsonResponse(['success' => false, 'error' => 'db_error']);
        }
    }
}
