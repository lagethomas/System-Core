<?php
/**
 * Logs compatibility shim.
 * This file exists to maintain backward compatibility for any legacy code or
 * server deployments that may reference includes/logs.php directly.
 * The actual Logger class lives in includes/helpers/Logger.php (loaded via autoloader).
 */
if (!class_exists('Logger')) {
    require_once __DIR__ . '/../src/Helpers/Logger.php';
    class_alias(\App\Helpers\Logger::class, 'Logger');
}
if (!class_exists('LogRepository')) {
    require_once __DIR__ . '/repositories/LogRepository.php';
}
