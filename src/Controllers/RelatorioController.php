<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;

class RelatorioController extends Controller {
    
    public function index(): void {
        \Auth::requireRole('caixa');
        $start_date = $_GET['start_date'] ?? date('Y-m-01');
        $end_date = $_GET['end_date'] ?? date('Y-m-d');
        $params = ['start' => $start_date, 'end' => $end_date];

        // Obter dados financeiros filtrados
        $movimentacoes = Database::fetchAll("
            SELECT 
                DATE(data_movimentacao) as data,
                SUM(CASE WHEN tipo = 'entrada' THEN valor ELSE 0 END) as entradas,
                SUM(CASE WHEN tipo = 'saida' THEN valor ELSE 0 END) as saidas
            FROM cp_financeiro 
            WHERE DATE(data_movimentacao) BETWEEN :start AND :end
            GROUP BY DATE(data_movimentacao)
            ORDER BY data ASC
        ", $params);

        foreach ($movimentacoes as &$m) {
            $m['entradas'] = (float)$m['entradas'];
            $m['saidas'] = (float)$m['saidas'];
        }

        // Obter dados por método de pagamento (Entradas apenas filtradas)
        $metodos = Database::fetchAll("
            SELECT 
                metodo_pagamento,
                SUM(valor) as total
            FROM cp_financeiro
            WHERE tipo = 'entrada' AND DATE(data_movimentacao) BETWEEN :start AND :end
            GROUP BY metodo_pagamento
        ", $params);

        // Top Produtos mais vendidos
        $topProdutos = Database::fetchAll("
            SELECT 
                produto_nome,
                SUM(quantidade) as qte,
                SUM(preco * quantidade) as total
            FROM cp_comanda_itens
            GROUP BY produto_nome
            ORDER BY total DESC
            LIMIT 5
        ");

        // Histórico de Caixas
        $caixas = Database::fetchAll("
            SELECT c.*, u.name as usuario_nome
            FROM cp_caixas c
            JOIN cp_users u ON c.user_id = u.id
            WHERE DATE(c.data_abertura) BETWEEN :start AND :end
            ORDER BY c.data_abertura DESC
        ", $params);

        // Para carregar totais de cada caixa
        foreach ($caixas as &$c) {
            $stats = Database::fetch("
                SELECT 
                    SUM(CASE WHEN tipo = 'entrada' THEN valor ELSE 0 END) as entradas,
                    SUM(CASE WHEN tipo = 'saida' THEN valor ELSE 0 END) as saidas
                FROM cp_financeiro 
                WHERE caixa_id = :cid
            ", ['cid' => $c['id']]);
            
            $c['entradas'] = (float)($stats['entradas'] ?? 0);
            $c['saidas'] = (float)($stats['saidas'] ?? 0);
            
            // Lógica: Comanda tem referencia_id, PDV tem descrição específica
            $c['pagamentos_comanda'] = (float)(Database::fetch("SELECT SUM(valor) as total FROM cp_financeiro WHERE caixa_id = :cid AND tipo = 'entrada' AND referencia_id IS NOT NULL", ['cid' => $c['id']])['total'] ?? 0);
            $c['pagamentos_pdv'] = (float)(Database::fetch("SELECT SUM(valor) as total FROM cp_financeiro WHERE caixa_id = :cid AND tipo = 'entrada' AND referencia_id IS NULL AND descricao LIKE 'Venda Direta PDV%'", ['cid' => $c['id']])['total'] ?? 0);
            
            // Entradas manuais (financeiro direto) = total entradas - comanda - pdv
            $c['entradas_manuais'] = $c['entradas'] - $c['pagamentos_comanda'] - $c['pagamentos_pdv'];
            $c['lucro'] = $c['entradas'] - $c['saidas'];
        }

        $this->render('app/relatorios', [
            'movimentacoes' => $movimentacoes,
            'metodos' => $metodos,
            'topProdutos' => $topProdutos,
            'caixas' => $caixas,
            'filters' => [
                'start_date' => $start_date,
                'end_date' => $end_date
            ]
        ]);
    }
}
