<?php
/**
 * Migration Helper - Category System & Products Update
 */
require_once __DIR__ . '/../../includes/db.php';
global $pdo;

$token = $_GET['token'] ?? '';
$validToken = '76269223e7';

header('Content-Type: application/json');

if ($token !== $validToken) {
    echo json_encode(['success' => false, 'message' => 'Acesso negado. Token inválido.']);
    exit;
}

$results = [];

try {
    // 1. Create Categorias Table
    $sqlCat = "CREATE TABLE IF NOT EXISTS cp_categorias (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nome VARCHAR(100) NOT NULL,
        slug VARCHAR(100) UNIQUE NOT NULL,
        icone VARCHAR(50) DEFAULT 'fa-tags',
        ordem INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    
    $pdo->exec($sqlCat);
    $results[] = 'Tabela cp_categorias criada ou já existente.';

    // 2. Update Products Table
    $checkCol = $pdo->query("SHOW COLUMNS FROM cp_produtos LIKE 'categoria_id'");
    if (!$checkCol->fetch()) {
        $pdo->exec("ALTER TABLE cp_produtos ADD COLUMN categoria_id INT NULL AFTER categoria");
        $results[] = 'Coluna categoria_id adicionada a cp_produtos.';
    } else {
        $results[] = 'Coluna categoria_id já existe em cp_produtos.';
    }

    echo json_encode([
        'success' => true, 
        'message' => 'Migrações executadas com sucesso!',
        'logs' => $results
    ], JSON_PRETTY_PRINT);

} catch (PDOException $e) {
    echo json_encode([
        'success' => false, 
        'message' => 'Erro na migração: ' . $e->getMessage()
    ], JSON_PRETTY_PRINT);
}
