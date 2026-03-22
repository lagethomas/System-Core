<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Helpers\Logger;

class PdvController extends Controller {
    
    public function index(): void {
        $caixa = Database::fetch("SELECT status FROM cp_caixas WHERE status = 'aberto' LIMIT 1");
        
        $this->render('app/pdv', [
            'title' => 'Frente de Caixa (PDV)',
            'caixa_aberto' => (bool)$caixa,
            'nonce' => \Nonce::create('pdv_checkout')
        ]);
    }

    /**
     * Finaliza uma venda rápida e lança no financeiro
     */
    public function checkout(): void {
        $data = json_decode(file_get_contents('php://input'), true);
        
        // Rule 6: Validate Nonce
        if (!\Nonce::verify($data['nonce'] ?? '', 'pdv_checkout')) {
            $this->jsonResponse(['success' => false, 'message' => 'Erro de segurança (Nonce inválido)'], 403);
            return;
        }

        $total = $data['total'] ?? 0;
        $metodo = $data['metodo'] ?? 'Dinheiro';
        $itens = $data['itens'] ?? [];

        if ($total <= 0 || empty($itens)) {
            $this->jsonResponse(['success' => false, 'message' => 'Venda vazia'], 400);
        }

        // Verificar se há caixa aberto
        $caixa = Database::fetch("SELECT id FROM cp_caixas WHERE status = 'aberto' LIMIT 1");
        $caixa_id = $caixa ? (int)$caixa['id'] : null;

        // Registrar no financeiro
        Database::insert('cp_financeiro', [
            'descricao' => "Venda Direta PDV — $metodo",
            'valor' => (float)$total,
            'tipo' => 'entrada',
            'metodo_pagamento' => $metodo,
            'caixa_id' => $caixa_id
        ]);

        require_once __DIR__ . '/../../includes/logs.php';
        Logger::log('pdv_venda', "Venda PDV de R$ " . number_format((float)$total, 2, ',', '.') . " via $metodo");

        $this->jsonResponse(['success' => true]);
    }
}
