<?php
declare(strict_types=1);

namespace App\Modules\Integrations\Controllers;

use App\Core\Controller;
use App\Core\Database;
use Auth;
use Mailer;

/**
 * Integrations Management Controller (Modular)
 */
class IntegrationsController extends Controller {
    
    public function index(): void {
        Auth::requireAdmin();
        
        $pdo = Database::getInstance();
        $active_tab = $_GET['tab'] ?? 'email';

        // Fetch Current Settings
        $stmt = $pdo->prepare("SELECT setting_key, setting_value FROM cp_settings");
        $stmt->execute();
        $settings = $stmt->fetchAll(\PDO::FETCH_KEY_PAIR);

        $this->render('index', [
            'settings' => $settings,
            'active_tab' => $active_tab
        ]);
    }

    /**
     * Save integration settings via AJAX
     */
    public function save(): void {
        Auth::requireAdmin();
        
        $pdo = Database::getInstance();
        $type = $_POST['type'] ?? 'email';

        if ($type === 'email') {
            $keys = ['smtp_host', 'smtp_port', 'smtp_user', 'smtp_pass', 'smtp_from_email', 'smtp_from_name', 'smtp_secure'];
            foreach ($keys as $key) {
                if (isset($_POST[$key])) {
                    $val = trim((string)$_POST[$key]);
                    $stmt = $pdo->prepare("INSERT INTO cp_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
                    $stmt->execute([$key, $val, $val]);
                }
            }
            
            require_once __DIR__ . '/../../../../includes/logs.php';
            \Logger::log('settings_update', 'Atualizou as configurações de integração de e-mail (SMTP).');
            
            $this->jsonResponse(['success' => true, 'message' => 'Configurações de e-mail salvas com sucesso!', 'noReload' => true]);
        } else {
            $this->jsonResponse(['success' => false, 'message' => 'Tipo de integração inválido.'], 400);
        }
    }

    /**
     * Test SMTP connection via AJAX
     */
    public function testEmail(): void {
        Auth::requireAdmin();
        
        $email = $_POST['email'] ?? null;
        if (!$email) {
            $this->jsonResponse(['success' => false, 'message' => 'E-mail é obrigatório para o teste.'], 400);
            return;
        }

        try {
            require_once __DIR__ . '/../../../../includes/mailer.php';
            $subject = "SaaSFlow Core - Teste de Configuração SMTP 🚀";
            $body = "
                <div style='font-family: sans-serif; max-width: 600px; margin: 0 auto; color: #334155;'>
                    <div style='background: #0f172a; padding: 40px; text-align: center; border-radius: 12px 12px 0 0;'>
                        <h2 style='color: #f59e0b; margin: 0;'>Teste de Conexão</h2>
                    </div>
                    <div style='background: #ffffff; padding: 40px; border: 1px solid #e2e8f0; border-radius: 0 0 12px 12px;'>
                        <p>Se você está recebendo este e-mail, significa que suas configurações de SMTP no <strong>SaaSFlow Core</strong> estão funcionando perfeitamente!</p>
                        <p style='font-size: 12px; color: #777; margin-top: 30px;'>Enviado em: " . date('d/m/Y H:i:s') . "</p>
                    </div>
                </div>
            ";

            $sent = (bool)\Mailer::send($email, $subject, $body);

            if ($sent) {
                require_once __DIR__ . '/../../../../includes/logs.php';
                \Logger::log('security_email', 'Teste de configuração SMTP realizado com sucesso para: ' . $email);
                
                $this->jsonResponse(['success' => true, 'message' => 'E-mail de teste enviado com sucesso! Verifique sua caixa de entrada.', 'noReload' => true]);
            } else {
                $this->jsonResponse(['success' => false, 'message' => 'Falha ao enviar e-mail de teste. Verifique suas credenciais SMTP.'], 500);
            }
        } catch (\Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => 'Erro: ' . $e->getMessage()], 500);
        }
    }
}
