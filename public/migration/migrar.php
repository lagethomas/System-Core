<?php
/**
 * Database Migration - Adds items_per_page and security_log_limit settings
 */

require_once __DIR__ . '/../../src/Core/Autoloader.php';
App\Core\Autoloader::register();

// Load DB
require_once __DIR__ . '/../../includes/DB.php';

$pdo = DB::getInstance();

echo "Starting migration...<br>";

try {
    // Add items_per_page if not exists
    $stmt = $pdo->prepare("SELECT setting_value FROM cp_settings WHERE setting_key = 'items_per_page'");
    $stmt->execute();
    if ($stmt->rowCount() == 0) {
        $pdo->prepare("INSERT INTO cp_settings (setting_key, setting_value) VALUES ('items_per_page', '25')")->execute();
        echo "✅ Created setting: items_per_page (25)<br>";
    }

    // Handle security_log_limit
    $stmt = $pdo->prepare("SELECT setting_value FROM cp_settings WHERE setting_key = 'security_log_limit'");
    $stmt->execute();
    $currentLimit = $stmt->fetchColumn();

    if ($currentLimit === false) {
        $pdo->prepare("INSERT INTO cp_settings (setting_key, setting_value) VALUES ('security_log_limit', '500')")->execute();
        echo "✅ Created setting: security_log_limit (500)<br>";
    } else {
        $limitInt = (int)$currentLimit;
        if ($limitInt > 500 || $currentLimit === '' || $currentLimit === null) {
            $pdo->prepare("UPDATE cp_settings SET setting_value = '500' WHERE setting_key = 'security_log_limit'")->execute();
            echo "✅ Updated existing security_log_limit to 500 (was $currentLimit).<br>";
        } else {
            echo "ℹ️ security_log_limit is already within acceptable range ($currentLimit).<br>";
        }
    }

    echo "<br><b>Migration completed successfully!</b>";
} catch (Exception $e) {
    die("<br><b style='color:red;'>Error during migration: " . $e->getMessage() . "</b>");
}
