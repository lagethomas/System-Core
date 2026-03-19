<?php
declare(strict_types=1);

/**
 * UrlHelper - Management of system URLs and redirects
 */
class UrlHelper {
    /**
     * Get the full URL for a internal path
     */
    public static function url(string $path = ''): string {
        $base = defined('SITE_URL') ? SITE_URL : '';
        return rtrim($base, '/') . '/' . ltrim($path, '/');
    }

    /**
     * Safe redirect
     */
    public static function redirect(string $path): void {
        $url = self::url($path);
        if (!headers_sent()) {
            header("Location: $url");
            exit;
        }
        echo "<script>window.location.href='$url';</script>";
        exit;
    }

    /**
     * Get asset URL with versioning to bust cache
     */
    public static function asset(string $path): string {
        $url = self::url('assets/' . ltrim($path, '/'));
        $v = defined('SYSTEM_VERSION') ? SYSTEM_VERSION : time();
        return $url . '?v=' . $v;
    }
}

// Global functions for convenience
if (!function_exists('url')) {
    function url(string $path = ''): string {
        return UrlHelper::url($path);
    }
}

if (!function_exists('redirect')) {
    function redirect(string $path): void {
        UrlHelper::redirect($path);
    }
}
