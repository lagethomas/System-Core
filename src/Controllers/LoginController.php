<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use Auth;
use App\Helpers\Logger;

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
        $this->renderLogin($error, $platform_settings ?? []);
    }

    /**
     * Handle login form submission (POST /login)
     */
    public function attempt(): void {
        global $pdo, $platform_settings;

        if (Auth::isLoggedIn()) {
            header('Location: ' . SITE_URL . '/dashboard');
            exit;
        }

        $error        = '';
        $warn_session = false; // flag for "active session" warning
        $username     = trim($_POST['username'] ?? '');
        $password     = $_POST['password'] ?? '';
        $force        = isset($_POST['force_login']); // user chose to force-logout old session

        if (!$username || !$password) {
            $error = 'Preencha todos os campos.';
        } else {
            $stmt = $pdo->prepare('SELECT * FROM cp_users WHERE username = ?');
            $stmt->execute([$username]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {

                // ── SINGLE SESSION CHECK ───────────────────────────────
                $single_session = ($platform_settings['security_single_session'] ?? '0') === '1';
                if ($single_session && !$force && Auth::hasActiveSession((int)$user['id'])) {
                    // Block login — show warning with a "force" button
                    $warn_session = true;
                    $this->renderLogin($error, $platform_settings ?? [], $warn_session, $username, $password);
                    return;
                }

                // If force = true, clear the old session from DB before logging in
                if ($single_session && $force) {
                    Auth::clearSessionFromDB((int)$user['id']);
                }
                // ── END SINGLE SESSION CHECK ───────────────────────────

                Auth::login($user);

                try {
                    require_once __DIR__ . '/../../includes/logs.php';
                    Logger::log('login', 'Login realizado via rota MVC.');
                } catch (\Exception $e) {}

                header('Location: ' . SITE_URL . '/dashboard');
                exit;

            } else {
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
            Logger::log('logout', 'Logout realizado.');
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
}
