<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Helpers\Logger;

class FinanceiroController extends Controller {
    
    public function index(): void {
        $start_date = $_GET['start_date'] ?? date('Y-m-01'); // Início do mês atual por padrão
        $end_date = $_GET['end_date'] ?? date('Y-m-d');

        $where = " WHERE DATE(data_movimentacao) BETWEEN :start AND :end ";
        $params = ['start' => $start_date, 'end' => $end_date];

        $movimentacoes = Database::fetchAll("SELECT * FROM cp_financeiro $where ORDER BY data_movimentacao DESC LIMIT 200", $params);
        
        // Calcular resumo (com base no filtro)
        $res_entradas = Database::fetch("SELECT SUM(valor) as total FROM cp_financeiro $where AND tipo = 'entrada'", $params);
        $totalEntradas = (float)($res_entradas['total'] ?? 0);
        
        $res_saidas = Database::fetch("SELECT SUM(valor) as total FROM cp_financeiro $where AND tipo = 'saida'", $params);
        $totalSaidas = (float)($res_saidas['total'] ?? 0);
        
        $saldo = $totalEntradas - $totalSaidas;

        // Dados para o gráfico (últimos 7 dias)
        $chartData = [];
        for ($i = 6; $i >= 0; $i--) {
            $offset = '-' . (string)$i . ' days';
            $date = date('Y-m-d', (int)strtotime($offset));
            $label = date('d/m', strtotime($date));
            
            $entrada = (float)(Database::fetch("SELECT SUM(valor) as total FROM cp_financeiro WHERE tipo = 'entrada' AND DATE(data_movimentacao) = :d", ['d' => $date])['total'] ?? 0);
            $saida = (float)(Database::fetch("SELECT SUM(valor) as total FROM cp_financeiro WHERE tipo = 'saida' AND DATE(data_movimentacao) = :d", ['d' => $date])['total'] ?? 0);
            
            $chartData[] = [
                'label' => $label,
                'entrada' => (float)$entrada,
                'saida' => (float)$saida
            ];
        }

        $this->render('app/financeiro', [
            'title' => 'Módulo Financeiro',
            'movimentacoes' => $movimentacoes,
            'filters' => [
                'start_date' => $start_date,
                'end_date' => $end_date
            ],
            'resumo' => [
                'entradas' => (float)$totalEntradas,
                'saidas' => (float)$totalSaidas,
                'saldo' => (float)$saldo
            ],
            'chartData' => $chartData,
            'nonce_add' => \Nonce::create('financeiro_add'),
            'nonce_delete' => \Nonce::create('financeiro_delete'),
        ]);
    }

    public function addMovimentacao(): void {
        // Rule 6: Validate Nonce
        if (!\Nonce::verify($_POST['nonce'] ?? '', 'financeiro_add')) {
            $this->jsonResponse(['success' => false, 'message' => 'Erro de segurança (Nonce inválido)'], 403);
            return;
        }

        $descricao = $_POST['descricao'] ?? '';
        $valor = str_replace(',', '.', $_POST['valor'] ?? '0');
        $tipo = $_POST['tipo'] ?? 'entrada';

        if (empty($descricao) || empty($valor)) {
            $this->jsonResponse(['success' => false, 'message' => 'Campos obrigatórios'], 400);
            return;
        }

        $metodo = $_POST['metodo_pagamento'] ?? 'Outros';

        // Verificar se há caixa aberto
        $caixa = Database::fetch("SELECT id FROM cp_caixas WHERE status = 'aberto' LIMIT 1");
        $caixa_id = $caixa ? (int)$caixa['id'] : null;

        Database::insert('cp_financeiro', [
            'descricao' => $descricao,
            'valor' => (float)$valor,
            'tipo' => $tipo,
            'metodo_pagamento' => $metodo,
            'caixa_id' => $caixa_id
        ]);

        Logger::log('financeiro_manual', "Lançamento manual de R$ " . number_format((float)$valor, 2, ',', '.') . " ($tipo) via $metodo — $descricao");

        $this->jsonResponse(['success' => true]);
    }

    public function delete(): void {
        // Rule 6: Validate Nonce
        if (!\Nonce::verify($_POST['nonce'] ?? '', 'financeiro_delete')) {
            $this->jsonResponse(['success' => false, 'message' => 'Erro de segurança (Nonce inválido)'], 403);
            return;
        }

        $id = $_POST['id'] ?? null;
        if ($id) {
            // Impedir deleção de registros com referência (Comandas)
            $mov = Database::fetch("SELECT referencia_id FROM cp_financeiro WHERE id = :id", ['id' => $id]);
            if ($mov && !is_null($mov['referencia_id'])) {
                $this->jsonResponse(['success' => false, 'message' => 'Lançamentos de comanda não podem ser excluídos'], 400);
                return;
            }

            Database::query("DELETE FROM cp_financeiro WHERE id = :id", ['id' => $id]);
            Logger::log('financeiro_deletado', "Removeu lançamento financeiro ID #$id");
            $this->jsonResponse(['success' => true]);
        }
    }
}
