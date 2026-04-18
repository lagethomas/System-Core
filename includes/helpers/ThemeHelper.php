<?php
declare(strict_types=1);
/**
 * Theme Helper - Handles dynamic theme discovery
 */

class ThemeHelper {
    private static $themes_dir = __DIR__ . '/../../public/assets/css/theme/';

    public static function getAvailableThemes() {
        $themes = [];
        $baseDir = dirname(__DIR__, 2); // Root of the project
        $dir = $baseDir . '/public/assets/css/theme';
        
        if (!is_dir($dir)) return [];
        
        $files = glob($dir . '/*.css');

        foreach ($files as $file) {
            $filename = basename($file, '.css');
            $content = file_get_contents($file);
            
            // Nice Name: "gold-black" -> "Gold Black"
            $nice_name = ucwords(str_replace('-', ' ', $filename));
            
            // Extract Primary Color
            $primary_color = 'var(--primary)'; // Default
            if (preg_match('/--primary:\s*([^;]+);/', $content, $matches)) {
                $primary_color = trim($matches[1]);
            }

            // Extract Bg Color
            $bg_color = '#16191e'; // Default dark
            if (preg_match('/--bg-surface:\s*([^;]+);/', $content, $matches)) {
                $bg_color = trim($matches[1]);
            }

            $themes[$filename] = [
                'name' => $nice_name,
                'color' => $primary_color,
                'bg' => $bg_color
            ];
        }

        // Ensure gold-black is first if exists
        if (isset($themes['gold-black'])) {
            $gold = $themes['gold-black'];
            unset($themes['gold-black']);
            return array_merge(['gold-black' => $gold], $themes);
        }

        return $themes;
    }
}