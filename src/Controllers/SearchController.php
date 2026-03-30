<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use Auth;

class SearchController extends Controller {
    public function globalSearch(): void {
        $query = trim($_GET['q'] ?? '');
        if (strlen($query) < 2) {
            $this->jsonResponse(['success' => true, 'results' => []]);
            return;
        }

        $results = [];
        $searchTerm = "%$query%";

        // 1. Search Users
        try {
            $users = Database::fetchAll(
                "SELECT id, name, email, username, role FROM cp_users 
                 WHERE name LIKE ? 
                 OR email LIKE ? 
                 OR username LIKE ? 
                 OR phone LIKE ? 
                 OR city LIKE ?
                 OR state LIKE ?
                 OR neighborhood LIKE ?
                 LIMIT 10",
                [$searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm]
            );
            foreach ($users as $u) {
                $results[] = [
                    'type' => 'user',
                    'name' => (!empty($u['name']) ? $u['name'] : $u['username']),
                    'sub' => $u['email'] . ' (@' . $u['username'] . ') - ' . ucfirst($u['role']),
                    'url' => SITE_URL . '/users?id=' . $u['id'],
                    'icon' => 'user'
                ];
            }
        } catch (\Exception $e) {
            error_log("SearchController::Users error: " . $e->getMessage());
        }

        // 2. Search Logs (if Admin)
        if (Auth::isAdmin()) {
            try {
                $logs = Database::fetchAll(
                    "SELECT action, description, created_at FROM cp_logs WHERE action LIKE ? OR description LIKE ? ORDER BY created_at DESC LIMIT 10",
                    [$searchTerm, $searchTerm]
                );
                foreach ($logs as $l) {
                    $results[] = [
                        'type' => 'log',
                        'name' => 'Ação: ' . $l['action'],
                        'sub' => $l['description'] . ' (' . date('d/m H:i', strtotime($l['created_at'])) . ')',
                        'url' => SITE_URL . '/logs?action=' . urlencode($l['action']),
                        'icon' => 'terminal'
                    ];
                }
            } catch (\Exception $e) {
                error_log("SearchController::Logs error: " . $e->getMessage());
            }

            // 3. System Settings
            try {
                // Map common settings to their tabs
                $settingTabs = [
                    'system_name' => 'general',
                    'system_logo' => 'general',
                    'enable_system_logs' => 'general',
                    'system_theme' => 'themes',
                    'security_max_attempts' => 'security',
                    'security_lockout_time' => 'security'
                ];

                $settings = Database::fetchAll(
                    "SELECT setting_key, setting_value FROM cp_settings WHERE (setting_key LIKE ? OR setting_value LIKE ?) AND setting_key NOT LIKE '%pass%' LIMIT 10",
                    [$searchTerm, $searchTerm]
                );
                foreach ($settings as $s) {
                    $tab = $settingTabs[$s['setting_key']] ?? 'general';
                    $results[] = [
                        'type' => 'setting',
                        'name' => 'Config: ' . $s['setting_key'],
                        'sub' => $s['setting_value'],
                        'url' => SITE_URL . '/settings?tab=' . $tab . '&highlight=' . $s['setting_key'],
                        'icon' => 'settings'
                    ];
                }
            } catch (\Exception $e) {
                error_log("SearchController::Settings error: " . $e->getMessage());
            }
        }

        // 4. Integrations-like (searching keys specifically related to integrations)
        if (Auth::isAdmin()) {
            try {
                $integrations = Database::fetchAll(
                    "SELECT setting_key, setting_value FROM cp_settings WHERE setting_key LIKE ? AND setting_key LIKE 'smtp_%' LIMIT 5",
                    ['%' . $query . '%']
                );
                foreach ($integrations as $i) {
                    $results[] = [
                        'type' => 'setting',
                        'name' => 'Integração: ' . $i['setting_key'],
                        'sub' => 'Configuração de E-mail / Conexão',
                        'url' => SITE_URL . '/integrations',
                        'icon' => 'plug'
                    ];
                }
            } catch (\Exception $e) {}
        }

        $this->jsonResponse(['success' => true, 'results' => $results]);
    }
}
