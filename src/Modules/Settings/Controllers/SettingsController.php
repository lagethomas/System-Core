<?php
declare(strict_types=1);

namespace App\Modules\Settings\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Cache;
use Auth;

/**
 * Global Settings Controller (Modular)
 */
class SettingsController extends Controller {

    public function index(): void {
        Auth::requireAdmin();
        
        $pdo = Database::getInstance();
        $active_tab = $_GET['tab'] ?? 'general';

        // Fetch Current Settings
        $stmt = $pdo->prepare("SELECT setting_key, setting_value FROM cp_settings");
        $stmt->execute();
        $settings = $stmt->fetchAll(\PDO::FETCH_KEY_PAIR);

        $this->render('index', [
            'settings' => $settings,
            'active_tab' => $active_tab
        ]);
    }

    /**
     * AJAX-First Save Endpoint
     */
    public function save(): void {
        Auth::requireAdmin();
        $pdo = Database::getInstance();
        
        require_once __DIR__ . '/../../../../includes/helpers/CSRF.php';
        if (!\CSRF::verifyToken($_POST['csrf_token'] ?? '')) {
            $this->jsonResponse(['success' => false, 'message' => 'Erro de segurança (CSRF).'], 403);
            return;
        }

        require_once __DIR__ . '/../../../../includes/repositories/LogRepository.php';
        $logRepo = new \LogRepository($pdo);
        $tab = $_POST['tab'] ?? '';

        try {
            if ($tab === 'general') {
                $keys = ['system_name', 'enable_system_logs', 'items_per_page'];
                
                // Fetch existing settings for file cleanup
                $stmt = $pdo->prepare("SELECT setting_key, setting_value FROM cp_settings WHERE setting_key IN ('system_logo', 'login_background')");
                $stmt->execute();
                $existing = $stmt->fetchAll(\PDO::FETCH_KEY_PAIR);

                foreach ($keys as $key) {
                    $val = trim((string)($_POST[$key] ?? ''));
                    if ($key === 'enable_system_logs') $val = isset($_POST[$key]) ? '1' : '0';
                    
                    $stmt = $pdo->prepare("INSERT INTO cp_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
                    $stmt->execute([$key, $val, $val]);
                }

                require_once __DIR__ . '/../../../../includes/helpers/ImageHelper.php';
                $logoDir = dirname(dirname(dirname(dirname(__DIR__)))) . '/public/uploads/logos';
                $bgDir   = dirname(dirname(dirname(dirname(__DIR__)))) . '/public/uploads/backgrounds';

                // Handle Logo Upload
                if (!empty($_FILES['system_logo']['name'])) {
                    $newLogo = \ImageHelper::uploadAndConvert($_FILES['system_logo'], $logoDir, 'logo');
                    if ($newLogo) {
                        if (!empty($existing['system_logo'])) \ImageHelper::safeDelete($existing['system_logo'], $logoDir);
                        $stmt = $pdo->prepare("INSERT INTO cp_settings (setting_key, setting_value) VALUES ('system_logo', ?) ON DUPLICATE KEY UPDATE setting_value = ?");
                        $stmt->execute([$newLogo, $newLogo]);
                    }
                } elseif (isset($_POST['remove_logo'])) {
                    if (!empty($existing['system_logo'])) \ImageHelper::safeDelete($existing['system_logo'], $logoDir);
                    $stmt = $pdo->prepare("UPDATE cp_settings SET setting_value = NULL WHERE setting_key = 'system_logo'");
                    $stmt->execute();
                }

                // Handle Login Background Upload
                if (!empty($_FILES['login_background']['name'])) {
                    $newBg = \ImageHelper::uploadAndConvert($_FILES['login_background'], $bgDir, 'login_bg');
                    if ($newBg) {
                        if (!empty($existing['login_background'])) \ImageHelper::safeDelete($existing['login_background'], $bgDir);
                        $stmt = $pdo->prepare("INSERT INTO cp_settings (setting_key, setting_value) VALUES ('login_background', ?) ON DUPLICATE KEY UPDATE setting_value = ?");
                        $stmt->execute([$newBg, $newBg]);
                    }
                } elseif (isset($_POST['remove_login_bg'])) {
                    if (!empty($existing['login_background'])) \ImageHelper::safeDelete($existing['login_background'], $bgDir);
                    $stmt = $pdo->prepare("UPDATE cp_settings SET setting_value = NULL WHERE setting_key = 'login_background'");
                    $stmt->execute();
                }

                $logRepo->create([
                    'user_id' => (int)($_SESSION['user_id'] ?? 0),
                    'action' => 'Settings Updated',
                    'description' => 'Configurações Gerais/Identidade atualizadas.',
                    'ip_address' => $_SERVER['REMOTE_ADDR'] ?? ''
                ]);

            } elseif ($tab === 'themes') {
                $theme = $_POST['system_theme'] ?? '';
                $login_theme = $_POST['system_login_theme'] ?? '';

                if ($theme) {
                    $stmt = $pdo->prepare("INSERT INTO cp_settings (setting_key, setting_value) VALUES ('system_theme', ?) ON DUPLICATE KEY UPDATE setting_value = ?");
                    $stmt->execute([$theme, $theme]);
                }
                
                if ($login_theme) {
                    $stmt = $pdo->prepare("INSERT INTO cp_settings (setting_key, setting_value) VALUES ('system_login_theme', ?) ON DUPLICATE KEY UPDATE setting_value = ?");
                    $stmt->execute([$login_theme, $login_theme]);
                }

                $logRepo->create([
                    'user_id' => (int)($_SESSION['user_id'] ?? 0),
                    'action' => 'Theme Updated',
                    'description' => 'Tema global alterado.',
                    'ip_address' => $_SERVER['REMOTE_ADDR'] ?? ''
                ]);

            } elseif ($tab === 'security') {
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

                $logRepo->create([
                    'user_id' => (int)($_SESSION['user_id'] ?? 0),
                    'action' => 'Security Updated',
                    'description' => 'Configurações de segurança atualizadas.',
                    'ip_address' => $_SERVER['REMOTE_ADDR'] ?? ''
                ]);
            }

            Cache::delete('platform_settings');
            $this->jsonResponse([
                'success' => true, 
                'message' => 'Configurações salvas com sucesso!',
                'noReload' => true
            ]);
        } catch (\Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => 'Erro ao salvar: ' . $e->getMessage()], 500);
        }
    }
}
