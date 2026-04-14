<?php
declare(strict_types=1);

namespace App\Modules\Profile\Controllers;

use App\Core\Controller;
use App\Core\Database;
use Auth;

/**
 * User Profile Management Controller (Modular)
 */
class ProfileController extends Controller {

    public function index(): void {
        Auth::requireLogin();
        
        $user_id = $_SESSION['user_id'];
        $user = Database::fetch("SELECT * FROM cp_users WHERE id = ?", [$user_id]);

        if (!$user) {
            die("Usuário não encontrado.");
        }

        $this->render('index', [
            'user' => $user
        ]);
    }

    /**
     * AJAX-First Profile Save
     */
    public function save(): void {
        Auth::requireLogin();
        
        $user_id = $_SESSION['user_id'];
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? null;

        if (!$name || !$email) {
            $this->jsonResponse(['success' => false, 'message' => 'Nome e e-mail são obrigatórios.'], 400);
            return;
        }

        try {
            $pdo = Database::getInstance();
            $currentData = Database::fetch("SELECT name, email FROM cp_users WHERE id = ?", [$user_id]);
            $emailChanged = ($email !== $currentData['email']);
            $emailConfirmationMsg = "";

            if ($emailChanged) {
                // Check availability
                $existing = Database::fetch("SELECT id FROM cp_users WHERE email = ? AND id != ?", [$email, $user_id]);
                if ($existing) {
                    $this->jsonResponse(['success' => false, 'message' => 'O novo e-mail já está sendo utilizado por outra conta.'], 400);
                    return;
                }

                // Security Token
                $token = bin2hex(random_bytes(32));
                $expiresAt = date('Y-m-d H:i:s', strtotime('+4 hours'));

                Database::delete('cp_email_confirmations', "user_id = ?", [$user_id]);
                Database::insert('cp_email_confirmations', [
                    'user_id' => $user_id,
                    'new_email' => $email,
                    'token' => $token,
                    'expires_at' => $expiresAt
                ]);

                // Send Email
                require_once __DIR__ . '/../../../../includes/mailer.php';
                $confirmUrl = SITE_URL . "/confirm-email?token=" . $token;
                $subject = "Confirmação de Alteração de E-mail 📧";
                $body = "
                    <div style='font-family: sans-serif; max-width: 600px; margin: 0 auto; padding: 40px; border: 1px solid #e2e8f0; border-radius: 16px; background: #fff;'>
                        <div style='text-align: center; margin-bottom: 30px;'>
                            <h2 style='color: #d4af37;'>Confirmar E-mail</h2>
                        </div>
                        <p>Olá, <strong>{$name}</strong>.</p>
                        <p>Recebemos uma solicitação para alterar seu e-mail para: <strong>{$email}</strong>.</p>
                        <p>Clique no botão abaixo para confirmar:</p>
                        <div style='text-align: center; margin: 30px 0;'>
                            <a href='{$confirmUrl}' style='display: inline-block; padding: 14px 30px; background: #d4af37; color: #000; text-decoration: none; border-radius: 8px; font-weight: 800; font-size: 14px;'>CONFIRMAR ALTERAÇÃO</a>
                        </div>
                        <p style='font-size: 12px; color: #64748b; text-align: center;'>Este link expira em 4 horas.</p>
                    </div>
                ";

                if (\Mailer::send($email, $subject, $body)) {
                    $emailConfirmationMsg = " Verifique o novo e-mail para confirmar a alteração.";
                } else {
                    $this->jsonResponse(['success' => false, 'message' => 'Falha ao enviar e-mail de confirmação.'], 500);
                    return;
                }

                $email = $currentData['email']; // Keep old email for now
            }

            $data = [
                'name' => $name,
                'email' => $email,
                'phone' => trim($_POST['phone'] ?? ''),
                'zip_code' => trim($_POST['zip_code'] ?? ''),
                'street' => trim($_POST['street'] ?? ''),
                'neighborhood' => trim($_POST['neighborhood'] ?? ''),
                'address_number' => trim($_POST['address_number'] ?? ''),
                'city' => trim($_POST['city'] ?? ''),
                'state' => trim($_POST['state'] ?? '')
            ];

            if (!empty($password)) {
                $data['password'] = password_hash($password, PASSWORD_DEFAULT);
            }

            // Image Handler
            require_once __DIR__ . '/../../../../includes/helpers/ImageHelper.php';
            if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = dirname(dirname(dirname(dirname(__DIR__)))) . '/public/uploads/profile';
                $oldAvatar = Database::fetch("SELECT avatar FROM cp_users WHERE id = ?", [$user_id])['avatar'] ?? null;
                $newFilename = \ImageHelper::uploadAndConvert($_FILES['profile_picture'], $uploadDir, 'avatar_' . $user_id);
                if ($newFilename) {
                    if ($oldAvatar && $oldAvatar !== $newFilename) \ImageHelper::safeDelete($oldAvatar, $uploadDir);
                    $data['avatar'] = $newFilename;
                }
            }

            Database::update('cp_users', $data, 'id = :where_id', ['where_id' => $user_id]);
            $_SESSION['user_name'] = $name;
            
            require_once __DIR__ . '/../../../../includes/logs.php';
            \Logger::log('edit_profile', "Usuário atualizou seus dados cadastrais." . ($emailChanged ? " (Troca de e-mail iniciada)" : ""));

            $this->jsonResponse(['success' => true, 'message' => 'Perfil atualizado com sucesso!' . $emailConfirmationMsg]);
        } catch (\Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => 'Erro: ' . $e->getMessage()], 500);
        }
    }
}
