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

                // Handle Files (System Logo)
                if (isset($_FILES['system_logo']) && $_FILES['system_logo']['error'] === UPLOAD_ERR_OK) {
                    $tmpPath = $_FILES['system_logo']['tmp_name'];
                    $filename = 'logo_' . time() . '.webp';
                    $uploadDir = dirname(dirname(dirname(__DIR__))) . '/public/assets/img/';
                    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
                    $uploadPath = $uploadDir . $filename;
                    
                    if ($this->convertToWebP($tmpPath, $uploadPath)) {
                        $val = '/assets/img/' . $filename;
                        $stmt = $pdo->prepare("INSERT INTO cp_settings (setting_key, setting_value) VALUES ('system_logo', ?) ON DUPLICATE KEY UPDATE setting_value = ?");
                        $stmt->execute([$val, $val]);
                    }
                }

                // Handle Files (Cardapio BG)
                if (isset($_FILES['cardapio_bg']) && $_FILES['cardapio_bg']['error'] === UPLOAD_ERR_OK) {
                    $tmpPath = $_FILES['cardapio_bg']['tmp_name'];
                    $filename = 'cardapio_bg_' . time() . '.webp';
                    $uploadDir = dirname(dirname(dirname(__DIR__))) . '/public/assets/img/';
                    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
                    $uploadPath = $uploadDir . $filename;
                    
                    if ($this->convertToWebP($tmpPath, $uploadPath)) {
                        $val = '/assets/img/' . $filename;
                        $stmt = $pdo->prepare("INSERT INTO cp_settings (setting_key, setting_value) VALUES ('cardapio_bg', ?) ON DUPLICATE KEY UPDATE setting_value = ?");
                        $stmt->execute([$val, $val]);
                    }
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

    /**
     * Converts an image to WebP format
     */
    private function convertToWebP(string $source, string $destination, int $quality = 80): bool {
        $info = getimagesize($source);
        if (!$info) return false;

        $mime = $info['mime'];
        switch ($mime) {
            case 'image/jpeg': $img = imagecreatefromjpeg($source); break;
            case 'image/png': 
                $img = imagecreatefrompng($source); 
                imagepalettetotruecolor($img);
                imagealphablending($img, true);
                imagesavealpha($img, true);
                break;
            case 'image/gif': $img = imagecreatefromgif($source); break;
            case 'image/webp': $img = imagecreatefromwebp($source); break;
            default: return false;
        }

        if (!$img) return false;
        $success = imagewebp($img, $destination, $quality);
        imagedestroy($img);
        return $success;
    }
}
