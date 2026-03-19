<?php
/**
 * Error Handler and Logger
 */

// Enable error reporting
error_reporting(E_ALL);
// Disable error display in production
ini_set('display_errors', 1); // Set to 1 for development
ini_set('log_errors', 1);
ini_set('error_log', dirname(__DIR__) . '/logs/php_errors.log');

// Function to check if logs are enabled (simplified for now)
function areLogsEnabled() {
    return true;
}

// Custom error handler
function customErrorHandler($errno, $errstr, $errfile, $errline) {
    if (!areLogsEnabled()) {
        return true;
    }
    
    $logFile = dirname(__DIR__) . '/logs/php_errors.log';
    $timestamp = date('Y-m-d H:i:s');
    $message = "[$timestamp] Error [$errno]: $errstr in $errfile on line $errline\n";
    
    // Ensure logs directory exists
    $logsDir = dirname(__DIR__) . '/logs';
    if (!is_dir($logsDir)) {
        mkdir($logsDir, 0755, true);
    }
    
    error_log($message, 3, $logFile);
    
    return false; // Let PHP continue with default handler if needed
}

// Custom exception handler
function customExceptionHandler($exception) {
    $logFile = dirname(__DIR__) . '/logs/php_errors.log';
    $timestamp = date('Y-m-d H:i:s');
    $message = "[$timestamp] Exception: " . $exception->getMessage() . " in " . $exception->getFile() . " on line " . $exception->getLine() . "\n";
    $message .= "Stack trace:\n" . $exception->getTraceAsString() . "\n\n";
    
    error_log($message, 3, $logFile);
    
    if (!ini_get('display_errors')) {
        echo "Ocorreu um erro inesperado. Por favor, tente novamente.";
    }
}

// Set handlers
set_error_handler('customErrorHandler');
set_exception_handler('customExceptionHandler');

// Function to log custom messages
function logMessage($message, $level = 'INFO') {
    $logFile = dirname(__DIR__) . '/logs/app.log';
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "[$timestamp] [$level] $message\n";
    
    $logsDir = dirname(__DIR__) . '/logs';
    if (!is_dir($logsDir)) {
        mkdir($logsDir, 0755, true);
    }
    
    error_log($logEntry, 3, $logFile);
}
