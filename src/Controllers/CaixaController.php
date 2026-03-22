<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Helpers\Logger;
use Auth;

class CaixaController extends Controller {
    
    public function index(): void {
        $caixaAberto = $this->getCaixaAberto();
        $historico = Database::fetchAll("SELECT c.*, u.name as usuario_nome FROM cp_caixas c JOIN cp_users u ON c.user_id = u.id ORDER BY c.data_abertura DESC LIMIT 30");

        $movimentacoes = [];
        if ($caixaAberto) {
            $movimentacoes = Database::fetchAll("SELECT * FROM cp_financeiro WHERE caixa_id = :cid ORDER BY data_movimentacao DESC", ['cid' => $caixaAberto['id']]);
        }

        $this->render('app/caixa', [
            'caixa' => $caixaAberto,
            'historico' => $historico,
            'movimentacoes' => $movimentacoes,
            'nonce_abrir' => \Nonce::create('caixa_abrir'),
            'nonce_fechar' => \Nonce::create('caixa_fechar')
        ]);
    }

    public function abrir(): void {
        $data = json_decode(file_get_contents('php://input'), true);
        
        // Rule 6: Validate Nonce
        if (!\Nonce::verify($data['nonce'] ?? '', 'caixa_abrir')) {
            $this->jsonResponse(['success' => false, 'message' => 'Erro de segurança (Nonce inválido)'], 403);
            return;
        }

        if ($this->getCaixaAberto()) {
            $this->jsonResponse(['success' => false, 'message' => 'Já existe um caixa aberto.'], 400);
            return;
        }

        $rawBalance = $data['saldo_inicial'] ?? '0';

        // Limpeza radical: remove tudo que não seja número ou vírgula/ponto
        $cleanedBalance = preg_replace('/[^0-9,.]/', '', (string)$rawBalance);
        
        // Se houver vírgula, assume formato brasileiro 1.234,56
        if (strpos($cleanedBalance, ',') !== false) {
            $cleanedBalance = str_replace('.', '', $cleanedBalance); // remove milhares
            $cleanedBalance = str_replace(',', '.', $cleanedBalance); // transforma decimal
        }
        
        $cleanedBalance = (float)$cleanedBalance;

        $id = Database::insert('cp_caixas', [
            'user_id' => Auth::id(),
            'status' => 'aberto',
            'saldo_inicial' => $cleanedBalance,
            'data_abertura' => date('Y-m-d H:i:s')
        ]);

        Logger::log('caixa_aberto', "Abertura de caixa (#$id) com saldo de R$ " . number_format($cleanedBalance, 2, ',', '.'));

        $this->jsonResponse(['success' => true, 'id' => $id]);
    }

    public function fechar(): void {
        $data = json_decode(file_get_contents('php://input'), true);
        
        // Rule 6: Validate Nonce
        if (!\Nonce::verify($data['nonce'] ?? '', 'caixa_fechar')) {
            $this->jsonResponse(['success' => false, 'message' => 'Erro de segurança (Nonce inválido)'], 403);
            return;
        }

        $caixa = $this->getCaixaAberto();
        if (!$caixa) {
            $this->jsonResponse(['success' => false, 'message' => 'Nenhum caixa aberto.'], 400);
            return;
        }

        // Calcular saldo final
        $entradas = Database::fetch("SELECT SUM(valor) as total FROM cp_financeiro WHERE caixa_id = :cid AND tipo = 'entrada'", ['cid' => $caixa['id']])['total'] ?? 0;
        $saidas = Database::fetch("SELECT SUM(valor) as total FROM cp_financeiro WHERE caixa_id = :cid AND tipo = 'saida'", ['cid' => $caixa['id']])['total'] ?? 0;
        $saldoFinal = $caixa['saldo_inicial'] + $entradas - $saidas;

        Database::update('cp_caixas', [
            'status' => 'fechado',
            'saldo_final' => (float)$saldoFinal,
            'data_fechamento' => date('Y-m-d H:i:s')
        ], 'id = :id', ['id' => $caixa['id']]);

        Logger::log('caixa_fechado', "Fechamento de caixa (#" . (string)$caixa['id'] . "). Saldo final: R$ " . number_format($saldoFinal, 2, ',', '.'));

        $this->jsonResponse(['success' => true]);
    }

    public function status(): void {
        $caixa = $this->getCaixaAberto();
        $this->jsonResponse(['success' => true, 'aberto' => (bool)$caixa, 'caixa' => $caixa]);
    }

    private function getCaixaAberto() {
        return Database::fetch("SELECT * FROM cp_caixas WHERE status = 'aberto' LIMIT 1");
    }
}
