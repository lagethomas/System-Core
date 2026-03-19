<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use Auth;
use ThemeHelper;
use Cache;

class SettingsController extends Controller {
    public function index() {
        Auth::requireAdmin();
        
        global $pdo;
        require_once __DIR__ . '/../../../includes/theme_helper.php';
        
        $active_tab = $_GET['tab'] ?? 'general';

        // Process POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['save_general'])) {
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
                $theme = $_POST['system_theme'] ?? 'gold-black';
                $stmt = $pdo->prepare("INSERT INTO cp_settings (setting_key, setting_value) VALUES ('system_theme', ?) ON DUPLICATE KEY UPDATE setting_value = ?");
                $stmt->execute([$theme, $theme]);
                Cache::delete('platform_settings');
                header("Location: " . SITE_URL . "/settings?tab=themes&msg=updated");
                exit;
            }
        }

        // Fetch Current Settings
        $stmt = $pdo->prepare("SELECT setting_key, setting_value FROM cp_settings");
        $stmt->execute();
        $settings = $stmt->fetchAll(\PDO::FETCH_KEY_PAIR);

        return $this->render('admin/settings', [
            'settings' => $settings,
            'active_tab' => $active_tab
        ]);
    }
}
