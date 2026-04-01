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
        $company_id = Auth::companyId();
        $isAdmin = Auth::isAdmin();

        // 1. Search Users
        try {
            $sql = "SELECT id, name, email, username, role FROM cp_users WHERE (name LIKE ? OR email LIKE ? OR username LIKE ? OR phone LIKE ?) ";
            $params = [$searchTerm, $searchTerm, $searchTerm, $searchTerm];
            
            // Apply Company Filter (Rule 39)
            if ($company_id && !$isAdmin) {
                $sql .= " AND company_id = ? ";
                $params[] = $company_id;
            }
            
            $sql .= " LIMIT 10";
            $users = Database::fetchAll($sql, $params);
            
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

        // 2. Search Logs
        try {
            if ($isAdmin) {
                // Global Logs for Admin
                $logs = Database::fetchAll(
                    "SELECT action, description, created_at FROM cp_logs WHERE action LIKE ? OR description LIKE ? ORDER BY created_at DESC LIMIT 10",
                    [$searchTerm, $searchTerm]
                );
            } elseif ($company_id) {
                // Company Logs for Tenant
                $logs = Database::fetchAll(
                    "SELECT l.action, l.description, l.created_at 
                     FROM cp_logs l 
                     JOIN cp_users u ON l.user_id = u.id 
                     WHERE u.company_id = ? AND (l.action LIKE ? OR l.description LIKE ?) 
                     ORDER BY l.created_at DESC LIMIT 10",
                    [$company_id, $searchTerm, $searchTerm]
                );
            } else {
                $logs = [];
            }

            foreach ($logs as $l) {
                $results[] = [
                    'type' => 'log',
                    'name' => 'Ação: ' . $l['action'],
                    'sub' => $l['description'] . ' (' . date('d/m H:i', strtotime($l['created_at'])) . ')',
                    'url' => $isAdmin ? SITE_URL . '/logs?action=' . urlencode($l['action']) : '#',
                    'icon' => 'terminal'
                ];
            }
        } catch (\Exception $e) {
            error_log("SearchController::Logs error: " . $e->getMessage());
        }

        // ──────────────────────────────────────────────────────────
        // SaaS Admin Specific Search (Not for tenants)
        // ──────────────────────────────────────────────────────────
        if ($isAdmin) {
            // 3. System Settings
            try {
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
            } catch (\Exception $e) {}

            // 5. Search Companies
            try {
                $companies = Database::fetchAll(
                    "SELECT id, name, slug, email, document FROM cp_companies 
                     WHERE (name LIKE ? OR slug LIKE ? OR email LIKE ? OR document LIKE ?) 
                     LIMIT 10",
                    [$searchTerm, $searchTerm, $searchTerm, $searchTerm]
                );
                foreach ($companies as $c) {
                    $results[] = [
                        'type' => 'company',
                        'name' => 'Empresa: ' . $c['name'],
                        'sub' => $c['email'] . ' (/' . $c['slug'] . ')',
                        'url' => SITE_URL . '/admin/companies?id=' . $c['id'],
                        'icon' => 'building'
                    ];
                }
            } catch (\Exception $e) {}
            
            // 6. Search Plans
            try {
                $plans = Database::fetchAll(
                    "SELECT id, name, base_price FROM cp_plans 
                     WHERE name LIKE ? 
                     LIMIT 5",
                    [$searchTerm]
                );
                foreach ($plans as $p) {
                    $results[] = [
                        'type' => 'plan',
                        'name' => 'Plano: ' . $p['name'],
                        'sub' => 'Valor Base: R$ ' . number_format((float)$p['base_price'], 2, ',', '.'),
                        'url' => SITE_URL . '/admin/plans?id=' . $p['id'],
                        'icon' => 'package'
                    ];
                }
            } catch (\Exception $e) {}
        }

        $this->jsonResponse(['success' => true, 'results' => $results]);
    }
}
