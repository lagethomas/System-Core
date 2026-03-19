<?php
<<<<<<<< HEAD:src/Views/admin/integrations.php
/** @var array $settings */
/** @var string $active_tab */
/** @var string $msg */

include_once __DIR__ . '/../../../includes/header.php';
========
declare(strict_types=1);
require_once '../../includes/DB.php';
require_once dirname(__DIR__, 2) . "/includes/helpers/Auth.php";

Auth::requireAdmin();

$active_tab = $_GET['tab'] ?? 'email';

// Fetch Current Settings
$stmt = $pdo->prepare("SELECT setting_key, setting_value FROM cp_settings");
$stmt->execute();
$settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['save_email'])) {
        $keys = ['smtp_host', 'smtp_port', 'smtp_user', 'smtp_pass', 'smtp_from_email', 'smtp_from_name', 'smtp_secure'];
        foreach ($keys as $key) {
            $val = trim($_POST[$key] ?? '');
            $stmt = $pdo->prepare("INSERT INTO cp_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
            $stmt->execute([$key, $val, $val]);
        }
        $msg = 'Configurações de e-mail salvas com sucesso!';
    }
    
    // Refresh settings
    $stmt = $pdo->prepare("SELECT setting_key, setting_value FROM cp_settings");
    $stmt->execute();
    $settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
}

include_once '../../includes/header.php';
>>>>>>>> ab660bf99d6d155d59d9302691d0bc8f9c62eeb9:public/admin/integrations.php
?>

<div class="settings-tab-nav">
    <a href="?tab=email" class="nav-link-tab <?php echo $active_tab === 'email' ? 'active' : ''; ?>">
        <i class="fas fa-envelope"></i> E-mail (SMTP)
    </a>
</div>

<?php if ($msg): ?>
    <div class="alert-success-custom">
        <i class="fas fa-check-circle"></i> <?php echo $msg; ?>
    </div>
<?php endif; ?>

<div class="integration-card">
    <?php if ($active_tab === 'email'): ?>
        <form method="POST">
            <div class="integration-header">
                <i class="fas fa-envelope text-primary"></i>
                <h5>Configurações de E-mail (SMTP)</h5>
            </div>
            <p class="integration-subtitle">Configure o servidor SMTP para o envio de notificações e e-mails do sistema.</p>
            
            <div class="form-grid-3 mb-4">
                <div class="form-group">
                    <label class="form-label">Host SMTP</label>
                    <input type="text" name="smtp_host" value="<?php echo htmlspecialchars($settings['smtp_host'] ?? ''); ?>" class="form-control w-100" placeholder="ex: smtp.gmail.com">
                </div>
                <div class="form-group">
                    <label class="form-label">Porta SMTP</label>
                    <input type="text" name="smtp_port" value="<?php echo htmlspecialchars($settings['smtp_port'] ?? ''); ?>" class="form-control w-100" placeholder="ex: 587">
                </div>
                <div class="form-group">
                    <label class="form-label">Segurança</label>
                    <select name="smtp_secure" class="form-control w-100">
                        <option value="" <?php echo ($settings['smtp_secure'] ?? '') === '' ? 'selected' : ''; ?>>Nenhum</option>
                        <option value="ssl" <?php echo ($settings['smtp_secure'] ?? '') === 'ssl' ? 'selected' : ''; ?>>SSL (Porta 465)</option>
                        <option value="tls" <?php echo ($settings['smtp_secure'] ?? '') === 'tls' ? 'selected' : ''; ?>>TLS / STARTTLS (Porta 587)</option>
                    </select>
                </div>
            </div>

            <div class="form-grid-2 mb-4">
                <div class="form-group">
                    <label class="form-label">Usuário SMTP</label>
                    <input type="text" name="smtp_user" value="<?php echo htmlspecialchars($settings['smtp_user'] ?? ''); ?>" class="form-control w-100">
                </div>
                <div class="form-group">
                    <label class="form-label">Senha SMTP</label>
                    <input type="password" name="smtp_pass" value="<?php echo htmlspecialchars($settings['smtp_pass'] ?? ''); ?>" class="form-control w-100">
                </div>
            </div>

            <div class="form-grid-2 mb-4">
                <div class="form-group">
                    <label class="form-label">E-mail de Envio (From Email)</label>
                    <input type="email" name="smtp_from_email" value="<?php echo htmlspecialchars($settings['smtp_from_email'] ?? ''); ?>" class="form-control w-100" placeholder="ex: no-reply@seusistema.com">
                </div>
                <div class="form-group">
                    <label class="form-label">Nome de Exibição (From Name)</label>
                    <input type="text" name="smtp_from_name" value="<?php echo htmlspecialchars($settings['smtp_from_name'] ?? ''); ?>" class="form-control w-100" placeholder="ex: SaaSFlow Core">
                </div>
            </div>

            <div class="integration-footer">
                <button type="submit" name="save_email" class="btn-primary btn-integration-save">
                    <i class="fas fa-save"></i> Salvar Integração de E-mail
                </button>
                <button type="button" onclick="sendTestEmail()" class="btn-secondary btn-integration-test">
                    <i class="fas fa-paper-plane"></i> Enviar E-mail Teste
                </button>
            </div>
        </form>
    <?php endif; ?>
</div>

<script>
async function sendTestEmail() {
    const email = prompt('Para qual e-mail deseja enviar o teste?');
    if (!email) return;

    const formData = new FormData();
    formData.append('email', email);

    try {
        const response = await fetch('<?php echo SITE_URL; ?>/api/admin/test_email', {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        const data = await response.json();
        alert(data.message);
    } catch (e) {
        alert('Erro ao enviar teste.');
    }
}
</script>

<<<<<<<< HEAD:src/Views/admin/integrations.php
<?php include_once __DIR__ . '/../../../includes/footer.php'; ?>
========
<?php include_once '../../includes/footer.php'; ?>
>>>>>>>> ab660bf99d6d155d59d9302691d0bc8f9c62eeb9:public/admin/integrations.php
