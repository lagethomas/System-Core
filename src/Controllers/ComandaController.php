<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Helpers\Logger;
use Auth;

class ComandaController extends Controller {
    
    public function index(): void {
        Auth::requireRole('atendente');
        $caixa = Database::fetch("SELECT status FROM cp_caixas WHERE status = 'aberto' LIMIT 1");

        $this->render('app/comanda', [
            'title' => 'Gerenciamento de Comandas',
            'caixa_aberto' => (bool)$caixa,
            'is_admin' => Auth::isAdmin(),
            'nonces' => [
                'add_mesa' => \Nonce::create('comanda_add_mesa'),
                'abrir_mesa' => \Nonce::create('comanda_abrir_mesa'),
                'lancar_item' => \Nonce::create('comanda_lancar_item'),
                'remover_item' => \Nonce::create('comanda_remover_item'),
                'fechar_mesa' => \Nonce::create('comanda_fechar_mesa'),
                'remover_mesa' => \Nonce::create('comanda_remover_mesa')
            ]
        ]);
    }

    /**
     * API: Listar todas as mesas
     */
    public function getMesas(): void {
        try {
            $mesas = Database::fetchAll("SELECT * FROM cp_mesas ORDER BY numero ASC");
            
            // Para cada mesa ocupada, buscar o total da comanda
            foreach ($mesas as &$mesa) {
                if ($mesa['status'] === 'ocupada') {
                    $total = Database::fetch("SELECT SUM(preco * quantidade) as total FROM cp_comanda_itens WHERE mesa_id = :id", ['id' => $mesa['id']]);
                    $mesa['total'] = (float)($total['total'] ?? 0);
                    
                    $itensCount = Database::fetch("SELECT COUNT(*) as count FROM cp_comanda_itens WHERE mesa_id = :id", ['id' => $mesa['id']]);
                    $mesa['itens_count'] = (int)($itensCount['count'] ?? 0);
                } else {
                    $mesa['total'] = 0;
                    $mesa['itens_count'] = 0;
                }
            }
            
            $this->jsonResponse(['success' => true, 'data' => $mesas]);
        } catch (\Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * API: Adicionar nova mesa
     */
    public function addMesa(): void {
        Auth::requireAdmin();
        try {
            $data = json_decode(file_get_contents('php://input'), true);

            // Rule 6: Validate Nonce
            if (!\Nonce::verify($data['nonce'] ?? '', 'comanda_add_mesa')) {
                $this->jsonResponse(['success' => false, 'message' => 'Erro de segurança (Nonce inválido)'], 403);
                return;
            }

            $lastMesa = Database::fetch("SELECT MAX(numero) as max_num FROM cp_mesas");
            $nextNum = ($lastMesa['max_num'] ?? 0) + 1;
            
            $id = Database::insert('cp_mesas', [
                'numero' => $nextNum,
                'status' => 'livre'
            ]);
            
            $this->jsonResponse(['success' => true, 'id' => $id, 'numero' => $nextNum]);
        } catch (\Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * API: Buscar itens de uma comanda
     */
    public function getComanda(string $id): void {
        try {
            $mesa = Database::fetch("SELECT * FROM cp_mesas WHERE id = :id", ['id' => $id]);
            if (!$mesa) {
                $this->jsonResponse(['success' => false, 'message' => 'Mesa não encontrada'], 404);
                return;
            }
            
            $itens = Database::fetchAll("SELECT * FROM cp_comanda_itens WHERE mesa_id = :id ORDER BY criado_em DESC", ['id' => $id]);
            
            $this->jsonResponse([
                'success' => true, 
                'mesa' => $mesa,
                'itens' => $itens
            ]);
        } catch (\Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * API: Abrir mesa
     */
    public function abrirMesa(string $id): void {
        try {
            $data = json_decode(file_get_contents('php://input'), true);

            // Rule 6: Validate Nonce
            if (!\Nonce::verify($data['nonce'] ?? '', 'comanda_abrir_mesa')) {
                $this->jsonResponse(['success' => false, 'message' => 'Erro de segurança (Nonce inválido)'], 403);
                return;
            }

            Database::update('cp_mesas', ['status' => 'ocupada'], 'id = :id', ['id' => $id]);
            $this->jsonResponse(['success' => true]);
        } catch (\Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * API: Lançar item na comanda
     */
    public function lançarItem(string $id): void {
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            
            // Rule 6: Validate Nonce
            if (!\Nonce::verify($data['nonce'] ?? '', 'comanda_lancar_item')) {
                $this->jsonResponse(['success' => false, 'message' => 'Erro de segurança (Nonce inválido)'], 403);
                return;
            }

            if (empty($data['nome']) || empty($data['preco'])) {
                $this->jsonResponse(['success' => false, 'message' => 'Dados inválidos'], 400);
                return;
            }
            
            $itemId = Database::insert('cp_comanda_itens', [
                'mesa_id' => $id,
                'produto_nome' => $data['nome'],
                'preco' => $data['preco'],
                'quantidade' => $data['quantidade'] ?? 1,
                'criado_em' => date('Y-m-d H:i:s')
            ]);
            
            $this->jsonResponse(['success' => true, 'id' => $itemId]);
        } catch (\Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * API: Remover item da comanda
     */
    public function removerItem(string $id): void {
        try {
            // Check nonce from header or query (comanda.js sends it in body for POST, but DELETE uses query usually in fetch if handled)
            $data = json_decode(file_get_contents('php://input'), true);
            $nonce = $data['nonce'] ?? ($_GET['nonce'] ?? '');

            // Rule 6: Validate Nonce
            if (!\Nonce::verify($nonce, 'comanda_remover_item')) {
                $this->jsonResponse(['success' => false, 'message' => 'Erro de segurança (Nonce inválido)'], 403);
                return;
            }

            Database::delete('cp_comanda_itens', 'id = :id', ['id' => $id]);
            $this->jsonResponse(['success' => true]);
        } catch (\Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * API: Fechar mesa (Limpar itens e liberar)
     */
    public function fecharMesa(string $id): void {
        try {
            $data = json_decode(file_get_contents('php://input'), true);

            // Rule 6: Validate Nonce
            if (!\Nonce::verify($data['nonce'] ?? '', 'comanda_fechar_mesa')) {
                $this->jsonResponse(['success' => false, 'message' => 'Erro de segurança (Nonce inválido)'], 403);
                return;
            }

            // Calcular o total da mesa antes de limpar
            $totalData = Database::fetch("SELECT SUM(preco * quantidade) as total FROM cp_comanda_itens WHERE mesa_id = :id", ['id' => $id]);
            $subtotal = (float)($totalData['total'] ?? 0);

            $finalTotal = 0;
             if ($subtotal > 0) {
                $metodo = $data['metodo'] ?? 'Dinheiro';
                $taxa = (float)($data['taxa_servico'] ?? 0);
                $cover = (float)($data['cover'] ?? 0);
                $finalTotal = $subtotal + $taxa + $cover;

                // Verificar se há caixa aberto
                $caixa = Database::fetch("SELECT id FROM cp_caixas WHERE status = 'aberto' LIMIT 1");
                $caixa_id = $caixa ? (int)$caixa['id'] : null;

                $mesa = Database::fetch("SELECT numero FROM cp_mesas WHERE id = :id", ['id' => $id]);
                $num = $mesa['numero'] ?? $id;

                // Registrar entrada no financeiro
                Database::insert('cp_financeiro', [
                    'descricao' => "Fechamento Comanda - Mesa $num — $metodo",
                    'valor' => $finalTotal,
                    'tipo' => 'entrada',
                    'metodo_pagamento' => $metodo,
                    'referencia_id' => (int)$id,
                    'caixa_id' => $caixa_id,
                    'taxa_servico' => $taxa,
                    'cover' => $cover
                ]);

                Logger::log('comanda_fechada', "Fechamento Mesa $num de R$ " . number_format($finalTotal, 2, ',', '.') . " (Sub: " . number_format($subtotal, 2, ',', '.') . ", Taxa: " . number_format($taxa, 2, ',', '.') . ", Cover: " . number_format($cover, 2, ',', '.') . ") via $metodo");
            }

            // Limpar itens e liberar mesa
            Database::delete('cp_comanda_itens', 'mesa_id = :id', ['id' => $id]);
            Database::update('cp_mesas', ['status' => 'livre'], 'id = :id', ['id' => $id]);
            
            $this->jsonResponse(['success' => true, 'total' => $finalTotal]);
        } catch (\Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * API: Remover mesa permanentemente
     */
    public function removerMesa(string $id): void {
        Auth::requireAdmin();
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            $nonce = $data['nonce'] ?? ($_GET['nonce'] ?? '');

            // Rule 6: Validate Nonce
            if (!\Nonce::verify($nonce, 'comanda_remover_mesa')) {
                $this->jsonResponse(['success' => false, 'message' => 'Erro de segurança (Nonce inválido)'], 403);
                return;
            }

            // Verificar se a mesa está limpa (opcional, mas seguro)
            $itens = Database::fetch("SELECT COUNT(*) as count FROM cp_comanda_itens WHERE mesa_id = :id", ['id' => $id]);
            if (($itens['count'] ?? 0) > 0) {
                $this->jsonResponse(['success' => false, 'message' => 'Não é possível remover uma mesa com itens lançados'], 400);
                return;
            }

            Database::delete('cp_mesas', 'id = :id', ['id' => $id]);
            $this->jsonResponse(['success' => true]);
        } catch (\Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
