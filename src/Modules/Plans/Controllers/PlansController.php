<?php
declare(strict_types=1);

namespace App\Modules\Plans\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Pagination;
use Auth;
use Nonce;

/**
 * Plans Management Controller (Modular)
 */
class PlansController extends Controller {
    
    public function index(): void {
        Auth::requireAdmin();
        
        $pdo = Database::getInstance();
        require_once __DIR__ . '/../../../../includes/repositories/PlanRepository.php';
        $planRepo = new \PlanRepository($pdo);
        
        $idFilter = !empty($_GET['id']) ? (int)$_GET['id'] : null;

        global $platform_settings;
        $perPage = (int)($platform_settings['items_per_page'] ?? 25);

        if ($idFilter) {
            $plan = $planRepo->getById($idFilter);
            $plans = $plan ? [$plan] : [];
            $pagination = Pagination::getParams(count($plans), $perPage);
        } else {
            $totalItems = $planRepo->countAll();
            $pagination = Pagination::getParams($totalItems, $perPage);
            $plans = $planRepo->getAll($pagination['limit'], $pagination['offset']);
        }

        $this->render('index', [
            'plans' => $plans,
            'pagination' => $pagination,
            'nonces' => [
                'save' => Nonce::create('save_plan'),
                'delete' => Nonce::create('delete_plan')
            ]
        ]);
    }

    public function save(): void {
        Auth::requireAdmin();
        
        $pdo = Database::getInstance();
        require_once __DIR__ . '/../../../../includes/repositories/PlanRepository.php';
        $planRepo = new \PlanRepository($pdo);

        $data = [
            'id' => !empty($_POST['id']) ? (int)$_POST['id'] : null,
            'name' => $_POST['name'] ?? '',
            'base_price' => (float)($_POST['base_price'] ?? 40),
            'included_users' => (int)($_POST['included_users'] ?? 4),
            'extra_user_price' => (float)($_POST['extra_user_price'] ?? 30),
            'trial_days' => (int)($_POST['trial_days'] ?? 7)
        ];

        if (empty($data['name'])) {
            $this->jsonResponse(['success' => false, 'message' => 'O nome do plano é obrigatório.'], 400);
            return;
        }

        try {
            $planRepo->save($data);
            $this->jsonResponse(['success' => true, 'message' => 'Plano salvo com sucesso.', 'noReload' => true]);
        } catch (\Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => 'Erro ao salvar plano: ' . $e->getMessage()], 500);
        }
    }

    public function delete(): void {
        Auth::requireAdmin();
        
        $id = !empty($_POST['id']) ? (int)$_POST['id'] : null;
        if ($id) {
            $pdo = Database::getInstance();
            require_once __DIR__ . '/../../../../includes/repositories/PlanRepository.php';
            $planRepo = new \PlanRepository($pdo);
            $planRepo->delete($id);
            $this->jsonResponse(['success' => true, 'message' => 'Plano removido com sucesso.', 'noReload' => true]);
        } else {
            $this->jsonResponse(['success' => false, 'message' => 'ID inválido.'], 400);
        }
    }
}
