<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Helpers\Logger;
use Auth;
use PDO;
use UserRepository;

class UsersController extends Controller {
    public function index(): void {
        require_once __DIR__ . '/../../../includes/repositories/UserRepository.php';
        $userRepo = new UserRepository(\App\Core\Database::getInstance());
        $all_users = $userRepo->getAll();

        $this->render('admin/users', [
            'all_users' => $all_users,
            'nonces' => [
                'save' => \Nonce::create('save_user'),
                'delete' => \Nonce::create('delete_user')
            ]
        ]);
    }

    public function save(): void {
        if (!\Nonce::verify($_POST['nonce'] ?? '', 'save_user')) {
            $this->jsonResponse(['success' => false, 'message' => 'Erro de segurança (Nonce).'], 403);
            return;
        }

        require_once __DIR__ . '/../../../includes/repositories/UserRepository.php';
        $userRepo = new UserRepository(\App\Core\Database::getInstance());

        $id = !empty($_POST['id']) ? (int)$_POST['id'] : null;
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? null;
        $role = $_POST['role'] ?? 'usuario';

        if (!$name || !$email || (!$id && !$username)) {
            $this->jsonResponse(['success' => false, 'message' => 'Nome, e-mail e username são obrigatórios.'], 400);
            return;
        }

        try {
            // Check if email already exists
            $existingUser = $userRepo->getByEmail($email);
            if ($existingUser && (int)$existingUser['id'] !== $id) {
                $this->jsonResponse(['success' => false, 'message' => 'Este e-mail já está sendo utilizado por outro usuário.'], 400);
                return;
            }

            // Check if username already exists
            if (!$id && $username) {
                $existingByUsername = $userRepo->getByUsername($username);
                if ($existingByUsername) {
                    $this->jsonResponse(['success' => false, 'message' => 'Este nome de usuário já está sendo utilizado.'], 400);
                    return;
                }
            }

            $userData = [
                'id' => $id,
                'name' => $name,
                'email' => $email,
                'username' => $username,
                'password' => $password,
                'role' => $role,
                'phone' => trim($_POST['phone'] ?? ''),
                'zip_code' => trim($_POST['zip_code'] ?? ''),
                'street' => trim($_POST['street'] ?? ''),
                'neighborhood' => trim($_POST['neighborhood'] ?? ''),
                'address_number' => trim($_POST['address_number'] ?? ''),
                'city' => trim($_POST['city'] ?? ''),
                'state' => trim($_POST['state'] ?? ''),
                'created_by' => Auth::id()
            ];

            $userRepo->save($userData);
            
            require_once __DIR__ . '/../../../includes/logs.php';
            Logger::log($id ? 'edit_user' : 'create_user', $id ? "Editou o usuário $name" : "Criou o usuário $name");
            
            $this->jsonResponse(['success' => true, 'message' => 'Usuário salvo com sucesso!', 'redirect' => 'users']);
        } catch (\Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => 'Erro: ' . $e->getMessage()], 500);
        }
    }

    public function delete(): void {
        if (!\Nonce::verify($_POST['nonce'] ?? '', 'delete_user')) {
            $this->jsonResponse(['success' => false, 'message' => 'Erro de segurança (Nonce).'], 403);
            return;
        }

        $id = !empty($_POST['id']) ? (int)$_POST['id'] : null;
        if (!$id) {
            $this->jsonResponse(['success' => false, 'message' => 'ID inválido.'], 400);
            return;
        }

        if ($id === Auth::id()) {
            $this->jsonResponse(['success' => false, 'message' => 'Você não pode excluir sua própria conta.'], 400);
            return;
        }

        try {
            require_once __DIR__ . '/../../../includes/repositories/UserRepository.php';
            $userRepo = new UserRepository(\App\Core\Database::getInstance());
            $user = $userRepo->getById($id);

            if (!$user) {
                $this->jsonResponse(['success' => false, 'message' => 'Usuário não encontrado.'], 404);
                return;
            }

            // Delete associated image if exists
            if (!empty($user['avatar'])) {
                require_once __DIR__ . '/../../../includes/image_helper.php';
                $uploadDir = __DIR__ . '/../../../uploads/profile/';
                \ImageHelper::safeDelete($user['avatar'], $uploadDir);
            }

            $userRepo->delete($id);
            
            require_once __DIR__ . '/../../../includes/logs.php';
            Logger::log('delete_user', "Usuário " . (string)$id . " removido.");
            
            $this->jsonResponse(['success' => true, 'message' => 'Usuário removido.']);
        } catch (\Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => 'Erro: ' . $e->getMessage()], 500);
        }
    }
}
