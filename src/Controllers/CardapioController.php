<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;

class CardapioController extends Controller {
    public function index(): void {
        $produtos = Database::fetchAll("SELECT * FROM cp_produtos WHERE disponivel_cardapio = 1 ORDER BY categoria ASC, nome ASC");
        
        $this->render('public/cardapio', [
            'produtos' => $produtos,
            'title' => 'Nosso Cardápio'
        ], false); // false to not use the main layout if it's public, or use it depending on design. User says "Página pública", I'll make it standalone but beautiful.
    }
}
