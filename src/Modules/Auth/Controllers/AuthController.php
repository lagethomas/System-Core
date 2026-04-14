<?php
declare(strict_types=1);

namespace App\Modules\Auth\Controllers;

use App\Core\Controller;
use App\Core\Database;
use Auth;
use Logger;

/**
 * Modular Authentication Controller
 */
class AuthController extends Controller {

    /**
     * Show login form (GET /login)
     */
    public function index(): void {
        global $platform_settings;

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
        global $platform_settings;

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

    /**
     * Login Attempt (POST /login)
     */
    public function attempt(): void {
        global $platform_settings;
        $pdo = Database::getInstance();
        
        if (Auth::isLoggedIn()) {
            header('Location: ' . SITE_URL . '/dashboard');
            exit;
        }

        $error        = '';
        $username     = trim($_POST['username'] ?? '');
        $password     = $_POST['password'] ?? '';
        $force        = isset($_POST['force_login']);

        // Security Checks
        if (!Auth::checkBruteForce()) {
            $lockout = $platform_settings['security_lockout_time'] ?? 15;
            $this->renderLogin("Muitas tentativas. Seu IP está bloqueado temporariamente por $lockout minutos.", $platform_settings ?? []);
            return;
        }

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

                // Tenant Validation
                if (empty($posted_company_id) && strtolower($user['role'] ?? '') !== 'administrador') {
                    $error = 'Acesso restrito. Utilize o link da sua empresa para logar.';
                    $user = null;
                } elseif (!empty($posted_company_id) && ($user['company_id'] ?? 0) != $posted_company_id) {
                    $error = 'Estas credenciais não pertencem a esta empresa.';
                    $user = null;
                } elseif (!empty($user['company_id']) && ($user['company_active'] ?? 1) == 0) {
                    $error = 'Acesso bloqueado: Esta empresa está inativa.';
                    $user = null;
                }
                
                if ($user && ($user['active'] ?? 1) == 0) {
                    $error = 'Sua conta de usuário está desativada.';
                    $user = null;
                }
                
                if ($user) {
                    // Single Session Enforcement
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

                    $stmt_update = $pdo->prepare("UPDATE cp_users SET last_login = NOW() WHERE id = ?");
                    $stmt_update->execute([$user['id']]);

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
        require_once __DIR__ . '/../../../../includes/logs.php';
        Logger::log('logout', 'Logout realizado.');
        Auth::logout();
    }

    /**
     * Session Pulse (POST /api/auth/pulse)
     */
    public function pulse(): void {
        if (!Auth::isLoggedIn()) {
            $this->jsonResponse(['success' => false, 'error' => 'expired']);
            return;
        }

        try {
            $pdo    = Database::getInstance();
            $userId = (int)$_SESSION['user_id'];
            $sessId = session_id();

            // Single Session Enforcement
            $stmt = $pdo->prepare('SELECT current_session_id FROM cp_users WHERE id = ?');
            $stmt->execute([$userId]);
            $storedSessId = $stmt->fetchColumn();

            if ($storedSessId && $storedSessId !== $sessId) {
                $this->jsonResponse(['success' => false, 'error' => 'duplicate']);
                return;
            }

            $_SESSION['last_activity'] = time();
            $pdo->prepare('UPDATE cp_users SET last_pulse = NOW() WHERE id = ?')->execute([$userId]);

            global $platform_settings;
            $timeoutMin  = (int)($platform_settings['security_session_timeout'] ?? 120);
            
            $this->jsonResponse([
                'success'           => true,
                'remaining_seconds' => $timeoutMin * 60
            ]);
        } catch (\Exception $e) {
            $this->jsonResponse(['success' => false, 'error' => 'db_error']);
        }
    }

    /**
     * Confirm Email Change (GET /confirm-email?token=...)
     */
    public function confirmEmail(): void {
        $token = $_GET['token'] ?? '';

        if (empty($token)) {
            $this->renderConfirmation('Token inválido ou ausente.', false);
            return;
        }

        try {
            $request = Database::fetch(
                "SELECT * FROM cp_email_confirmations WHERE token = ? AND expires_at > NOW()", 
                [$token]
            );

            if (!$request) {
                $expired = Database::fetch("SELECT id FROM cp_email_confirmations WHERE token = ?", [$token]);
                $msg = $expired ? 'Este link de confirmação expirou (validade de 4 horas).' : 'Token de confirmação inválido.';
                $this->renderConfirmation($msg, false);
                return;
            }

            $userId = (int)$request['user_id'];
            $newEmail = $request['new_email'];

            Database::update('cp_users', ['email' => $newEmail], 'id = :where_id', ['where_id' => $userId]);
            Database::delete('cp_email_confirmations', 'user_id = ?', [$userId]);

            require_once __DIR__ . '/../../../../includes/logs.php';
            Logger::log('email_changed', "E-mail do usuário ID ". (string)$userId ." alterado para {$newEmail} confirm-email.");

            $this->renderConfirmation('E-mail alterado com sucesso! Agora você pode utilizar seu novo e-mail para acessar o sistema.', true);
        } catch (\Exception $e) {
            $this->renderConfirmation('Erro ao processar confirmação: ' . $e->getMessage(), false);
        }
    }

    /**
     * Layout Private Renderers
     */
    private function renderLogin(string $error, array $settings, bool $warn_session = false, string $pre_username = '', string $pre_password = '', ?array $company = null): void {
        $system_name      = $company ? $company['name'] : htmlspecialchars($settings['system_name'] ?? 'SaaSFlow Core');
        $system_logo      = ($company && !empty($company['logo'])) ? $company['logo'] : ($settings['system_logo'] ?? '');
        $login_background = ($company && !empty($company['background_image'])) ? $company['background_image'] : ($settings['login_background'] ?? '');
        
        if ($company && !empty($company['login_theme'])) {
            $theme_slug = $company['login_theme'];
        } elseif ($company && !empty($company['theme'])) {
            $theme_slug = $company['theme'];
        } else {
            $theme_slug = $settings['system_login_theme'] ?? ($settings['system_theme'] ?? 'gold-black');
        }
        $theme_slug = htmlspecialchars((string)$theme_slug);
        $v = (string)time();
        
        include __DIR__ . '/../Views/login.php';
        exit;
    }

    private function renderConfirmation(string $message, bool $success): void {
        global $platform_settings;
        $theme = $platform_settings['system_theme'] ?? 'gold-black';
        $systemName = $platform_settings['system_name'] ?? 'SaaSFlow Core';
        $success_msg = $success;
        
        include __DIR__ . '/../Views/confirm_email.php';
        exit;
    }

    private function getCompanyBySlug(string $slug): ?array {
        return Database::fetch("SELECT * FROM cp_companies WHERE slug = ? AND active = 1 LIMIT 1", [$slug]);
    }

    private function getCompanyById(int $id): ?array {
        return Database::fetch("SELECT * FROM cp_companies WHERE id = ?", [$id]);
    }
}
