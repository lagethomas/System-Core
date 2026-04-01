<?php
require_once __DIR__ . '/PlanRepository.php';

class CompanyRepository {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    private function debugLog($msg) {
        if (defined('SYSTEM_LOGS_ENABLED') && SYSTEM_LOGS_ENABLED) {
            error_log($msg);
        }
    }

    /**
     * Synchronize company expiration date based on the latest paid invoice
     */
    public function synchronizeExpiration($companyId) {
        $stmt = $this->pdo->prepare("
            SELECT due_date FROM cp_invoices 
            WHERE company_id = ? AND status = 'paid' AND type = 'recurring' 
            ORDER BY due_date DESC LIMIT 1
        ");
        $stmt->execute([$companyId]);
        $latestPaidDueDate = $stmt->fetchColumn();

        if ($latestPaidDueDate) {
            // New expiration is 30 days after the last paid period start (due_date)
            $newExpiration = date('Y-m-d', strtotime($latestPaidDueDate . ' + 30 days'));
            $stmt = $this->pdo->prepare("UPDATE cp_companies SET expires_at = ? WHERE id = ?");
            $stmt->execute([$newExpiration, $companyId]);

            // Immediately generate the NEXT invoice so it's ready for the next period
            $this->generateMonthlyInvoice($companyId);
            
            return $newExpiration;
        }
        return null;
    }

    /**
     * Check if slug is already taken
     */
    public function isSlugTaken($slug, $excludeId = null) {
        $sql = "SELECT id FROM cp_companies WHERE slug = ?";
        $params = [$slug];
        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch() ? true : false;
    }

    /**
     * Get a simple list of all companies (ID and Name)
     */
    public function getAllSimple() {
        return $this->pdo->query("SELECT id, name FROM cp_companies ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get company by ID
     */
    public function getById($id) {
        $stmt = $this->pdo->prepare("
            SELECT 
                c.*, 
                p.id as plan_id_actual,
                p.name as plan_name,
                p.base_price,
                p.included_users,
                p.extra_user_price
            FROM cp_companies c
            LEFT JOIN cp_plans p ON c.plan_id = p.id
            WHERE c.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get all companies
     */
    public function getAll() {
        $stmt = $this->pdo->prepare("
            SELECT c.*, p.name as plan_name 
            FROM cp_companies c
            LEFT JOIN cp_plans p ON c.plan_id = p.id
            ORDER BY c.name ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Update company logo
     */
    public function updateLogo($id, $logoPath) {
        $stmt = $this->pdo->prepare("UPDATE cp_companies SET logo = ? WHERE id = ?");
        return $stmt->execute([$logoPath, $id]);
    }

    /**
     * Update company background image
     */
    public function updateBackground($id, $bgPath) {
        $stmt = $this->pdo->prepare("UPDATE cp_companies SET background_image = ? WHERE id = ?");
        return $stmt->execute([$bgPath, $id]);
    }

    /**
     * Update company status/expiration
     */
    public function updateStatus($id, $active, $expiresAt) {
        $stmt = $this->pdo->prepare("UPDATE cp_companies SET active = ?, expires_at = ? WHERE id = ?");
        return $stmt->execute([$active, $expiresAt, $id]);
    }

    /**
     * Update company cancellation settings
     */
    public function updateCancellationSettings($id, $allow, $limit) {
        $stmt = $this->pdo->prepare("UPDATE cp_companies SET allow_client_cancellation = ?, cancellation_time_limit = ? WHERE id = ?");
        return $stmt->execute([$allow, $limit, $id]);
    }

    /**
     * Update company theme
     */
    public function updateTheme($id, $theme) {
        $stmt = $this->pdo->prepare("UPDATE cp_companies SET theme = ? WHERE id = ?");
        return $stmt->execute([$theme, $id]);
    }

    /**
     * Update Mercado Pago settings
     */
    public function updateMercadoPagoSettings($id, $enabled, $accessToken, $publicKey) {
        $stmt = $this->pdo->prepare("UPDATE cp_companies SET mp_enabled = ?, mp_access_token = ?, mp_public_key = ? WHERE id = ?");
        return $stmt->execute([$enabled, $accessToken, $publicKey, $id]);
    }

    /**
     * Update basic company contact/info from owner dashboard
     */
    public function updateBasicInfo($id, $data) {
        $stmt = $this->pdo->prepare("
            UPDATE cp_companies SET 
            name = ?, document = ?, phone = ?, email = ?
            WHERE id = ?
        ");
        return $stmt->execute([
            $data['name'], $data['document'] ?? null, $data['phone'] ?? null, $data['email'] ?? null,
            $id
        ]);
    }

    /**
     * Save/Update company info
     */
    public function save($data) {
        if (isset($data['id']) && $data['id']) {
            $existing = $this->getById($data['id']);
            
            $stmt = $this->pdo->prepare("
                UPDATE cp_companies SET 
                name = ?, slug = ?, custom_domain = ?, document = ?, phone = ?, email = ?, 
                theme_color = ?, theme = ?, active = ?, expires_at = ?, plan_id = ?, partner_id = ?
                WHERE id = ?
            ");
            $res = $stmt->execute([
                $data['name'], 
                $data['slug'], 
                $data['custom_domain'] ?? $existing['custom_domain'] ?? null, 
                $data['document'] ?? $existing['document'] ?? null, 
                $data['phone'] ?? $existing['phone'] ?? null, 
                $data['email'] ?? $existing['email'] ?? null,
                $data['theme_color'] ?? $existing['theme_color'] ?? 'var(--primary)', 
                $data['theme'] ?? $existing['theme'] ?? 'default', 
                isset($data['active']) ? $data['active'] : ($existing['active'] ?? 1), 
                isset($data['expires_at']) ? $data['expires_at'] : ($existing['expires_at'] ?? null), 
                $data['plan_id'] ?? $existing['plan_id'] ?? null,
                $data['partner_id'] ?? $existing['partner_id'] ?? null,
                $data['id']
            ]);

            if ($res) {
                // Refresh peak count to current state to ensure extras are caught
                $this->incrementPeakUsers($data['id']);
                return (int)$data['id'];
            }
            return false;
        } else {
            // Get Plan trial days if not provided
            $expiresAt = $data['expires_at'] ?? null;
            if (!$expiresAt && isset($data['plan_id'])) {
                $stmt_plan = $this->pdo->prepare("SELECT trial_days FROM cp_plans WHERE id = ?");
                $stmt_plan->execute([$data['plan_id']]);
                $trial = $stmt_plan->fetchColumn() ?: 7;
                $expiresAt = date('Y-m-d', strtotime("+$trial days"));
            }

            $stmt = $this->pdo->prepare("
                INSERT INTO cp_companies (name, slug, custom_domain, document, phone, email, theme_color, theme, active, expires_at, plan_id, partner_id) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $res = $stmt->execute([
                $data['name'], $data['slug'], $data['custom_domain'] ?? null, $data['document'] ?? null, $data['phone'] ?? null, $data['email'] ?? null,
                $data['theme_color'] ?? 'var(--primary)', $data['theme'] ?? 'default', $data['active'] ?? 1, $expiresAt, $data['plan_id'] ?? null,
                $data['partner_id'] ?? null
            ]);

            if ($res) {
                $id = $this->pdo->lastInsertId();
                // Refresh peak count immediately upon creation/plan association
                $this->resetPeakUsers($id);

                // Create a "PAID" invoice for the TRIAL period to show in history
                if (isset($data['plan_id'])) {
                    $planRepo = new PlanRepository($this->pdo);
                    $plan = $planRepo->getById($data['plan_id']);

                    if ($plan) {
                        // 1. Trial Invoice (Paid) - Representative of the first 15 days
                        $planName = $plan['name'];
                        $trialStart = date('d/m');
                        $trialEnd = date('d/m', strtotime($expiresAt));
                        $trialDesc = "Cortesia Trial - Pacote $planName ($trialStart a $trialEnd)";
                        
                        $stmt_inv = $this->pdo->prepare("
                            INSERT INTO cp_invoices (company_id, amount, status, type, due_date, paid_at, description) 
                            VALUES (?, 0.00, 'paid', 'single', ?, NOW(), ?)
                        ");
                        $stmt_inv->execute([$id, date('Y-m-d'), $trialDesc]);

                        // 2. First Paid Cycle (Pending) - To be paid until the end of trial
                        $basePrice = (float)$plan['base_price'];
                        $periodNextStart = date('d/m', strtotime($expiresAt));
                        $periodNextEnd = date('d/m', strtotime($expiresAt . ' + 30 days'));
                        $nextDesc = "Mensalidade SaaS ($periodNextStart a $periodNextEnd)";
                        
                        $stmt_next = $this->pdo->prepare("
                            INSERT INTO cp_invoices (company_id, amount, status, type, due_date, description) 
                            VALUES (?, ?, 'pending', 'recurring', ?, ?)
                        ");
                        $stmt_next->execute([$id, $basePrice, $expiresAt, $nextDesc]);
                    }
                }
                return $id;
            }
            return false;
        }
    }

    /**
     * Generate a monthly invoice for a company based on its plan and usage
     */
    public function generateMonthlyInvoice($companyId) {
        $this->debugLog("Billing: Generating invoice for company $companyId");
        $company = $this->getById($companyId);
        if (!$company || !$company['plan_id']) {
            $this->debugLog("Billing: Skipping generation - No company or plan found for ID $companyId");
            return false;
        }

        // Check if there is already a pending recurring invoice
        $stmt = $this->pdo->prepare("SELECT id FROM cp_invoices WHERE company_id = ? AND status = 'pending' AND type = 'recurring' LIMIT 1");
        $stmt->execute([$companyId]);
        if ($stmt->fetch()) {
            $this->debugLog("Billing: Skipping generation - Pending recurring invoice already exists for company $companyId");
            return false;
        }

        $basePrice = (float)$company['base_price'];
        $includedUsers = (int)$company['included_users'];
        $extraPrice = (float)$company['extra_user_price'];
        $peakUsers = (int)$company['peak_users_count'];

        $extraCount = max(0, $peakUsers - $includedUsers);
        $totalAmount = $basePrice + ($extraCount * $extraPrice);

        $this->debugLog("Billing: New Invoice Calculation - Base: $basePrice, Peak: $peakUsers, Included: $includedUsers, Extra: $extraCount, Total: $totalAmount");

        // Create Invoice
        $stmt = $this->pdo->prepare("
            INSERT INTO cp_invoices (company_id, amount, status, type, due_date, description) 
            VALUES (?, ?, 'pending', 'recurring', ?, ?)
        ");
        
        $dueDate = $company['expires_at'] ?: date('Y-m-d');
        $periodStart = date('d/m', strtotime($dueDate));
        $periodEnd = date('d/m', strtotime($dueDate . ' + 30 days'));
        $desc = "Mensalidade SaaS ($periodStart a $periodEnd)";
        if ($extraCount > 0) {
            $desc .= " + $extraCount usuários extras";
        }

        $res = $stmt->execute([$companyId, $totalAmount, $dueDate, $desc]);
        $this->debugLog("Billing: Invoice generation result for $companyId: " . ($res ? "Success" : "Failed"));
        return $res;
    }

    /**
     * Run background billing check for all active companies
     */
    public function checkAutoBilling($specificCompanyId = null) {
        $this->debugLog("Billing: checkAutoBilling started (" . ($specificCompanyId ? "Company: $specificCompanyId" : "All Companies") . ")");
        $ids = [];
        
        if ($specificCompanyId) {
            // Check if there is already a pending invoice to update, OR if it's near expiration
            $has_pending = $this->pdo->query("SELECT 1 FROM cp_invoices WHERE company_id = $specificCompanyId AND status = 'pending' AND type = 'recurring' LIMIT 1")->fetchColumn();
            
            if ($has_pending) {
                $ids = [$specificCompanyId];
            } else {
                $stmt = $this->pdo->prepare("SELECT id FROM cp_companies WHERE id = ? AND active = 1 AND plan_id IS NOT NULL AND expires_at <= DATE_ADD(CURDATE(), INTERVAL 10 DAY)");
                $stmt->execute([$specificCompanyId]);
                if ($stmt->fetch()) {
                    $ids = [$specificCompanyId];
                } else {
                    $this->debugLog("Billing: Specific company $specificCompanyId has no pending invoice and is not near expiration. Skipping.");
                    return;
                }
            }
        } else {
            // 1. Find companies whose access expires in 10 days or less
            $stmt = $this->pdo->query("
                SELECT id FROM cp_companies 
                WHERE active = 1 
                AND plan_id IS NOT NULL 
                AND expires_at <= DATE_ADD(CURDATE(), INTERVAL 10 DAY)
            ");
            $ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
        }
        
        $this->debugLog("Billing: Found " . count($ids) . " companies to check.");

        foreach ($ids as $id) {
            // Self-healing: Always refresh peak count before checking billing
            $this->incrementPeakUsers($id);

            // Check if there is already a pending RECURRING invoice
            $stmt_inv = $this->pdo->prepare("SELECT id FROM cp_invoices WHERE company_id = ? AND status = 'pending' AND type = 'recurring' LIMIT 1");
            $stmt_inv->execute([$id]);
            $existing_invoice = $stmt_inv->fetch();

            if ($existing_invoice) {
                $this->debugLog("Billing: Company $id has pending invoice #" . $existing_invoice['id'] . ". Triggering update.");
                // UPDATE valor da fatura existente se o número de usuários mudou
                $this->updatePendingInvoiceAmount($id, $existing_invoice['id']);
            } else {
                $this->debugLog("Billing: Company $id has NO pending recurring invoice. Generating new one.");
                // Cria nova fatura
                $this->generateMonthlyInvoice($id);
            }
        }
    }

    /**
     * Recalculate and update the amount of a pending invoice
     */
    private function updatePendingInvoiceAmount($companyId, $invoiceId) {
        $this->debugLog("Billing: updatePendingInvoiceAmount for Company $companyId, Invoice $invoiceId");
        $company = $this->getById($companyId);
        if (!$company) {
            $this->debugLog("Billing: Error - Company $companyId not found during update.");
            return;
        }

        $basePrice = (float)$company['base_price'];
        $includedUsers = (int)$company['included_users'];
        $extraPrice = (float)$company['extra_user_price'];
        $peakUsers = (int)$company['peak_users_count'];

        $extraCount = max(0, $peakUsers - $includedUsers);
        $totalAmount = $basePrice + ($extraCount * $extraPrice);

        $this->debugLog("Billing: Update Calculation for $companyId - Base: $basePrice, Peak: $peakUsers, Incl: $includedUsers, Extra: $extraCount, Total: $totalAmount");

        $desc = "Mensalidade SaaS";
        if ($extraCount > 0) {
            $desc .= " + $extraCount usuários extras";
        }

        $stmt = $this->pdo->prepare("UPDATE cp_invoices SET amount = ?, description = ? WHERE id = ?");
        $res = $stmt->execute([$totalAmount, $desc, $invoiceId]);
        $this->debugLog("Billing: Invoice update result for $invoiceId: " . ($res ? "Success" : "Failed"));
    }

    /**
     * Increment Peak Users count if current count is higher
     */
    public function incrementPeakUsers($companyId) {
        $stmt_list = $this->pdo->prepare("SELECT id, name, role FROM cp_users WHERE company_id = ?");
        $stmt_list->execute([$companyId]);
        $foundUsers = $stmt_list->fetchAll(PDO::FETCH_ASSOC);
        $currentCount = count($foundUsers);
        
        $userList = implode(', ', array_map(function($u) { return $u['name'] . " (" . $u['role'] . ")"; }, $foundUsers));
        $this->debugLog("Billing: incrementPeakUsers for $companyId. Found $currentCount users: [$userList]");

        $stmt = $this->pdo->prepare("
            UPDATE cp_companies 
            SET peak_users_count = GREATEST(peak_users_count, ?)
            WHERE id = ?
        ");
        $res = $stmt->execute([$currentCount, $companyId]);

        // Verify result
        $stmt_verify = $this->pdo->prepare("SELECT peak_users_count FROM cp_companies WHERE id = ?");
        $stmt_verify->execute([$companyId]);
        $newPeak = $stmt_verify->fetchColumn();
        $this->debugLog("Billing: Company $companyId Peak User Count is now: $newPeak (Result: " . ($res ? "Success" : "Failed") . ")");
        
        return $res;
    }

    /**
     * Reset Peak Users count to current count
     */
    public function resetPeakUsers($companyId) {
        $stmt = $this->pdo->prepare("
            UPDATE cp_companies 
            SET peak_users_count = (SELECT COUNT(*) FROM cp_users WHERE company_id = ?)
            WHERE id = ?
        ");
        return $stmt->execute([$companyId, $companyId]);
    }

    /**
     * Auto-lifecycle:
     * 1. Inactivate companies with invoice overdue >= 10 days
     * 2. Send inactive companies (inactive >= 30 days) to trash
     * 3. Permanently delete trashed companies (trashed >= 15 days), including owners and images
     */
    public function runLifecycle() {
        $this->debugLog("Lifecycle: Starting auto-lifecycle...");

        // Read configurable grace period from settings (default 10 days)
        $stmt_grace = $this->pdo->prepare("SELECT setting_value FROM cp_settings WHERE setting_key = 'billing_grace_days'");
        $stmt_grace->execute();
        $graceDays = (int)($stmt_grace->fetchColumn() ?: 10);
        $this->debugLog("Lifecycle: Grace period = $graceDays days.");

        // --- 1. Auto-inactivate if overdue invoice >= grace days ---
        $this->pdo->prepare("
            UPDATE cp_companies c
            SET c.active = 0,
                c.inactive_since = CURDATE()
            WHERE c.active = 1
            AND c.trashed_at IS NULL
            AND EXISTS (
                SELECT 1 FROM cp_invoices i
                WHERE i.company_id = c.id
                AND i.status = 'pending'
                AND i.due_date <= DATE_SUB(CURDATE(), INTERVAL ? DAY)
            )
            AND c.inactive_since IS NULL
        ")->execute([$graceDays]);

        // --- 2. Move to trash: inactive >= 30 days ---
        $this->pdo->exec("
            UPDATE cp_companies
            SET trashed_at = NOW(),
                active = 0
            WHERE active = 0
            AND trashed_at IS NULL
            AND inactive_since IS NOT NULL
            AND inactive_since <= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
        ");

        // --- 3. Full-permanent-delete: trashed >= 15 days ---
        $trashed = $this->pdo->query("
            SELECT id, name FROM cp_companies
            WHERE trashed_at IS NOT NULL
            AND trashed_at <= DATE_SUB(NOW(), INTERVAL 15 DAY)
        ")->fetchAll(PDO::FETCH_ASSOC);

        $deletedCount = 0;
        foreach ($trashed as $c) {
            if ($this->deleteCompanyData($c['id'])) {
                $deletedCount++;
                $this->debugLog("Lifecycle: Company #{$c['id']} ({$c['name']}) permanently deleted.");
            }
        }

        $this->debugLog("Lifecycle: Auto-lifecycle complete. $deletedCount companies permanently deleted.");
    }

    /**
     * Perform full data cleanup for a company
     */
    public function deleteCompanyData($id) {
        try {
            // Safety check: Don't delete if active or not expired
            $stmt = $this->pdo->prepare("SELECT active, expires_at, trashed_at FROM cp_companies WHERE id = ?");
            $stmt->execute([$id]);
            $check = $stmt->fetch();
            
            if (!$check) return false;
            
            // Allow deletion only if NOT active AND (expired OR in trash)
            $is_expired = (!empty($check['expires_at']) && strtotime($check['expires_at']) < time());
            if ($check['active'] && !$is_expired && !$check['trashed_at']) {
                return false; 
            }

            // 1. Get images from related tables before database deletion
            $images = [];
            
            // Company logo and bg
            $stmt = $this->pdo->prepare("SELECT logo, background_image FROM cp_companies WHERE id = ?");
            $stmt->execute([$id]);
            $comp = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($comp) {
                if (!empty($comp['logo'])) $images[] = $comp['logo'];
                if (!empty($comp['background_image'])) $images[] = $comp['background_image'];
            }

            // Products, Services, Packages
            $stmt = $this->pdo->prepare("SELECT image FROM cp_products WHERE company_id = ? AND image IS NOT NULL");
            $stmt->execute([$id]);
            $images = array_merge($images, $stmt->fetchAll(PDO::FETCH_COLUMN));

            $stmt = $this->pdo->prepare("SELECT image FROM cp_services WHERE company_id = ? AND image IS NOT NULL");
            $stmt->execute([$id]);
            $images = array_merge($images, $stmt->fetchAll(PDO::FETCH_COLUMN));

            $stmt = $this->pdo->prepare("SELECT image FROM cp_packages WHERE company_id = ? AND image IS NOT NULL");
            $stmt->execute([$id]);
            $images = array_merge($images, $stmt->fetchAll(PDO::FETCH_COLUMN));

            $this->pdo->beginTransaction();

            // 2. Delete Users (Owners/Staff) associated with this company
            // Partners have company_id = NULL so they are safe.
            $this->pdo->prepare("DELETE FROM cp_users WHERE company_id = ?")->execute([$id]);

            // 3. Delete Company record
            $this->pdo->prepare("DELETE FROM cp_companies WHERE id = ?")->execute([$id]);

            $this->pdo->commit();

            // 4. Physical deletion of files
            $rootDir = dirname(__DIR__, 2) . '/public';
            foreach ($images as $img) {
                if (empty($img)) continue;
                
                // Ensure we have a valid path starting from root
                $relativePath = ltrim($img, '/');
                $fullPath = $rootDir . DIRECTORY_SEPARATOR . $relativePath;
                
                if (file_exists($fullPath) && is_file($fullPath)) {
                    @unlink($fullPath);
                }
            }

            return true;
        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) $this->pdo->rollBack();
            $this->debugLog("Error deleting company data: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Mark a subscription payment as paid, update company access and assign commissions
     */
    public function markSubscriptionPaid($companyId, $gatewayId) {
        // 1. Find the oldest pending recurring invoice for this company
        $stmt = $this->pdo->prepare("SELECT id FROM cp_invoices WHERE company_id = ? AND status = 'pending' AND type = 'recurring' ORDER BY due_date ASC LIMIT 1");
        $stmt->execute([$companyId]);
        $invoiceId = $stmt->fetchColumn();

        if ($invoiceId) {
            // Mark invoice as paid
            $stmt = $this->pdo->prepare("UPDATE cp_invoices SET status = 'paid', paid_at = NOW() WHERE id = ?");
            $stmt->execute([$invoiceId]);

            // Renew access
            $this->synchronizeExpiration($companyId);

            // Assign commission
            $commHelper = __DIR__ . '/../helpers/CommissionHelper.php';
            if (file_exists($commHelper)) {
                require_once $commHelper;
                // Use dynamic call to satisfy IDE when file is optional
                call_user_func(['CommissionHelper', 'assignCommissionForInvoice'], $this->pdo, $invoiceId);
            }
            return true;
        }
        return false;
    }
    /**
     * Send email reminders for upcoming and overdue invoices
     */
    public function sendInvoiceReminders() {
        $this->debugLog("Reminders: Starting invoice reminder checks...");
        
        // Defensive check: Verify if the column 'last_reminder_date' exists
        try {
            $check = $this->pdo->query("SHOW COLUMNS FROM `cp_invoices` LIKE 'last_reminder_date'")->fetch();
            if (!$check) {
                $this->debugLog("Reminders: Column 'last_reminder_date' missing. Please run migrations.");
                return;
            }
        } catch (\Exception $e) {
            return;
        }

        // Fetch settings
        $stmt_set = $this->pdo->prepare("SELECT setting_key, setting_value FROM cp_settings WHERE setting_key IN ('days_before_notify', 'billing_grace_days')");
        $stmt_set->execute();
        $sets = $stmt_set->fetchAll(PDO::FETCH_KEY_PAIR);
        
        $daysBefore = (int)($sets['days_before_notify'] ?? 5);
        $graceDays = (int)($sets['billing_grace_days'] ?? 10);
        
        $today = date('Y-m-d');

        // 1. Upcoming Reminders (Invoice due in X days)
        $stmt_up = $this->pdo->prepare("
            SELECT i.*, c.name as company_name, c.email as company_email 
            FROM cp_invoices i
            JOIN cp_companies c ON i.company_id = c.id
            WHERE i.status = 'pending' 
            AND i.due_date = DATE_ADD(?, INTERVAL ? DAY)
            AND (i.last_reminder_date IS NULL OR i.last_reminder_date != ?)
        ");
        $stmt_up->execute([$today, $daysBefore, $today]);
        $upcoming = $stmt_up->fetchAll(PDO::FETCH_ASSOC);

        foreach ($upcoming as $inv) {
            $this->mailReminder($inv, 'upcoming');
        }

        // 2. Overdue Reminders (Overdue today or specific intervals)
        // Remind on: day 1 overdue, day 5 overdue, and day grace-1 
        $stmt_ov = $this->pdo->prepare("
            SELECT i.*, c.name as company_name, c.email as company_email 
            FROM cp_invoices i
            JOIN cp_companies c ON i.company_id = c.id
            WHERE i.status = 'pending' 
            AND (
                i.due_date = DATE_SUB(?, INTERVAL 1 DAY) OR 
                i.due_date = DATE_SUB(?, INTERVAL 5 DAY) OR
                i.due_date = DATE_SUB(?, INTERVAL ? DAY)
            )
            AND (i.last_reminder_date IS NULL OR i.last_reminder_date != ?)
        ");
        $stmt_ov->execute([$today, $today, $today, $graceDays - 1, $today]);
        $overdue = $stmt_ov->fetchAll(PDO::FETCH_ASSOC);

        foreach ($overdue as $inv) {
            $this->mailReminder($inv, 'overdue');
        }
    }

    private function mailReminder($inv, $type) {
        if (empty($inv['company_email'])) return;
        
        $this->debugLog("Reminders: Sending $type reminder for Invoice #{$inv['id']} to {$inv['company_email']}");
        
        require_once __DIR__ . '/../helpers/Mailer.php';
        
        $companyName = $inv['company_name'];
        $dueDate = date('d/m/Y', strtotime($inv['due_date']));
        $amount = number_format((float)$inv['amount'], 2, ',', '.');
        $loginUrl = SITE_URL . "/login"; 
        
        if ($type === 'upcoming') {
            $subject = "Lembrete de Vencimento - Fatura em Aberto 📄";
            $title = "Sua fatura vence em breve";
            $message = "Olá, <strong>$companyName</strong>! Gostaríamos de lembrar que sua fatura no valor de <strong>R$ $amount</strong> vence no dia <strong>$dueDate</strong>.";
        } else {
            $subject = "ALERTA: Fatura Vencida - Evite a suspensão do serviço ⚠️";
            $title = "Fatura em Atraso";
            $message = "Olá, <strong>$companyName</strong>! Identificamos que sua fatura no valor de <strong>R$ $amount</strong> venceu em <strong>$dueDate</strong> e ainda não consta como paga em nosso sistema.";
        }

        $body = "
            <div style='font-family: sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #eee; border-radius: 10px;'>
                <h2 style='color: #4a6cf7;'>$title</h2>
                <p>$message</p>
                <div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0;'>
                    <p style='margin: 5px 0;'><strong>Fatura:</strong> #{$inv['id']}</p>
                    <p style='margin: 5px 0;'><strong>Valor:</strong> R$ $amount</p>
                    <p style='margin: 5px 0;'><strong>Vencimento:</strong> $dueDate</p>
                </div>
                <p>Para garantir a continuidade do serviço, realize o pagamento acessando o link abaixo:</p>
                <p style='text-align: center; margin: 30px 0;'>
                    <a href='$loginUrl' style='background: #4a6cf7; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; font-weight: bold;'>VER FATURA / PAGAR</a>
                </p>
                <p style='font-size: 12px; color: #777; border-top: 1px solid #eee; padding-top: 10px;'>Se você já realizou o pagamento, por favor desconsidere este e-mail. A baixa ocorre automaticamente em até 24h.</p>
            </div>
        ";

        if (\Mailer::send($inv['company_email'], $subject, $body)) {
            $stmt = $this->pdo->prepare("UPDATE cp_invoices SET last_reminder_date = ? WHERE id = ?");
            $stmt->execute([date('Y-m-d'), $inv['id']]);
        }
    }
}
