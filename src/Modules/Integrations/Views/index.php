<?php declare(strict_types=1);
/** @var array $settings */
/** @var string $active_tab */
?>

<div class="settings-page-header mb-5">
    <div class="flex items-center gap-4">
        <div class="header-icon-box">
            <i data-lucide="plug"></i>
        </div>
        <div>
            <h2 class="m-0">Integrações do Sistema</h2>
            <p class="text-muted m-0">Conecte serviços externos e automatize processos da plataforma.</p>
        </div>
    </div>
</div>

<div class="tab-navigation mb-4">
    <a href="?tab=email" class="nav-link-tab <?php echo $active_tab === 'email' ? 'active' : ''; ?>">
        <i data-lucide="mail" class="icon-sm"></i> E-mail (SMTP)
    </a>
</div>

<div class="card p-5">
    <?php if ($active_tab === 'email'): ?>
        <form action="<?php echo SITE_URL; ?>/api/admin/integrations/save" method="POST" class="ajax-form" data-no-reload="true">
            <input type="hidden" name="type" value="email">
            <input type="hidden" name="csrf_token" value="<?php echo CSRF::generateToken(); ?>">

            <div class="flex items-center gap-3 mb-4">
                <div class="header-icon-box" style="width: 48px; height: 48px; border-radius: 12px;">
                    <i data-lucide="mail" class="icon-sm"></i>
                </div>
                <div>
                    <h3 class="text-main m-0 fw-800">Configurações de E-mail (SMTP)</h3>
                    <p class="text-muted small m-0">Configure o servidor SMTP para o envio de notificações e e-mails do sistema.</p>
                </div>
            </div>
            
            <div class="form-grid-3 gap-4 mb-4">
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

            <div class="form-grid-2 gap-4 mb-4">
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

            <div class="form-grid-2 gap-4 mb-5">
                <div class="floating-group">
                    <input type="email" name="smtp_from_email" value="<?php echo htmlspecialchars($settings['smtp_from_email'] ?? ''); ?>" class="form-control" placeholder=" ">
                    <label class="floating-label">E-mail de Envio (Remetente)</label>
                </div>
                <div class="floating-group">
                    <input type="text" name="smtp_from_name" value="<?php echo htmlspecialchars($settings['smtp_from_name'] ?? ''); ?>" class="form-control" placeholder=" ">
                    <label class="floating-label">Nome de Exibição (Assinatura)</label>
                </div>
            </div>

            <div class="pt-5 border-top flex justify-end gap-3">
                <button type="button" onclick="testSmtp()" class="btn-dark" style="padding: 12px 25px; border-radius: 12px;">
                    <i data-lucide="send" class="icon-sm mr-2"></i> Enviar E-mail Teste
                </button>
                <button type="submit" class="btn-primary" style="padding: 12px 30px; border-radius: 12px; font-weight: 700;">
                    <i data-lucide="save" class="icon-sm mr-2"></i> Salvar Integração
                </button>
            </div>
        </form>
    <?php endif; ?>
</div>

<script src="<?php echo SITE_URL; ?>/assets/js/modules/integrations.js"></script>
