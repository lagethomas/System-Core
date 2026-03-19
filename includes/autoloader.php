<?php
declare(strict_types=1);

/**
 * SaaSFlow Autoloader - PSR-4 Compliant
 */
spl_autoload_register(function ($class) {
    // Base directory for the project
    $base_dir = __DIR__ . '/';

    // 1. Handle Namespaces (PSR-4)
    $prefixes = [
        'App\\' => $base_dir,
        'PHPMailer\\PHPMailer\\' => $base_dir . 'PHPMailer/',
        'Psr\\Log\\' => $base_dir . 'Psr/Log/',
    ];

    foreach ($prefixes as $prefix => $dir) {
        $len = strlen($prefix);
        if (strncmp($prefix, $class, $len) === 0) {
            $relative_class = substr($class, $len);
            $file = $dir . str_replace('\\', '/', $relative_class) . '.php';
            if (file_exists($file)) {
                require_once $file;
                return;
            }
        }
    }

    // 2. Fallback for direct class name mapping (legacy/no namespace)
    $search_dirs = [
        $base_dir,
        $base_dir . 'repositories/',
        $base_dir . 'helpers/',
    ];

    foreach ($search_dirs as $dir) {
        $file = $dir . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});
