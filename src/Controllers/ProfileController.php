<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use Auth;
use PDO;
use App\Helpers\Logger;

class ProfileController extends Controller {
    public function index(): void {
        $user_id = $_SESSION['user_id'];
        $user = \App\Core\Database::fetch("SELECT * FROM cp_users WHERE id = ?", [$user_id]);

        if (!$user) {
            die("Usuário não encontrado.");
        }

        $this->render('app/profile', [
            'user' => $user,
            'nonce' => \Nonce::create('save_profile')
        ]);
    }

    public function save(): void {
        if (!\Nonce::verify($_POST['nonce'] ?? '', 'save_profile')) {
            $this->jsonResponse(['success' => false, 'message' => 'Erro de segurança (Nonce).'], 403);
            return;
        }

        $user_id = $_SESSION['user_id'];
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? null;

        if (!$name || !$email) {
            $this->jsonResponse(['success' => false, 'message' => 'Nome e e-mail são obrigatórios.'], 400);
            return;
        }

        try {
            // Check if email belongs to another user
            $existing = \App\Core\Database::fetch("SELECT id FROM cp_users WHERE email = ? AND id != ?", [$email, $user_id]);
            if ($existing) {
                $this->jsonResponse(['success' => false, 'message' => 'Este e-mail já está sendo utilizado por outra conta.'], 400);
                return;
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

            // Image Handler (Legacy wrapper for now or move to Service)
            require_once __DIR__ . '/../../includes/image_helper.php';
            if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = __DIR__ . '/../../uploads/profile/';
                $oldAvatar = \App\Core\Database::fetch("SELECT avatar FROM cp_users WHERE id = ?", [$user_id])['avatar'] ?? null;
                $newFilename = \ImageHelper::uploadAndConvert($_FILES['profile_picture'], $uploadDir, 'avatar_' . $user_id);
                if ($newFilename) {
                    if ($oldAvatar && $oldAvatar !== $newFilename) {
                        \ImageHelper::safeDelete($oldAvatar, $uploadDir);
                    }
                    $data['avatar'] = $newFilename;
                }
            }

            \App\Core\Database::update('cp_users', $data, 'id = :where_id', ['where_id' => $user_id]);
            $_SESSION['user_name'] = $name;
            
            require_once __DIR__ . '/../../includes/logs.php';
            Logger::log('edit_profile', "Usuário atualizou seus próprios dados via Controller.");

            $this->jsonResponse(['success' => true, 'message' => 'Perfil atualizado com sucesso!']);
        } catch (\Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => 'Erro: ' . $e->getMessage()], 500);
        }
    }
}
