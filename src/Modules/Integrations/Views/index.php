<?php
/** @var array $settings */
/** @var string $active_tab */
$v = (string)time();
?>
<link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/modules/integrations.css?v=<?php echo $v; ?>">

<div class="tab-navigation">
    <a href="?tab=email" class="nav-link-tab <?php echo $active_tab === 'email' ? 'active' : ''; ?>">
        <i data-lucide="mail" class="icon-lucide icon-sm"></i> E-mail (SMTP)
    </a>
</div>

<div class="card user-list-card">
    <?php if ($active_tab === 'email'): ?>
        <form onsubmit="saveIntegration(event, this)">
            <input type="hidden" name="type" value="email">

            <div class="integration-header">
                <div class="integration-icon-box">
                    <i data-lucide="mail"></i>
                </div>
                <div>
                    <h3 class="text-main font-md m-0 fw-800">Configurações de E-mail (SMTP)</h3>
                </div>
            </div>
            <p class="text-muted font-sm mb-4">Configure o servidor SMTP para o envio de notificações e e-mails do sistema.</p>
            
            <div class="form-grid-3">
                <div class="floating-group">
                    <input type="text" name="smtp_host" value="<?php echo htmlspecialchars($settings['smtp_host'] ?? ''); ?>" class="form-control" placeholder=" ">
                    <label class="floating-label">Servidor SMTP (Host)</label>
                </div>
                <div class="floating-group">
                    <input type="text" name="smtp_port" value="<?php echo htmlspecialchars($settings['smtp_port'] ?? ''); ?>" class="form-control" placeholder=" ">
                    <label class="floating-label">Porta SMTP</label>
                </div>
                <div class="floating-group">
                    <select name="smtp_secure" class="form-control" placeholder=" ">
                        <option value="" <?php echo ($settings['smtp_secure'] ?? '') === '' ? 'selected' : ''; ?>>Nenhum</option>
                        <option value="ssl" <?php echo ($settings['smtp_secure'] ?? '') === 'ssl' ? 'selected' : ''; ?>>SSL (Porta 465)</option>
                        <option value="tls" <?php echo ($settings['smtp_secure'] ?? '') === 'tls' ? 'selected' : ''; ?>>TLS / STARTTLS (Porta 587)</option>
                    </select>
                    <label class="floating-label">Segurança de Conexão</label>
                </div>
            </div>

            <div class="form-grid-2 mt-4">
                <div class="floating-group">
                    <input type="text" name="smtp_user" value="<?php echo htmlspecialchars($settings['smtp_user'] ?? ''); ?>" class="form-control" placeholder=" ">
                    <label class="floating-label">Usuário SMTP / Login</label>
                </div>
                <div class="floating-group">
                    <div class="password-toggle-wrapper">
                        <input type="password" name="smtp_pass" id="smtp-pass" value="<?php echo htmlspecialchars($settings['smtp_pass'] ?? ''); ?>" class="form-control" placeholder=" ">
                        <label class="floating-label">Senha SMTP</label>
                        <div class="floating-actions">
                            <button type="button" class="btn-password-toggle" onclick="UI.togglePassword(this, 'smtp-pass')">
                                <i data-lucide="eye"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-grid-2 mt-4">
                <div class="floating-group">
                    <input type="email" name="smtp_from_email" value="<?php echo htmlspecialchars($settings['smtp_from_email'] ?? ''); ?>" class="form-control" placeholder=" ">
                    <label class="floating-label">E-mail de Envio (Remetente)</label>
                </div>
                <div class="floating-group">
                    <input type="text" name="smtp_from_name" value="<?php echo htmlspecialchars($settings['smtp_from_name'] ?? ''); ?>" class="form-control" placeholder=" ">
                    <label class="floating-label">Nome de Exibição (Assinatura)</label>
                </div>
            </div>

            <div class="integration-actions">
                <button type="submit" class="btn-primary">
                    <i data-lucide="save" class="icon-lucide icon-sm mr-2"></i> Salvar Integração de E-mail
                </button>
                <button type="button" onclick="testSmtp()" class="btn-secondary">
                    <i data-lucide="send" class="icon-lucide icon-sm mr-2"></i> Enviar E-mail Teste
                </button>
            </div>
        </form>
    <?php endif; ?>
</div>

<script>
async function saveIntegration(e, form) {
    e.preventDefault();
    const btn = form.querySelector('button[type="submit"]');
    if (btn) btn.disabled = true;

    try {
        const formData = new FormData(form);
        const res = await fetch('<?php echo SITE_URL; ?>/api/admin/integrations/save', {
            method: 'POST',
            body: formData
        });
        const result = await res.json();
        if (result.success) {
            UI.showToast('Integração salva com sucesso!');
        } else {
            UI.showToast(result.message || 'Erro ao salvar', 'error');
        }
    } catch (error) {
        UI.showToast('Erro de conexão', 'error');
    } finally {
        if (btn) btn.disabled = false;
    }
}

async function testSmtp() {
    const email = await UI.prompt('Para qual e-mail deseja enviar o teste?', {
        title: '📧 Testar Conexão SMTP',
        placeholder: 'nome@exemplo.com'
    });
    
    if (!email) return;

    UI.showToast('Enviando e-mail de teste...', 'info');

    const formData = new FormData();
    formData.append('email', email);

    try {
        const response = await fetch('<?php echo SITE_URL; ?>/api/admin/integrations/test-email', {
            method: 'POST',
            body: formData
        });
        const data = await response.json();
        if (data.success) {
            UI.showToast(data.message, 'success');
        } else {
            UI.showToast(data.message || 'Falha no teste.', 'error');
        }
    } catch (e) {
        UI.showToast('Erro ao enviar teste.', 'error');
    }
}
</script>
