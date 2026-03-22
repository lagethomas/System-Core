<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;

class CardapioController extends Controller {
    public function index(): void {
        // Fetch only categories that HAVE products available in menu
        $categorias = Database::fetchAll("
            SELECT DISTINCT c.* 
            FROM cp_categorias c
            INNER JOIN cp_produtos p ON p.categoria_id = c.id
            WHERE p.disponivel_cardapio = 1
            ORDER BY c.ordem ASC, c.nome ASC
        ");

        $produtos = Database::fetchAll("
            SELECT p.*, c.nome as categoria_nome, c.slug as categoria_slug
            FROM cp_produtos p
            LEFT JOIN cp_categorias c ON p.categoria_id = c.id
            WHERE p.disponivel_cardapio = 1
            ORDER BY c.ordem ASC, p.nome ASC
        ");
        
        $this->render('public/cardapio', [
            'produtos' => $produtos,
            'categorias' => $categorias,
            'title' => 'Nosso Cardápio'
        ], false); 
    }
}
