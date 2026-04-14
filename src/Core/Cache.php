<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Basic Cache Handler
 */
class Cache {
    public static function delete(string $key): void {
        if (class_exists('\Cache')) {
            \Cache::delete($key);
        }
    }

    public static function get(string $key) {
        if (class_exists('\Cache')) {
            return \Cache::get($key);
        }
        return null;
    }

    public static function set(string $key, $value, int $ttl = 3600): void {
        if (class_exists('\Cache')) {
            \Cache::set($key, $value, $ttl);
        }
    }
}
