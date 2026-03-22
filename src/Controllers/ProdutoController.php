<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Helpers\Logger;

class ProdutoController extends Controller {
    
    public function index(): void {
        $produtos = Database::fetchAll("SELECT * FROM cp_produtos ORDER BY nome ASC");
        $this->render('app/produtos', [
            'produtos' => $produtos,
            'nonces' => [
                'save' => \Nonce::create('save_produto'),
                'delete' => \Nonce::create('delete_produto')
            ]
        ]);
    }

    public function save(): void {
        if (!\Nonce::verify($_POST['nonce'] ?? '', 'save_produto')) {
            $this->jsonResponse(['success' => false, 'message' => 'Erro de segurança (Nonce).'], 403);
            return;
        }
        $id = $_POST['id'] ?? '';
        $nome = $_POST['nome'] ?? '';
        $precoRaw = $_POST['preco'] ?? '0';
        // Remove R$ symbol if present and whitespace
        $precoRaw = trim(str_replace(['R$', ' '], '', $precoRaw));
        
        if (strpos($precoRaw, ',') !== false) {
            // Brazilian format: 1.234,56 -> 1234.56
            $preco = str_replace(['.', ','], ['', '.'], $precoRaw);
        } else {
            // Standard format or already cleaned: 1234.56
            $preco = $precoRaw;
        }

        $preco = (float)$preco;
        $categoria = $_POST['categoria'] ?? 'Diversos';
        $codigo = $_POST['codigo'] ?? '';
        $descricao = $_POST['descricao'] ?? '';
        $disponivel = isset($_POST['disponivel_cardapio']) ? 1 : 0;

        if (empty($nome)) {
            $this->jsonResponse(['success' => false, 'message' => 'Nome é obrigatório'], 400);
            return;
        }

        if (empty($codigo)) {
            $codigo = strtoupper(substr(uniqid(), -6));
        }

        $data = [
            'nome' => $nome,
            'preco' => (float)$preco,
            'categoria' => $categoria,
            'codigo' => $codigo,
            'descricao' => $descricao,
            'disponivel_cardapio' => $disponivel
        ];

        // Handle Image Upload & WebP Conversion
        if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === UPLOAD_ERR_OK) {
            $tmpPath = $_FILES['imagem']['tmp_name'];
            $filename = 'prod_' . time() . '_' . $codigo . '.webp';
            $uploadDir = dirname(dirname(__DIR__)) . '/public/assets/img/produtos/';
            
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            $uploadPath = $uploadDir . $filename;

            if ($this->convertToWebP($tmpPath, $uploadPath)) {
                // If editing, delete old image
                if ($id) {
                    $old = Database::fetch("SELECT imagem FROM cp_produtos WHERE id = :id", ['id' => $id]);
                    if ($old && $old['imagem']) {
                        $oldFullPath = dirname(dirname(__DIR__)) . '/public' . $old['imagem'];
                        if (file_exists($oldFullPath)) @unlink($oldFullPath);
                    }
                }
                $data['imagem'] = '/assets/img/produtos/' . $filename;
            }
        }

        if ($id) {
            Database::update('cp_produtos', $data, 'id = :id', ['id' => $id]);
            Logger::log('produto_editado', "Editou produto $nome (Código: $codigo)");
        } else {
            Database::insert('cp_produtos', $data);
            Logger::log('produto_criado', "Criou novo produto $nome (Código: $codigo)");
        }

        $this->jsonResponse(['success' => true]);
    }

    /**
     * Converts an image to WebP format
     */
    private function convertToWebP(string $source, string $destination, int $quality = 80): bool {
        $info = getimagesize($source);
        if (!$info) return false;

        $mime = $info['mime'];
        switch ($mime) {
            case 'image/jpeg': $img = imagecreatefromjpeg($source); break;
            case 'image/png': 
                $img = imagecreatefrompng($source); 
                imagepalettetotruecolor($img);
                imagealphablending($img, true);
                imagesavealpha($img, true);
                break;
            case 'image/gif': $img = imagecreatefromgif($source); break;
            case 'image/webp': $img = imagecreatefromwebp($source); break;
            default: return false;
        }

        if (!$img) return false;
        $success = imagewebp($img, $destination, $quality);
        imagedestroy($img);
        return $success;
    }

    public function delete(): void {
        if (!\Nonce::verify($_POST['nonce'] ?? '', 'delete_produto')) {
            $this->jsonResponse(['success' => false, 'message' => 'Erro de segurança (Nonce).'], 403);
            return;
        }
        $id = $_POST['id'] ?? null;
        if ($id) {
            $prod = Database::fetch("SELECT imagem FROM cp_produtos WHERE id = :id", ['id' => $id]);
            if ($prod && $prod['imagem']) {
                $filePath = dirname(dirname(__DIR__)) . '/public' . $prod['imagem'];
                if (file_exists($filePath)) @unlink($filePath);
            }

            Database::delete('cp_produtos', 'id = :id', ['id' => $id]);
            Logger::log('produto_deletado', "Removeu produto ID #$id");
            $this->jsonResponse(['success' => true]);
        }
    }

    public function listApi(): void {
        $produtos = Database::fetchAll("SELECT * FROM cp_produtos ORDER BY nome ASC");
        $this->jsonResponse(['success' => true, 'data' => $produtos]);
    }
}
