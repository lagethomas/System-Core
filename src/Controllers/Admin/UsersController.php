<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use Auth;
use UserRepository;

class UsersController extends Controller {
    public function index(): void {
        require_once __DIR__ . '/../../../includes/repositories/UserRepository.php';
        $userRepo = new UserRepository(\App\Core\Database::getInstance());
        
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = 10;
        $search = $_GET['search'] ?? '';
        $idFilter = isset($_GET['id']) ? (int)$_GET['id'] : null;

        $totalUsers = $userRepo->countAll($search, $idFilter);
        $all_users = $userRepo->getPaginated($page, $perPage, $search, $idFilter);
        $totalPages = (int)ceil($totalUsers / $perPage);

        $this->render('admin/users', [
            'all_users' => $all_users,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalUsers' => $totalUsers,
            'searchTerm' => $search
        ]);
    }

    public function save(): void {
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
            if ($existingUser && $existingUser['id'] != $id) {
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
                'created_by' => $_SESSION['user_id']
            ];

            $userRepo->save($userData);
            
            require_once __DIR__ . '/../../../includes/logs.php';
            \Logger::log($id ? 'edit_user' : 'create_user', $id ? "Editou o usuário $name" : "Criou o usuário $name");
            
            // NOTIFICATION (Rule 39)
            try {
                require_once __DIR__ . '/../../../includes/repositories/NotificationRepository.php';
                $notifRepo = new \NotificationRepository(\DB::getInstance());
                $notifRepo->create([
                    'user_id' => 1,
                    'title'   => '👤 Usuário ' . ($id ? 'Atualizado' : 'Criado'),
                    'message' => "O usuário $name foi salvo com sucesso.",
                    'link'    => SITE_URL . '/admin/users',
                    'type'    => 'info'
                ]);
            } catch (\Exception $e) {}

            $this->jsonResponse(['success' => true, 'message' => 'Usuário salvo com sucesso!', 'redirect' => 'users']);
        } catch (\Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => 'Erro: ' . $e->getMessage()], 500);
        }
    }

    public function delete(): void {
        $id = !empty($_POST['id']) ? (int)$_POST['id'] : null;
        if (!$id) {
            $this->jsonResponse(['success' => false, 'message' => 'ID inválido.'], 400);
            return;
        }

        if ($id == $_SESSION['user_id']) {
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
            \Logger::log('delete_user', "Usuário " . (string)$id . " removido.");

            // NOTIFICATION (Rule 39)
            try {
                require_once __DIR__ . '/../../../includes/repositories/NotificationRepository.php';
                $notifRepo = new \NotificationRepository(\DB::getInstance());
                $notifRepo->create([
                    'user_id' => 1,
                    'title'   => '👤 Usuário Removido',
                    'message' => "Um usuário (ID " . (string)$id . ") foi excluído do sistema.",
                    'link'    => SITE_URL . '/admin/users',
                    'type'    => 'danger'
                ]);
            } catch (\Exception $e) {}
            
            $this->jsonResponse(['success' => true, 'message' => 'Usuário removido.']);
        } catch (\Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => 'Erro: ' . $e->getMessage()], 500);
        }
    }

    public function sendAccess(): void {
        Auth::requireAdmin();
        $id = !empty($_POST['id']) ? (int)$_POST['id'] : null;
        if (!$id) {
            $this->jsonResponse(['success' => false, 'message' => 'ID inválido.'], 400);
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

            // Generate temporary password (8 random chars)
            $tempPassword = bin2hex(random_bytes(4));
            
            // Update user password (Hashed according to security standards)
            \App\Core\Database::update('cp_users', [
                'password' => password_hash($tempPassword, PASSWORD_ARGON2ID)
            ], 'id = :where_id', ['where_id' => $id]);

            // Load site name for the email
            global $platform_settings;
            $siteName = $platform_settings['system_name'] ?? 'SaaSFlow Core';

            // Send Email
            require_once __DIR__ . '/../../../includes/mailer.php';
            $subject = "Acesso ao Painel " . $siteName . " 🚀";
            $body = "
                <div style='font-family: sans-serif; max-width: 600px; margin: 0 auto; color: #334155;'>
                    <div style='background: #0f172a; padding: 40px; text-align: center; border-radius: 12px 12px 0 0;'>
                        <h2 style='color: #f59e0b; margin: 0;'>Dados de Acesso</h2>
                    </div>
                    <div style='background: #ffffff; padding: 40px; border: 1px solid #e2e8f0; border-radius: 0 0 12px 12px;'>
                        <p>Olá, <strong>{$user['name']}</strong>!</p>
                        <p>Seus dados de acesso ao painel do <strong>{$siteName}</strong> foram gerados pelo administrador.</p>
                        <hr style='border: 0; border-top: 1px solid #e2e8f0; margin: 30px 0;'>
                        <div style='background: #f1f5f9; padding: 25px; border-radius: 12px;'>
                            <p style='margin: 0 0 15px 0;'><strong>🔗 URL de Acesso:</strong> <a href='".SITE_URL."' style='color: #2563eb;'>".SITE_URL."</a></p>
                            <p style='margin: 0 0 15px 0;'><strong>👤 Usuário:</strong> <code>{$user['username']}</code></p>
                            <p style='margin: 0;'><strong>🔑 Senha Temporária:</strong> <code style='background: #cbd5e1; padding: 4px 8px; border-radius: 6px; font-weight: 800;'>{$tempPassword}</code></p>
                        </div>
                        <p style='color: #ef4444; font-size: 13px; margin-top: 30px;'>
                            <i class='fas fa-exclamation-triangle'></i> 
                            Por segurança, sua senha anterior foi alterada. Recomendamos trocá-la após o primeiro acesso.
                        </p>
                    </div>
                </div>
            ";

            if (\Mailer::send($user['email'], $subject, $body)) {
                try {
                    require_once __DIR__ . '/../../../includes/logs.php';
                    \Logger::log('security_email', "Dados de recuperação/vínculo enviados para o e-mail {$user['email']} (Usuário: {$user['name']}).");
                    
                    // NOTIFICATION (Rule 39)
                    require_once __DIR__ . '/../../../includes/repositories/NotificationRepository.php';
                    $notifRepo = new \NotificationRepository(\DB::getInstance());
                    $notifRepo->create([
                        'user_id' => 1,
                        'title'   => '📧 Credenciais Enviadas',
                        'message' => "Link de acesso enviado para: {$user['email']}",
                        'link'    => SITE_URL . '/admin/logs',
                        'type'    => 'info'
                    ]);
                } catch (\Exception $e) {}
                
                $this->jsonResponse(['success' => true, 'message' => 'Dados de acesso enviados para o e-mail: ' . $user['email']]);
            } else {
                $this->jsonResponse(['success' => false, 'message' => 'Falha ao enviar e-mail. Verifique as configurações de SMTP.']);
            }

        } catch (\Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => 'Erro interno: ' . $e->getMessage()], 500);
        }
    }
}
