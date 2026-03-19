<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use Auth;

class IntegrationsController extends Controller {
    public function index() {
        Auth::requireAdmin();
        
        global $pdo;
        $active_tab = $_GET['tab'] ?? 'email';
        $msg = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['save_email'])) {
                $keys = ['smtp_host', 'smtp_port', 'smtp_user', 'smtp_pass', 'smtp_from_email', 'smtp_from_name'];
                foreach ($keys as $key) {
                    $val = trim($_POST[$key] ?? '');
                    $stmt = $pdo->prepare("INSERT INTO cp_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
                    $stmt->execute([$key, $val, $val]);
                }
                $msg = 'Configurações de e-mail salvas com sucesso!';
            }
        }

        // Fetch Current Settings
        $stmt = $pdo->prepare("SELECT setting_key, setting_value FROM cp_settings");
        $stmt->execute();
        $settings = $stmt->fetchAll(\PDO::FETCH_KEY_PAIR);

        return $this->render('admin/integrations', [
            'settings' => $settings,
            'active_tab' => $active_tab,
            'msg' => $msg
        ]);
    }

    public function testEmail() {
        $email = $_POST['email'] ?? null;
        if (!$email) {
            return $this->jsonResponse(['success' => false, 'message' => 'E-mail é obrigatório para o teste.'], 400);
        }

        try {
            require_once __DIR__ . '/../../includes/mailer.php';
            $subject = "SaaSFlow Core - Teste de Configuração SMTP 🚀";
            $body = "
                <div style='font-family: sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #eee; border-radius: 10px;'>
                    <h2 style='color: #d4af37;'>Teste de Conexão</h2>
                    <p>Se você está recebendo este e-mail, significa que suas configurações de SMTP no <strong>SaaSFlow Core</strong> estão funcionando perfeitamente!</p>
                    <p style='font-size: 12px; color: #777;'>Enviado em: " . date('d/m/Y H:i:s') . "</p>
                </div>
            ";

            $sent = \Mailer::send($email, $subject, $body);

            if ($sent) {
                return $this->jsonResponse(['success' => true, 'message' => 'E-mail de teste enviado com sucesso! Verifique sua caixa de entrada.']);
            } else {
                return $this->jsonResponse(['success' => false, 'message' => 'Falha ao enviar e-mail de teste. Verifique suas credenciais SMTP.'], 500);
            }
        } catch (\Exception $e) {
            return $this->jsonResponse(['success' => false, 'message' => 'Erro: ' . $e->getMessage()], 500);
        }
    }
}
