<?php
require_once __DIR__ . '/../../includes/DB.php';
$stmt = $pdo->query("SELECT * FROM cp_settings WHERE setting_key = 'system_theme'");
var_dump($stmt->fetch());
