<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;
use App\Helpers\Logger;
use Auth;

class CategoriasController extends Controller {
    
    public function index(): void {
        Auth::requireAdmin();
        
        $categorias = Database::fetchAll("
            SELECT c.*, (SELECT COUNT(*) FROM cp_produtos p WHERE p.categoria = c.nome OR p.categoria_id = c.id) as total_produtos 
            FROM cp_categorias c 
            ORDER BY c.ordem ASC, c.nome ASC
        ");

        $this->render('admin/categorias', [
            'categorias' => $categorias,
            'nonces' => [
                'save' => \Nonce::create('save_categoria'),
                'delete' => \Nonce::create('delete_categoria')
            ]
        ]);
    }

    public function save(): void {
        Auth::requireAdmin();
        
        if (!\Nonce::verify($_POST['nonce'] ?? '', 'save_categoria')) {
            $this->jsonResponse(['success' => false, 'message' => 'Erro de segurança (Nonce).'], 403);
            return;
        }

        $id = $_POST['id'] ?? '';
        $nome = trim($_POST['nome'] ?? '');
        $icone = trim($_POST['icone'] ?? 'fa-tags');
        $ordem = (int)($_POST['ordem'] ?? 0);

        if (empty($nome)) {
            $this->jsonResponse(['success' => false, 'message' => 'O nome da categoria é obrigatório.'], 400);
            return;
        }

        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $nome)));

        $data = [
            'nome' => $nome,
            'slug' => $slug,
            'icone' => $icone,
            'ordem' => $ordem
        ];

        if ($id) {
            Database::update('cp_categorias', $data, 'id = :id_where', ['id_where' => $id]);
            Logger::log('categoria_editada', "Editou categoria: $nome");
        } else {
            Database::insert('cp_categorias', $data);
            Logger::log('categoria_criada', "Criou categoria: $nome");
        }

        $this->jsonResponse(['success' => true]);
    }

    public function delete(): void {
        Auth::requireAdmin();
        
        if (!\Nonce::verify($_POST['nonce'] ?? '', 'delete_categoria')) {
            $this->jsonResponse(['success' => false, 'message' => 'Erro de segurança (Nonce).'], 403);
            return;
        }

        $id = $_POST['id'] ?? null;
        if ($id) {
            Database::delete('cp_categorias', 'id = :id', ['id' => $id]);
            Logger::log('categoria_deletada', "Removeu categoria ID #$id");
            $this->jsonResponse(['success' => true]);
        }
    }
}
