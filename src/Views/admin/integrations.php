<?php
/** @var array $settings */
/** @var string $active_tab */
/** @var string $msg */
?>


<div class="settings-tab-nav">
    <a href="?tab=email" class="nav-link-tab <?php echo $active_tab === 'email' ? 'active' : ''; ?>">
        <i data-lucide="mail"></i> E-mail (SMTP)
    </a>
</div>

<?php if ($msg): ?>
    <div class="alert-success-custom">
        <i data-lucide="check-circle"></i> <?php echo $msg; ?>
    </div>
<?php endif; ?>

<div class="integration-card">
    <?php if ($active_tab === 'email'): ?>
        <form method="POST">
            <div class="integration-header">
                <i data-lucide="mail" class="text-primary"></i>
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
                    <i data-lucide="save"></i> Salvar Integração de E-mail
                </button>
                <button type="button" onclick="sendTestEmail()" class="btn-secondary btn-integration-test">
                    <i data-lucide="send"></i> Enviar E-mail Teste
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


