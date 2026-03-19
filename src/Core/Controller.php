<?php
declare(strict_types=1);

namespace App\Core;

class Controller {
    /**
     * Render a view with optional data
     * 
     * @param string $view View name (e.g. 'app/dashboard')
     * @param array $data Data to be extracted into the view
     * @param bool $withLayout Whether to include header/footer
     */
    protected function render(string $view, array $data = [], bool $withLayout = true) {
        global $pdo, $platform_settings, $current_page;
        
        // Make essential globals available in view
        $data['pdo'] = $pdo;
        $data['platform_settings'] = $platform_settings;
        $data['current_page'] = $current_page;
        $data['SITE_URL'] = SITE_URL;

        extract($data);
        $viewPath = __DIR__ . "/../Views/" . $view . ".php";
        
        if (!file_exists($viewPath)) {
            die("View $view not found at $viewPath");
        }

        if ($withLayout) {
            require_once __DIR__ . '/../../includes/header.php';
        }

        require $viewPath;

        if ($withLayout) {
            require_once __DIR__ . '/../../includes/footer.php';
        }
    }

    /**
     * Standardized JSON Response
     */
    protected function jsonResponse(array $data, int $code = 200) {
        header('Content-Type: application/json');
        http_response_code($code);
        echo json_encode($data);
        exit;
    }

    /**
     * Asset Versioning Helper
     */
    public static function asset(string $path): string {
        $version = defined('SYSTEM_VERSION') ? SYSTEM_VERSION : time();
        $connector = strpos($path, '?') !== false ? '&' : '?';
        return SITE_URL . '/' . ltrim($path, '/') . $connector . "v=" . $version;
    }
}
