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
        require_once __DIR__ . '/../../../includes/repositories/LogRepository.php';
        $logRepo = new \LogRepository($pdo);
        
        $active_tab = $_GET['tab'] ?? 'general';

        // Process POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // CSRF Check
            require_once __DIR__ . '/../../../includes/helpers/CSRF.php';
            if (!\CSRF::verifyToken($_POST['csrf_token'] ?? '')) {
                header("Location: " . SITE_URL . "/settings?msg=error_csrf");
                exit;
            }

            if (isset($_POST['save_general']) || isset($_POST['remove_logo']) || isset($_POST['remove_login_bg'])) {
                $keys = ['system_name', 'enable_system_logs'];
                
                // Fetch existing settings for file cleanup
                $stmt = $pdo->prepare("SELECT setting_key, setting_value FROM cp_settings WHERE setting_key IN ('system_logo', 'login_background')");
                $stmt->execute();
                $existing = $stmt->fetchAll(\PDO::FETCH_KEY_PAIR);

                foreach ($keys as $key) {
                    $val = trim($_POST[$key] ?? '');
                    if ($key === 'enable_system_logs') $val = isset($_POST[$key]) ? '1' : '0';
                    
                    $stmt = $pdo->prepare("INSERT INTO cp_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
                    $stmt->execute([$key, $val, $val]);
                }

                require_once __DIR__ . '/../../../includes/helpers/ImageHelper.php';
                $logoDir = __DIR__ . '/../../../public/uploads/logos';
                $bgDir   = __DIR__ . '/../../../public/uploads/backgrounds';

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

                Cache::delete('platform_settings');
                
                // NOTIFICATION (Rule 39)
                try {
                    require_once __DIR__ . '/../../../includes/repositories/NotificationRepository.php';
                    $notifRepo = new \NotificationRepository($pdo);
                    $notifRepo->create([
                        'user_id' => 1,
                        'title'   => '⚙️ Configurações Atualizadas',
                        'message' => 'As configurações gerais do sistema foram modificadas.',
                        'link'    => SITE_URL . '/admin/settings?tab=general',
                        'type'    => 'info'
                    ]);
                } catch (\Exception $e) {}

                $logRepo->create([
                    'user_id' => $_SESSION['user_id'] ?? 0,
                    'action' => 'Settings Updated',
                    'description' => 'Configurações Gerais/Identidade do sistema atualizadas.',
                    'ip_address' => $_SERVER['REMOTE_ADDR'] ?? ''
                ]);
                
                header("Location: " . SITE_URL . "/settings?tab=general&msg=saved");
                exit;
            }

            if (isset($_POST['save_theme'])) {
                $theme = $_POST['system_theme'] ?? 'gold-black';
                $stmt = $pdo->prepare("INSERT INTO cp_settings (setting_key, setting_value) VALUES ('system_theme', ?) ON DUPLICATE KEY UPDATE setting_value = ?");
                $stmt->execute([$theme, $theme]);
                Cache::delete('platform_settings');
                
                $logRepo->create([
                    'user_id' => $_SESSION['user_id'] ?? 0,
                    'action' => 'Theme Updated',
                    'description' => 'Tema do sistema alterado para: ' . $theme,
                    'ip_address' => $_SERVER['REMOTE_ADDR'] ?? ''
                ]);

                header("Location: " . SITE_URL . "/settings?tab=themes&msg=updated");
                exit;
            }

            if (isset($_POST['save_security'])) {
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
                // Sync Blocked IPs table (Rule 39)
                if (isset($_POST['security_blocked_ips'])) {
                    try {
                        $ips = explode("\n", str_replace("\r", "", $_POST['security_blocked_ips']));
                        $ips = array_filter(array_map('trim', $ips));
                        
                        $pdo->exec("DELETE FROM cp_blocked_ips");
                        if (!empty($ips)) {
                            $stmt = $pdo->prepare("INSERT INTO cp_blocked_ips (ip_address, reason) VALUES (?, 'Bloqueio Manual')");
                            foreach (array_unique($ips) as $ip) {
                                if (!empty($ip)) $stmt->execute([$ip]);
                            }
                        }
                    } catch (\PDOException $e) {
                        // Migration may not have been run
                    }
                }
                Cache::delete('platform_settings');

                // NOTIFICATION (Rule 39)
                try {
                    require_once __DIR__ . '/../../../includes/repositories/NotificationRepository.php';
                    $notifRepo = new \NotificationRepository($pdo);
                    $notifRepo->create([
                        'user_id' => 1,
                        'title'   => '🛡️ Segurança Atualizada',
                        'message' => 'As políticas de segurança e IPs bloqueados foram modificados.',
                        'link'    => SITE_URL . '/admin/settings?tab=security',
                        'type'    => 'warning'
                    ]);
                } catch (\Exception $e) {}

                $logRepo->create([
                    'user_id' => $_SESSION['user_id'] ?? 0,
                    'action' => 'Security Settings Updated',
                    'description' => 'Políticas de segurança e lista de IPs bloqueados atualizadas.',
                    'ip_address' => $_SERVER['REMOTE_ADDR'] ?? ''
                ]);

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
            'active_tab' => $active_tab
        ]);
    }
}
