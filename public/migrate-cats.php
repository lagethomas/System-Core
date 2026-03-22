<?php
require_once __DIR__ . '/includes/db.php';
global $pdo;

$sql = "CREATE TABLE IF NOT EXISTS cp_categorias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    slug VARCHAR(100) UNIQUE NOT NULL,
    icone VARCHAR(50) DEFAULT 'fa-tags',
    ordem INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

try {
    $pdo->exec($sql);
    echo "Tabela cp_categorias criada com sucesso!\n";
    
    // Check if cp_produtos needs category_id
    $checkProduct = $pdo->query("SHOW COLUMNS FROM cp_produtos LIKE 'categoria_id'");
    if (!$checkProduct->fetch()) {
        $pdo->exec("ALTER TABLE cp_produtos ADD COLUMN categoria_id INT NULL AFTER categoria");
        echo "Coluna categoria_id adicionada a cp_produtos!\n";
    }
} catch (PDOException $e) {
    echo "Erro: " . $e->getMessage() . "\n";
}
