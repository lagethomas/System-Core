<?php
declare(strict_types=1);

namespace App\Modules\Companies\Controllers;

use App\Core\Controller;
use App\Core\Database;
use Auth;
use Nonce;

/**
 * Controller for Company-specific settings (Owner/App)
 */
class SettingsController extends Controller {
    
    public function index(): void {
        Auth::requireRole('proprietario'); 
        
        $company_id = Auth::companyId();
        if (!$company_id) {
            header("Location: " . SITE_URL . "/dashboard");
            exit;
        }

        $active_tab = $_GET['tab'] ?? 'general';
        $company = Database::fetch("SELECT * FROM cp_companies WHERE id = :id", ['id' => $company_id]);
        
        require_once __DIR__ . '/../../../../includes/helpers/ThemeHelper.php';
        $themes = \ThemeHelper::getAvailableThemes();

        $this->render('settings', [
            'company' => $company,
            'active_tab' => $active_tab,
            'themes' => $themes,
            'nonce' => Nonce::create('save_company_settings')
        ]);
    }

    public function save(): void {
        Auth::requireRole('proprietario');
        $company_id = Auth::companyId();
        if (!$company_id) {
            $this->jsonResponse(['success' => false, 'message' => 'Empresa não identificada.'], 403);
            return;
        }
        
        $company = Database::fetch("SELECT * FROM cp_companies WHERE id = :id", ['id' => $company_id]);
        
        $data = [
            'name'          => isset($_POST['name']) ? trim($_POST['name']) : ($company['name'] ?? ''),
            'email'         => isset($_POST['email']) ? trim($_POST['email']) : ($company['email'] ?? ''),
            'phone'         => isset($_POST['phone']) ? trim($_POST['phone']) : ($company['phone'] ?? ''),
            'document'      => isset($_POST['document']) ? trim($_POST['document']) : ($company['document'] ?? ''),
            'custom_domain' => isset($_POST['custom_domain']) ? trim($_POST['custom_domain']) : ($company['custom_domain'] ?? ''),
            'theme'         => $_POST['theme'] ?? ($company['theme'] ?? 'gold-black'),
            'login_theme'   => $_POST['login_theme'] ?? ($company['login_theme'] ?? 'gold-black'),
        ];

        require_once __DIR__ . '/../../../../includes/helpers/ThemeHelper.php';
        $themes = \ThemeHelper::getAvailableThemes();
        if (isset($themes[$data['theme']])) {
            $data['theme_color'] = $themes[$data['theme']]['color'];
        }

        if (empty($data['name'])) {
            $this->jsonResponse(['success' => false, 'message' => 'O nome da empresa é obrigatório.'], 400);
            return;
        }

        // Handle Image Upload for Logo
        if (!empty($_FILES['logo']['name'])) {
            require_once __DIR__ . '/../../../../includes/helpers/ImageHelper.php';
            $uploadDir = dirname(dirname(dirname(dirname(__DIR__)))) . '/public/uploads/companies';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            
            $newLogo = \ImageHelper::uploadAndConvert($_FILES['logo'], $uploadDir, 'logo_' . (string)$company_id);
            if ($newLogo) {
                if (!empty($company['logo'])) {
                    \ImageHelper::safeDelete(basename($company['logo']), $uploadDir);
                }
                $data['logo'] = '/uploads/companies/' . $newLogo;
            }
        }

        // Handle Image Upload for Background
        if (!empty($_FILES['background_image']['name'])) {
            require_once __DIR__ . '/../../../../includes/helpers/ImageHelper.php';
            $uploadDir = dirname(dirname(dirname(dirname(__DIR__)))) . '/public/uploads/companies';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            
            $newBg = \ImageHelper::uploadAndConvert($_FILES['background_image'], $uploadDir, 'bg_' . (string)$company_id);
            if ($newBg) {
                if (!empty($company['background_image'])) {
                    \ImageHelper::safeDelete(basename($company['background_image']), $uploadDir);
                }
                $data['background_image'] = '/uploads/companies/' . $newBg;
            }
        }

        Database::update('cp_companies', $data, 'id = :id', ['id' => $company_id]);
        

        $this->jsonResponse(['success' => true, 'message' => 'Configurações salvas com sucesso!']);
    }
}
