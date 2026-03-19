<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use Auth;
use UserRepository;

class UsersController extends Controller {
    public function index() {
        require_once __DIR__ . '/../../../includes/repositories/UserRepository.php';
        $userRepo = new \UserRepository(\App\Core\Database::getInstance());
        $all_users = $userRepo->getAll();

        return $this->render('admin/users', [
            'all_users' => $all_users
        ]);
    }

    public function save() {
        require_once __DIR__ . '/../../../includes/repositories/UserRepository.php';
        $userRepo = new \UserRepository(\App\Core\Database::getInstance());

        $id = !empty($_POST['id']) ? (int)$_POST['id'] : null;
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? null;
        $role = $_POST['role'] ?? 'usuario';

        if (!$name || !$email || (!$id && !$username)) {
            return $this->jsonResponse(['success' => false, 'message' => 'Nome, e-mail e username são obrigatórios.'], 400);
        }

        try {
            // Check if email already exists
            $existingUser = $userRepo->getByEmail($email);
            if ($existingUser && $existingUser['id'] != $id) {
                return $this->jsonResponse(['success' => false, 'message' => 'Este e-mail já está sendo utilizado por outro usuário.'], 400);
            }

            // Check if username already exists
            if (!$id && $username) {
                $existingByUsername = $userRepo->getByUsername($username);
                if ($existingByUsername) {
                    return $this->jsonResponse(['success' => false, 'message' => 'Este nome de usuário já está sendo utilizado.'], 400);
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
                'created_by' => $_SESSION['user_id']
            ];

            $userRepo->save($userData);
            
            require_once __DIR__ . '/../../../includes/logs.php';
            \Logger::log($id ? 'edit_user' : 'create_user', $id ? "Editou o usuário $name" : "Criou o usuário $name");
            
            return $this->jsonResponse(['success' => true, 'message' => 'Usuário salvo com sucesso!', 'redirect' => 'users']);
        } catch (\Exception $e) {
            return $this->jsonResponse(['success' => false, 'message' => 'Erro: ' . $e->getMessage()], 500);
        }
    }

    public function delete() {
        $id = !empty($_POST['id']) ? (int)$_POST['id'] : null;
        if (!$id) return $this->jsonResponse(['success' => false, 'message' => 'ID inválido.'], 400);

        if ($id == $_SESSION['user_id']) {
            return $this->jsonResponse(['success' => false, 'message' => 'Você não pode excluir sua própria conta.'], 400);
        }

        try {
            require_once __DIR__ . '/../../../includes/repositories/UserRepository.php';
            $userRepo = new \UserRepository(\App\Core\Database::getInstance());
            $user = $userRepo->getById($id);

            if (!$user) {
                return $this->jsonResponse(['success' => false, 'message' => 'Usuário não encontrado.'], 404);
            }

            // Delete associated image if exists
            if (!empty($user['avatar'])) {
                require_once __DIR__ . '/../../../includes/image_helper.php';
                $uploadDir = __DIR__ . '/../../../uploads/profile/';
                \ImageHelper::safeDelete($user['avatar'], $uploadDir);
            }

            $userRepo->delete($id);
            
            require_once __DIR__ . '/../../../includes/logs.php';
            \Logger::log('delete_user', "Usuário $id removido.");
            
            return $this->jsonResponse(['success' => true, 'message' => 'Usuário removido.']);
        } catch (\Exception $e) {
            return $this->jsonResponse(['success' => false, 'message' => 'Erro: ' . $e->getMessage()], 500);
        }
    }
}
