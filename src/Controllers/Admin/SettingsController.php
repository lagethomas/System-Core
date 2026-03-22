<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use Auth;
use ThemeHelper;
use Cache;

class SettingsController extends Controller {
    public function index(): void {
        Auth::requireAdmin();
        
        global $pdo;
        require_once __DIR__ . '/../../../includes/helpers/ThemeHelper.php';
        
        $active_tab = $_GET['tab'] ?? 'general';

        // Process POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nonce = $_POST['nonce'] ?? '';
            
            if (isset($_POST['save_general'])) {
                if (!\Nonce::verify($nonce, 'save_general_settings')) {
                    header("Location: " . SITE_URL . "/settings?tab=general&msg=error_nonce");
                    exit;
                }
                $keys = ['system_name', 'enable_system_logs'];
                foreach ($keys as $key) {
                    $val = trim($_POST[$key] ?? '');
                    if ($key === 'enable_system_logs') $val = isset($_POST[$key]) ? '1' : '0';
                    $stmt = $pdo->prepare("INSERT INTO cp_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
                    $stmt->execute([$key, $val, $val]);
                }
                Cache::delete('platform_settings');
                header("Location: " . SITE_URL . "/settings?tab=general&msg=saved");
                exit;
            }

            if (isset($_POST['save_theme'])) {
                if (!\Nonce::verify($nonce, 'save_theme_settings')) {
                    header("Location: " . SITE_URL . "/settings?tab=themes&msg=error_nonce");
                    exit;
                }
                $theme = $_POST['system_theme'] ?? 'gold-black';
                $stmt = $pdo->prepare("INSERT INTO cp_settings (setting_key, setting_value) VALUES ('system_theme', ?) ON DUPLICATE KEY UPDATE setting_value = ?");
                $stmt->execute([$theme, $theme]);
                Cache::delete('platform_settings');
                header("Location: " . SITE_URL . "/settings?tab=themes&msg=updated");
                exit;
            }

            if (isset($_POST['save_security'])) {
                if (!\Nonce::verify($nonce, 'save_security_settings')) {
                    header("Location: " . SITE_URL . "/settings?tab=security&msg=error_nonce");
                    exit;
                }
                $keys = [
                    'security_max_attempts', 'security_lockout_time', 'security_strong_password', 
                    'security_session_timeout', 'security_ip_lockout', 'security_single_session',
                    'security_log_days', 'security_log_limit'
                ];
                foreach ($keys as $key) {
                    $val = trim((string)($_POST[$key] ?? ''));
                    if ($key === 'security_strong_password' || $key === 'security_ip_lockout' || $key === 'security_single_session') $val = isset($_POST[$key]) ? '1' : '0';
                    $stmt = $pdo->prepare("INSERT INTO cp_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
                    $stmt->execute([$key, $val, $val]);
                }
                Cache::delete('platform_settings');
                header("Location: " . SITE_URL . "/settings?tab=security&msg=saved");
                exit;
            }
        }

        // Fetch Current Settings
        $stmt = $pdo->prepare("SELECT setting_key, setting_value FROM cp_settings");
        $stmt->execute();
        $settings = $stmt->fetchAll(\PDO::FETCH_KEY_PAIR);

        $this->render('admin/settings', [
            'settings' => $settings,
            'active_tab' => $active_tab,
            'nonces' => [
                'general' => \Nonce::create('save_general_settings'),
                'theme' => \Nonce::create('save_theme_settings'),
                'security' => \Nonce::create('save_security_settings')
            ]
        ]);
    }
}
