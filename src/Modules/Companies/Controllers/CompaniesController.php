<?php
declare(strict_types=1);

namespace App\Modules\Companies\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Pagination;
use Auth;
use Nonce;
use PDO;

/**
 * Controller for SaaS Companies Management (Admin)
 */
class CompaniesController extends Controller {
    
    public function index(): void {
        Auth::requireAdmin();
        
        $pdo = Database::getInstance();
        
        // Repositories path adjustment for modular structure
        require_once __DIR__ . '/../../../../includes/repositories/CompanyRepository.php';
        require_once __DIR__ . '/../../../../includes/repositories/PlanRepository.php';
        
        $companyRepo = new \CompanyRepository($pdo);
        $planRepo = new \PlanRepository($pdo);
        
        // Maintenance tasks
        $companyRepo->checkAutoBilling();
        $companyRepo->sendInvoiceReminders();

        $idFilter = !empty($_GET['id']) ? (int)$_GET['id'] : null;

        $countSql = "SELECT COUNT(*) as total FROM cp_companies";
        if ($idFilter) $countSql .= " WHERE id = $idFilter";

        global $platform_settings;
        $perPage = (int)($platform_settings['items_per_page'] ?? 25);
        $totalItems = (int)Database::fetch($countSql)['total'];
        $pagination = Pagination::getParams($totalItems, $perPage);

        $sql = "
            SELECT c.*, 
            (SELECT name FROM cp_users WHERE company_id = c.id AND role = 'proprietario' ORDER BY id ASC LIMIT 1) as master_manager,
            (SELECT id FROM cp_users WHERE company_id = c.id AND role = 'proprietario' ORDER BY id ASC LIMIT 1) as master_manager_id,
            p.name as plan_name,
            u_part.name as partner_name
            FROM cp_companies c 
            LEFT JOIN cp_plans p ON c.plan_id = p.id
            LEFT JOIN cp_users u_part ON c.partner_id = u_part.id";

        if ($idFilter) {
            $sql .= " WHERE c.id = :idFilter";
        }

        $sql .= " ORDER BY c.name ASC LIMIT :limit OFFSET :offset";

        $stmt = $pdo->prepare($sql);
        if ($idFilter) $stmt->bindValue(':idFilter', $idFilter, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $pagination['limit'], PDO::PARAM_INT);
        $stmt->bindValue(':offset', $pagination['offset'], PDO::PARAM_INT);
        $stmt->execute();
        $companies = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $plans = $planRepo->getAll();

        $stmtPartner = $pdo->prepare("SELECT id, name FROM cp_users WHERE role = 'parceiro' ORDER BY name ASC");
        $stmtPartner->execute();
        $partners = $stmtPartner->fetchAll(PDO::FETCH_ASSOC);

        $stmtOwners = $pdo->prepare("SELECT id, name, company_id FROM cp_users WHERE role = 'proprietario' ORDER BY name ASC");
        $stmtOwners->execute();
        $owners = $stmtOwners->fetchAll(PDO::FETCH_ASSOC);

        $this->render('index', [
            'companies'  => $companies,
            'plans'      => $plans,
            'partners'   => $partners,
            'owners'     => $owners,
            'pagination' => $pagination,
            'nonces'     => [
                'save'   => Nonce::create('save_company'),
                'delete' => Nonce::create('delete_company')
            ]
        ]);
    }

    public function save(): void {
        Auth::requireAdmin();
        
        $pdo = Database::getInstance();
        require_once __DIR__ . '/../../../../includes/repositories/CompanyRepository.php';
        $companyRepo = new \CompanyRepository($pdo);

        $id = !empty($_POST['id']) ? (int)$_POST['id'] : null;
        
        $data = [
            'name'          => $_POST['name'] ?? '',
            'slug'          => $_POST['slug'] ?? '',
            'custom_domain' => trim($_POST['custom_domain'] ?? ''),
            'phone'         => $_POST['phone'] ?? '',
            'email'         => $_POST['email'] ?? '',
            'document'      => $_POST['document'] ?? '',
            'plan_id'       => !empty($_POST['plan_id']) ? (int)$_POST['plan_id'] : null,
            'partner_id'    => !empty($_POST['partner_id']) ? (int)$_POST['partner_id'] : null,
            'theme'         => $_POST['theme'] ?? 'default',
            'expires_at'    => !empty($_POST['expires_at']) ? $_POST['expires_at'] : null,
            'active'        => isset($_POST['active']) ? (int)$_POST['active'] : 1
        ];

        $owner_id = !empty($_POST['owner_id']) ? (int)$_POST['owner_id'] : null;

        if (empty($data['name']) || empty($data['slug'])) {
            $this->jsonResponse(['success' => false, 'message' => 'Nome e Slug são obrigatórios.'], 400);
            return;
        }

        if ($id) {
            $data['id'] = $id;
        }

        $companyId = $companyRepo->save($data);

        // Associate owner with the company
        if ($companyId && $owner_id) {
            $stmt = $pdo->prepare("UPDATE cp_users SET company_id = ? WHERE id = ?");
            $stmt->execute([$companyId, $owner_id]);
        }

        $this->jsonResponse(['success' => true, 'message' => 'Empresa salva com sucesso!']);
    }

    public function delete(): void {
        Auth::requireAdmin();
        
        $pdo = Database::getInstance();
        require_once __DIR__ . '/../../../../includes/repositories/CompanyRepository.php';
        $companyRepo = new \CompanyRepository($pdo);

        $id = !empty($_POST['id']) ? (int)$_POST['id'] : null;
        if ($id) {
            $companyRepo->deleteCompanyData($id);
            $this->jsonResponse(['success' => true, 'message' => 'Empresa removida.']);
        } else {
            $this->jsonResponse(['success' => false, 'message' => 'ID inválido.'], 400);
        }
    }
}
