<?php
/**
 * SaaSFlow Core – Secure Migration Runner
 * Usage: 
 *   CLI: php scripts/migrations.php
 *   Web: https://yourdomain.com/scripts/migrations.php?token=YOUR_TOKEN
 */
declare(strict_types=1);

// Load configuration and environment
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/DB.php';

// --- SECURITY CHECK (Rule 28 from .env) ---
$envToken   = $_ENV['DB_MIGRATION_TOKEN'] ?? '';
$inputToken = $_GET['token'] ?? '';

// If running in CLI, skip token check if user is on console OR required if specified
$isCLI = (php_sapi_name() === 'cli');

if (!$isCLI) {
    // Web Access Security
    if (empty($envToken)) {
        die("❌ Erro: DB_MIGRATION_TOKEN não está definido no .env. Migração bloqueada via WEB por segurança.\n");
    }

    if ($inputToken !== $envToken) {
        header('HTTP/1.1 403 Forbidden');
        die("❌ Acesso Negado: Token de migração inválido.\n");
    }
}

echo "<pre>"; // For browser readability
echo "🚀 Iniciando Migrações do Sistema...\n";

try {
    $pdo = \DB::getInstance();

    // ── Pre-check: Migration Tracking Table ────────────────────
    $pdo->exec("CREATE TABLE IF NOT EXISTS `cp_migrations` (
        `migration_id` INT PRIMARY KEY,
        `title` VARCHAR(255),
        `executed_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB;");

    // ── Get All Migrations in scripts/migrations.sql ─────────────
    $migration_file = __DIR__ . '/migrations.sql';
    if (!file_exists($migration_file)) {
        die("❌ Erro: scripts/migrations.sql não foi encontrado.\n");
    }

    $sql_content = file_get_contents($migration_file);
    // Split by indexed comments like -- [MIGRATION #001]
    $migrations = preg_split('/--\s*\[MIGRATION\s*#(\d+)\]/', $sql_content, -1, PREG_SPLIT_DELIM_CAPTURE);

    array_shift($migrations); // remove header before first migration

    // ── Execution Loop ───────────────────────────────────────────
    for ($i = 0; $i < count($migrations); $i += 2) {
        $mid    = (int)$migrations[$i];
        $blob   = (string)$migrations[$i + 1];

        // Titulo e SQL parsing
        preg_match('/--\s*Title:\s*([^\n]+)/', $blob, $matches);
        $title = (string)($matches[1] ?? 'Sem título');

        // Check if already executed
        $check = $pdo->prepare("SELECT COUNT(*) FROM cp_migrations WHERE migration_id = ?");
        $check->execute([$mid]);
        if ($check->fetchColumn() > 0) {
            echo sprintf("⏭️  MIG #%d [%s] já executada. Pulando.\n", $mid, $title);
            continue;
        }

        // Cleanup the SQL to execute
        $sql = preg_replace('/--.*/', '', $blob); // remove remaining comments
        $sql = trim((string)$sql);

        if (empty($sql)) continue;

        try {
            echo sprintf("⚙️  Executando MIG #%d [%s]...", $mid, $title);
            
            // Execute each statement one by one (if there are multiple)
            $statements = array_filter(explode(';', $sql));
            foreach ($statements as $stmt) {
                $pdo->exec($stmt);
            }

            // Record execution
            $pdo->prepare("INSERT INTO cp_migrations (migration_id, title) VALUES (?, ?)")
                ->execute([(int)$mid, (string)$title]);

            echo " ✅ SUCESSO!\n";
        } catch (\PDOException $e) {
            echo " ❌ ERRO: " . $e->getMessage() . "\n";
        }
    }

    echo "\n🏆 Todas as migrações concluídas.\n";
    echo "</pre>";

} catch (\Exception $e) {
    echo "🚨 Erro fatal durante a migração: " . $e->getMessage() . "\n";
}
