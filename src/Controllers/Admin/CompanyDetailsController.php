<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;
use Auth;
use CompanyRepository;
use InvoiceRepository;
use UserRepository;
use PDO;

class CompanyDetailsController extends Controller {
    
    public function index(): void {
        Auth::requireAdmin();
        $id = $_GET['id'] ?? null;
        
        if (!$id) {
            header('Location: ' . SITE_URL . '/admin/companies');
            exit;
        }

        require_once __DIR__ . '/../../../includes/repositories/CompanyRepository.php';
        require_once __DIR__ . '/../../../includes/repositories/InvoiceRepository.php';
        require_once __DIR__ . '/../../../includes/repositories/UserRepository.php';

        $pdo = Database::getInstance();
        $companyRepo = new CompanyRepository($pdo);
        $invoiceRepo = new InvoiceRepository($pdo);
        $userRepo = new UserRepository($pdo);

        $company = $companyRepo->getById($id);
        
        if (!$company) {
            header('Location: ' . SITE_URL . '/admin/companies');
            exit;
        }

        $invoices = $invoiceRepo->getAllByCompany($id);
        
        // Fetch users for the company
        $stmtUsers = $pdo->prepare("SELECT * FROM cp_users WHERE company_id = ? ORDER BY name ASC");
        $stmtUsers->execute([$id]);
        $users = $stmtUsers->fetchAll(PDO::FETCH_ASSOC);

        $this->render('admin/company_details', [
            'company' => $company,
            'invoices' => $invoices,
            'users' => $users,
            'title' => 'Detalhes da Empresa - ' . $company['name']
        ]);
    }

    public function generateInvoice(): void {
        Auth::requireAdmin();
        $pdo = Database::getInstance();
        $id = $_POST['company_id'] ?? null;
        
        if (!$id) {
            $this->jsonResponse(['success' => false, 'message' => 'ID da empresa não informado.'], 400);
            return;
        }

        $amount = (float)($_POST['amount'] ?? 0);
        $due_date = $_POST['due_date'] ?? date('Y-m-d');
        $type = $_POST['type'] ?? 'single';
        $description = $_POST['description'] ?? 'Fatura Manual';

        if ($amount <= 0) {
            $this->jsonResponse(['success' => false, 'message' => 'Valor deve ser maior que zero.'], 400);
            return;
        }

        require_once __DIR__ . '/../../../includes/repositories/InvoiceRepository.php';
        $invoiceRepo = new InvoiceRepository($pdo);

        $res = $invoiceRepo->create([
            'company_id' => $id,
            'amount' => $amount,
            'due_date' => $due_date,
            'status' => 'pending',
            'type' => $type,
            'description' => $description
        ]);

        if ($res) {
            // Self-healing: if company expiration is undefined, set it to this invoice's due date
            $stmt_check = $pdo->prepare("SELECT expires_at FROM cp_companies WHERE id = ?");
            $stmt_check->execute([$id]);
            $current_expiration = $stmt_check->fetchColumn();

            if (empty($current_expiration) && $type === 'recurring') {
                $pdo->prepare("UPDATE cp_companies SET expires_at = ? WHERE id = ?")->execute([$due_date, $id]);
            }

            $this->jsonResponse(['success' => true, 'message' => 'Fatura gerada com sucesso!']);
        } else {
            $this->jsonResponse(['success' => false, 'message' => 'Erro ao gerar fatura.'], 500);
        }
    }

    public function deleteInvoice(): void {
        Auth::requireAdmin();
        $id = $_POST['id'] ?? null;
        if (!$id) {
            $this->jsonResponse(['success' => false, 'message' => 'ID não informado.'], 400);
            return;
        }

        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("DELETE FROM cp_invoices WHERE id = ? AND status != 'paid'");
        $res = $stmt->execute([$id]);

        if ($res) {
            $this->jsonResponse(['success' => true, 'message' => 'Fatura removida.']);
        } else {
            $this->jsonResponse(['success' => false, 'message' => 'Erro ao remover fatura ou fatura já está paga.'], 500);
        }
    }

    public function payInvoice(): void {
        Auth::requireAdmin();
        $id = $_POST['id'] ?? null;
        if (!$id) {
            $this->jsonResponse(['success' => false, 'message' => 'ID não informado.'], 400);
            return;
        }

        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("UPDATE cp_invoices SET status = 'paid', paid_at = NOW() WHERE id = ?");
        $res = $stmt->execute([$id]);

        if ($res) {
            // Synchronize expiration if it's a recurring invoice
            $stmt_check = $pdo->prepare("SELECT company_id, type FROM cp_invoices WHERE id = ?");
            $stmt_check->execute([$id]);
            $inv = $stmt_check->fetch(PDO::FETCH_ASSOC);
            
            if ($inv && $inv['type'] === 'recurring') {
                require_once __DIR__ . '/../../../includes/repositories/CompanyRepository.php';
                $companyRepo = new CompanyRepository($pdo);
                $companyRepo->synchronizeExpiration($inv['company_id']);
            }

            $this->jsonResponse(['success' => true, 'message' => 'Fatura marcada como paga!']);
        } else {
            $this->jsonResponse(['success' => false, 'message' => 'Erro ao atualizar fatura.'], 500);
        }
    }

    public function updateDate(): void {
        Auth::requireAdmin();
        $id = $_POST['invoice_id'] ?? null;
        $date = $_POST['due_date'] ?? null;
        
        if (!$id || !$date) {
            $this->jsonResponse(['success' => false, 'message' => 'Dados incompletos.'], 400);
            return;
        }

        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("UPDATE cp_invoices SET due_date = ? WHERE id = ? AND status = 'pending'");
        $res = $stmt->execute([$date, $id]);

        if ($res) {
            $this->jsonResponse(['success' => true, 'message' => 'Data atualizada com sucesso!']);
        } else {
            $this->jsonResponse(['success' => false, 'message' => 'Erro ao atualizar data.'], 500);
        }
    }

    public function updateExpiration(): void {
        Auth::requireAdmin();
        $id = $_POST['company_id'] ?? null;
        $date = $_POST['expires_at'] ?? null;
        
        if (!$id || !$date) {
            $this->jsonResponse(['success' => false, 'message' => 'Dados incompletos.'], 400);
            return;
        }

        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("UPDATE cp_companies SET expires_at = ? WHERE id = ?");
        $res = $stmt->execute([$date, $id]);

        if ($res) {
            $this->jsonResponse(['success' => true, 'message' => 'Vencimento atualizado com sucesso!']);
        } else {
            $this->jsonResponse(['success' => false, 'message' => 'Erro ao atualizar vencimento.'], 500);
        }
    }
}
