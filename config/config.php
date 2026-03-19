<?php
declare(strict_types=1);

/**
 * Configuration Loader - High Compatibility Version
 */
define('SYSTEM_VERSION', 'v2.1.1');

// --- AUTOLOADER (Rule 1) ---
require_once __DIR__ . '/../includes/autoloader.php';

// --- SESSION SECURITY (Rule 6) ---
ini_set('session.use_strict_mode', '1');
ini_set('session.cookie_httponly', '1');
ini_set('session.use_only_cookies', '1');
ini_set('session.cookie_samesite', 'Lax');
// Use secure only if HTTPS is detected
if (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] === 'on' || $_SERVER['HTTPS'] === '1')) {
    ini_set('session.cookie_secure', '1');
}


function loadEnvDirectly($path) {
    if (!file_exists($path)) return;
    
    $content = file_get_contents($path);
    $lines = explode("\n", $content);
    
    foreach ($lines as $line) {
        $line = trim($line);
        // Skip empty lines or full-line comments
        if (!$line || strpos($line, '#') === 0) continue;
        
        if (strpos($line, '=') !== false) {
            // Split by the FIRST '='
            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);
            
            // Handle inline comments
            if (strpos($value, '#') !== false) {
                // Only split if '#' is after some value and not inside quotes
                // For simplicity, we just take everything before '#' and trim
                $value = trim(explode('#', $value)[0]);
            }
            
            // Remove optional quotes
            $value = trim($value, "\"' \t\n\r\0\x0B");
            
            // Set as Environment Variable and Constant
            if (!defined($name)) {
                define($name, $value);
            }
            $_ENV[$name] = $value;
            putenv("{$name}={$value}");
        }
    }
}

// Absolute path to the root .env file
$envPaths = [
    __DIR__ . '/../.env',
    __DIR__ . '/.env'
];

foreach ($envPaths as $path) {
    if (file_exists($path)) {
        loadEnvDirectly($path);
    }
}

// --- REDIS SESSION HANDLER ---
if (extension_loaded('redis') && !empty($_ENV['REDIS_HOST'] ?? '')) {
    $redis_host = $_ENV['REDIS_HOST'];
    $redis_port = $_ENV['REDIS_PORT'] ?? '6379';
    $redis_pass = $_ENV['REDIS_PASSWORD'] ?? '';
    
    $save_path = "tcp://{$redis_host}:{$redis_port}";
    if ($redis_pass) {
        $save_path .= "?auth=" . urlencode($redis_pass);
    }
    
    ini_set('session.save_handler', 'redis');
    ini_set('session.save_path', $save_path);
}

// Essential constants - These MUST be in .env or the system environment.
$required_env = ['DB_HOST', 'DB_NAME', 'DB_USER', 'DB_PASS', 'DB_PORT', 'DB_CHARSET', 'APP_TIMEZONE'];
foreach ($required_env as $key) {
    // If not in $_ENV but in system environment, populate $_ENV
    if (!isset($_ENV[$key])) {
        $val = getenv($key);
        if ($val !== false) {
            $_ENV[$key] = $val;
        }
    }

    if (!defined($key)) {
        define($key, $_ENV[$key] ?? '');
    }
}

date_default_timezone_set(defined('APP_TIMEZONE') && !empty(APP_TIMEZONE) ? APP_TIMEZONE : 'America/Sao_Paulo');
if (!defined('APP_DATE_FORMAT')) define('APP_DATE_FORMAT', 'd/m/Y H:i');

// --- BASE URL DETECTION ---
$protocol = 'http';
if (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] === 'on' || $_SERVER['HTTPS'] === 1)) {
    $protocol = 'https';
} elseif (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
    $protocol = 'https';
}

$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$base_url = $protocol . "://" . $host;

// Calculate base path from config.php location relative to document root
$config_dir = str_replace('\\', '/', __DIR__);
$doc_root = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT'] ?? '');
$base_path = '';

if (!empty($doc_root)) {
    // If config is inside doc_root, it's NOT the public/ folder structure usually
    if (strpos($config_dir, $doc_root) === 0) {
        $base_path = substr($config_dir, strlen($doc_root));
        $base_path = str_replace('/config', '', $base_path);
    } else {
        // If config is one level UP from doc_root (classic public/ structure)
        $parent_config = dirname($config_dir);
        if ($parent_config === $doc_root || strpos($doc_root, $config_dir) === false) {
             $base_path = ''; // Root is already 'public/'
        }
    }
}

// Fallback logic for systems without DOCUMENT_ROOT set correctly
if (empty($base_path) && !empty($_SERVER['SCRIPT_NAME'])) {
    $script_name = $_SERVER['SCRIPT_NAME'];
    $base_path = dirname($script_name);
    
    // Clean up subfolders from base_path
    $base_path = str_replace(['/admin', '/app', '/api'], '', $base_path);
}

$base_path = rtrim(str_replace('\\', '/', $base_path), '/');

if (!defined('SITE_URL')) define('SITE_URL', $base_url . $base_path);
if (!defined('MP_WEBHOOK_URL')) define('MP_WEBHOOK_URL', SITE_URL . '/api/v1/webhook_mp.php');
