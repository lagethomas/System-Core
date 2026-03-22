<?php
/**
 * SaaSFlow Core – Secure Migration Runner
 * 
 * Usage: 
 *   CLI: php public/migrations/migrations.php
 *   Web: https://yourdomain.com/migrations/migrations.php?token=YOUR_TOKEN
 */
declare(strict_types=1);

// ── Correct PATHS ───────────────────────────────────────────
$root = dirname(dirname(__DIR__));
require_once $root . '/config/config.php';
require_once $root . '/includes/DB.php';

// --- SECURITY CHECK ---
// You can define DB_MIGRATION_TOKEN in your .env
$envToken   = $_ENV['DB_MIGRATION_TOKEN'] ?? 'saasflow_migrate_2026'; 
$inputToken = $_GET['token'] ?? '';

$isCLI = (php_sapi_name() === 'cli');

if (!$isCLI) {
    if ($inputToken !== $envToken) {
        header('HTTP/1.1 403 Forbidden');
        die("❌ Acesso Negado: Token de migração inválido.");
    }
}

echo $isCLI ? "" : "<pre>";
echo "🚀 Iniciando Migrações do Sistema...\n";

try {
    // Get PDO instance from the global $pdo initialized in includes/DB.php
    global $pdo;
    if (!$pdo) {
        $pdo = \DB::getInstance();
    }

    // ── Pre-check: Migration Tracking Table ────────────────────
    $pdo->exec("CREATE TABLE IF NOT EXISTS `cp_migrations` (
        `migration_id` INT PRIMARY KEY,
        `title` VARCHAR(255),
        `executed_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB;");

    // ── Get All Migrations in migrations.sql ─────────────────────
    $migration_file = __DIR__ . '/migrations.sql';
    if (!file_exists($migration_file)) {
        die("❌ Erro: migrations.sql não foi encontrado em " . __DIR__ . "\n");
    }

    $sql_content = file_get_contents($migration_file);
    
    // Split by indexed comments like -- [MIGRATION #001]
    $parts = preg_split('/--\s*\[MIGRATION\s*#(\d+)\]/', $sql_content, -1, PREG_SPLIT_DELIM_CAPTURE);
    
    if (count($parts) < 2) {
        die("ℹ️ Nenhuma migração encontrada no formato esperado (#001).\n");
    }

    array_shift($parts); // Remove text before the first migration marker

    // ── Execution Loop ───────────────────────────────────────────
    for ($i = 0; $i < count($parts); $i += 2) {
        $mid    = (int)$parts[$i];
        $blob   = (string)$parts[$i + 1];

        // Parse Title
        preg_match('/--\s*Title:\s*([^\n]+)/', $blob, $matches);
        $title = trim($matches[1] ?? ("Migração #" . (string)$mid));

        // Check if already executed
        $check = $pdo->prepare("SELECT COUNT(*) FROM cp_migrations WHERE migration_id = ?");
        $check->execute([$mid]);
        if ($check->fetchColumn() > 0) {
            echo sprintf("⏭️  MIG #%03d [%s] já executada. Pulando.\n", $mid, $title);
            continue;
        }

        // Clean up the SQL (remove comments and extra whitespace)
        $sqlClean = preg_replace('/--.*$/m', '', $blob); // remove comments
        $statements = array_filter(array_map('trim', explode(';', $sqlClean)));

        echo sprintf("⚙️  Executando MIG #%03d [%s]...", $mid, $title);

        try {
            // Nota: MySQL não suporta transações para DDL (CREATE/ALTER). 
            // Cada comando desse commita automaticamente a transação.
            foreach ($statements as $stmt) {
                if (empty($stmt)) continue;
                $pdo->exec($stmt);
            }

            // Record execution
            $pdo->prepare("INSERT INTO cp_migrations (migration_id, title) VALUES (?, ?)")
                ->execute([$mid, $title]);

            echo " ✅ SUCESSO!\n";
        } catch (\PDOException $e) {
            echo " ❌ ERRO: " . $e->getMessage() . "\n";
            die("\n🛑 Migração interrompida devido a erro.\n");
        }
    }

    echo "\n🏆 Todas as migrações concluídas com sucesso.\n";
    echo $isCLI ? "" : "</pre>";

} catch (\Exception $e) {
    echo "🚨 Erro fatal durante a migração: " . $e->getMessage() . "\n";
}
