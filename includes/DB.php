<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/config/config.php';
require_once __DIR__ . '/helpers/Cache.php';

/**
 * Database Singleton - Professional Connection Management
 */

class DB {
    private static ?PDO $instance = null;

    /**
     * Get the PDO instance (Singleton)
     */
    public static function getInstance(): PDO {
        if (self::$instance === null) {
            self::connect();
        }
        return self::$instance;
    }

    /**
     * Connect to database
     */
    private static function connect(): void {
        // Error logging setup
        $logsDir = __DIR__ . '/../logs';
        if (!is_dir($logsDir)) {
            mkdir($logsDir, 0755, true);
        }

        $is_production = (defined('APP_ENV') && APP_ENV === 'production');
        error_reporting(E_ALL);

        // --- TIMEZONE CONFIGURATION (Priority to .env) ---
        $app_tz = defined('APP_TIMEZONE') && !empty(APP_TIMEZONE) ? APP_TIMEZONE : 'America/Sao_Paulo';
        date_default_timezone_set($app_tz);

        if (!defined('APP_DATE_FORMAT')) define('APP_DATE_FORMAT', 'd/m/Y H:i');
        ini_set('display_errors', $is_production ? '0' : '1');
        ini_set('log_errors', '1');
        ini_set('error_log', $logsDir . '/php_errors.log');

        $db_host = $_ENV['DB_HOST'] ?? '';
        $db_port = $_ENV['DB_PORT'] ?? '3306';
        $db_name = $_ENV['DB_NAME'] ?? '';
        $db_user = $_ENV['DB_USER'] ?? '';
        $db_pass = $_ENV['DB_PASS'] ?? '';
        $db_charset = $_ENV['DB_CHARSET'] ?? 'utf8mb4';

        $dsn = "mysql:host={$db_host};port={$db_port};dbname={$db_name};charset={$db_charset}";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        // Conditional assignment for MYSQL_ATTR_INIT_COMMAND
        if (defined('PDO::MYSQL_ATTR_INIT_COMMAND')) {
            $options[PDO::MYSQL_ATTR_INIT_COMMAND] = "SET NAMES {$db_charset}";
        }

        try {
            self::$instance = new PDO($dsn, $db_user, $db_pass, $options);
            
            // Synchronize MySQL timezone with App (Force APP_TIMEZONE - Rule 39)
            // Use the offset to ensure compatibility even without MySQL timezone tables
            $tz_offset = date('P'); 
            self::$instance->exec("SET time_zone = '{$tz_offset}'");
            
        } catch (\PDOException $e) {
            error_log("Database Connection Error: " . $e->getMessage());
            die(self::showFatalError($e->getMessage()));
        }
    }

    /**
     * Displays a styled error message for connection failures
     */
    private static function showFatalError(string $msg): string {
        return "<div style='padding: 20px; background: #fee2e2; color: #b91c1c; border-radius: 8px; margin: 20px; font-family: sans-serif; border: 1px solid #fecaca;'>
                    <h3 style='margin-top:0'>Erro de Conexão Crítico</h3>
                    <p>Não foi possível estabelecer uma conexão com o banco de dados.</p>
                    <small>Detalhes: " . htmlspecialchars($msg) . "</small>
                </div>";
    }

    /**
     * Prevents cloning/unserialization
     */
    private function __construct() {}
    private function __clone() {}
}

// Instantiate global $pdo for backward compatibility
$pdo = DB::getInstance();

// --- LOAD PLATFORM SETTINGS (with cache) ---
$platform_settings = Cache::get('platform_settings');

if ($platform_settings === null) {
    try {
        $stmt = $pdo->query("SELECT setting_key, setting_value FROM cp_settings");
        $platform_settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        Cache::set('platform_settings', $platform_settings, 300); // 5 min TTL
    } catch (\Exception $e) {
        $platform_settings = [];
    }
}

// --- LIVE DEBUG LOG ---
try {
    if (isset($platform_settings['security_single_session']) && class_exists('Logger')) {
        $st = $platform_settings['security_single_session'] === '1' ? 'LIGADO' : 'DESLIGADO';
        // Only log once per request if needed, but here it's fine
        // error_log("Settings Loader: security_single_session is $st");
    }
} catch (\Exception $e) {}
// ----------------------

// --- CONTROL SYSTEM LOGS ---
$logsEnabled = ($platform_settings['enable_system_logs'] ?? '1') === '1';
if (!$logsEnabled) {
    ini_set('log_errors', '0');
}
