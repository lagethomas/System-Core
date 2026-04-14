<?php
/** @var array $settings */
/** @var string $active_tab */
/** @var array $migrations */
?>

<div class="settings-tab-nav">
    <a href="?tab=general" class="nav-link-tab <?php echo $active_tab === 'general' ? 'active' : ''; ?>">
        <i data-lucide="settings"></i> Geral
    </a>
    <a href="?tab=themes" class="nav-link-tab <?php echo $active_tab === 'themes' ? 'active' : ''; ?>">
        <i data-lucide="palette"></i> Temas
    </a>
    <a href="?tab=security" class="nav-link-tab <?php echo $active_tab === 'security' ? 'active' : ''; ?>">
        <i data-lucide="shield"></i> Segurança
    </a>

</div>

<div class="card settings-main-card">
    <!-- General Settings Tab -->
    <?php if ($active_tab === 'general'): ?>
        <form id="form-general" onsubmit="saveSettings(event, 'general')">
            <input type="hidden" name="csrf_token" value="<?php echo CSRF::generateToken(); ?>">
            <input type="hidden" name="tab" value="general">
            
            <div class="settings-header-box">
                <h5><i data-lucide="settings" class="text-primary"></i> Configurações Gerais</h5>
                <p>Gerencie a identidade básica e funcionamento do sistema.</p>
            </div>
            
            <div class="form-grid-5 mb-4">
                <div class="upload-box-wrapper p-3">
                    <label class="upload-label"><i data-lucide="info"></i> Nome</label>
                    <div class="form-group mt-2">
                        <input type="text" name="system_name" value="<?php echo htmlspecialchars($settings['system_name'] ?? ''); ?>" class="form-control" placeholder="ex: SaaSFlow">
                    </div>
                    <small class="text-muted d-block mt-auto pt-2">Identificação principal do sistema.</small>
                </div>

                <div class="upload-box-wrapper p-3">
                    <label class="upload-label"><i data-lucide="terminal"></i> Logs</label>
                    <div class="form-group mt-2">
                        <label class="switch-label d-flex align-items-center justify-content-between cursor-pointer p-0">
                            <span class="fs-11 opacity-08">Ativar em Disco</span>
                            <label class="switch scale-08 mr-n5">
                                <input type="checkbox" name="enable_system_logs" value="1" <?php echo ($settings['enable_system_logs'] ?? '0') === '1' ? 'checked' : ''; ?>>
                                <span class="slider"></span>
                            </label>
                        </label>
                    </div>
                    <small class="text-muted d-block mt-auto pt-2">Registro de erros técnicos em disco.</small>
                </div>

                <div class="upload-box-wrapper p-3">
                    <label class="upload-label"><i data-lucide="image"></i> Logo</label>
                    <div class="upload-clickable-container">
                        <div id="preview-logo" class="upload-preview-box upload-clickable" onclick="document.getElementById('logo-upload').click()">
                            <?php if (!empty($settings['system_logo'])): ?>
                                <img src="<?php echo SITE_URL; ?>/uploads/logos/<?php echo $settings['system_logo']; ?>" alt="Logo" class="logo-img">
                            <?php else: ?>
                                <i data-lucide="upload" class="upload-placeholder-icon"></i>
                                <span class="upload-placeholder-text">Clique para enviar</span>
                            <?php endif; ?>
                            <div class="upload-hover-overlay"><i data-lucide="upload"></i><span>Alterar</span></div>
                        </div>
                        <input type="file" id="logo-upload" name="system_logo" accept="image/*" onchange="previewImage(this, 'preview-logo', 'logo-img')" style="display: none;">
                    </div>
                </div>

                <div class="upload-box-wrapper p-3">
                    <label class="upload-label"><i data-lucide="monitor"></i> Background</label>
                    <div class="upload-clickable-container">
                        <div id="preview-bg" class="upload-preview-box upload-clickable" onclick="document.getElementById('bg-upload').click()">
                            <?php if (!empty($settings['login_background'])): ?>
                                <img src="<?php echo SITE_URL; ?>/uploads/backgrounds/<?php echo $settings['login_background']; ?>" alt="BG" class="bg-img">
                            <?php else: ?>
                                <i data-lucide="upload" class="upload-placeholder-icon"></i>
                                <span class="upload-placeholder-text">Clique para enviar</span>
                            <?php endif; ?>
                            <div class="upload-hover-overlay"><i data-lucide="upload"></i><span>Alterar</span></div>
                        </div>
                        <input type="file" id="bg-upload" name="login_background" accept="image/*" onchange="previewImage(this, 'preview-bg', 'bg-img')" style="display: none;">
                    </div>
                </div>
            </div>

            <div class="settings-footer-section">
                <button type="submit" class="btn-primary settings-save-btn">
                    <i data-lucide="save"></i> Salvar Agora
                </button>
            </div>
        </form>

    <!-- Themes Tab (Rule 26: Selectable Cards) -->
    <?php elseif ($active_tab === 'themes'): ?>
        <form id="form-themes" onsubmit="saveSettings(event, 'themes')">
            <input type="hidden" name="csrf_token" value="<?php echo CSRF::generateToken(); ?>">
            <input type="hidden" name="tab" value="themes">
            
            <div class="settings-header-box">
                <h5><i data-lucide="palette" class="text-primary"></i> Tema do Sistema</h5>
                <p>Identidade visual do painel interno.</p>
            </div>

            <div class="theme-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 20px;">
                <?php 
                $themes = ThemeHelper::getAvailableThemes();
                $current_theme = $settings['system_theme'] ?? 'gold-black';
                foreach ($themes as $slug => $theme): 
                    $isSelected = ($slug === $current_theme);
                ?>
                    <div class="selectable-card <?php echo $isSelected ? 'active' : ''; ?>" onclick="toggleSelectableCard(this, 'theme_<?php echo $slug; ?>')">
                        <input type="radio" name="system_theme" id="theme_<?php echo $slug; ?>" value="<?php echo $slug; ?>" <?php echo $isSelected ? 'checked' : ''; ?>>
                        <div class="theme-card-preview" style="background: <?php echo $theme['bg']; ?>; width: 100%; height: 60px; border-radius: 8px; position: relative;">
                            <div style="position: absolute; top: 10px; right: 10px; width: 20px; height: 20px; border-radius: 5px; background: <?php echo $theme['color']; ?>;"></div>
                        </div>
                        <span><?php echo $theme['name']; ?></span>
                        <div class="theme-check-icon"><i data-lucide="check"></i></div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="settings-header-box mt-5">
                <h5><i data-lucide="monitor" class="text-primary"></i> Tema da Tela de Login</h5>
                <p>Identidade visual da página de acesso.</p>
            </div>

            <div class="theme-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 20px;">
                <?php 
                $current_login_theme = $settings['system_login_theme'] ?? 'gold-black';
                foreach ($themes as $slug => $theme): 
                    $isSelected = ($slug === $current_login_theme);
                ?>
                    <div class="selectable-card <?php echo $isSelected ? 'active' : ''; ?>" onclick="toggleSelectableCard(this, 'login_theme_<?php echo $slug; ?>')">
                        <input type="radio" name="system_login_theme" id="login_theme_<?php echo $slug; ?>" value="<?php echo $slug; ?>" <?php echo $isSelected ? 'checked' : ''; ?>>
                        <div class="theme-card-preview" style="background: <?php echo $theme['bg']; ?>; width: 100%; height: 60px; border-radius: 8px; position: relative;">
                            <div style="position: absolute; top: 10px; right: 10px; width: 20px; height: 20px; border-radius: 5px; background: <?php echo $theme['color']; ?>;"></div>
                        </div>
                        <span><?php echo $theme['name']; ?></span>
                        <div class="theme-check-icon"><i data-lucide="check"></i></div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="settings-footer-section">
                <button type="submit" class="btn-primary settings-save-btn">
                    <i data-lucide="save"></i> Aplicar Temas
                </button>
            </div>
        </form>

    <!-- Security Tab -->
    <?php elseif ($active_tab === 'security'): ?>
        <form id="form-security" onsubmit="saveSettings(event, 'security')">
            <input type="hidden" name="csrf_token" value="<?php echo CSRF::generateToken(); ?>">
            <input type="hidden" name="tab" value="security">
            
            <div class="settings-header-box">
                <h5><i data-lucide="shield" class="text-primary"></i> Segurança</h5>
                <p>Governança de acessos e logs.</p>
            </div>

            <div class="form-grid-5">
                <div class="upload-box-wrapper p-3">
                    <label class="upload-label"><i data-lucide="user-x"></i> Tentativas</label>
                    <input type="number" name="security_max_attempts" value="<?php echo $settings['security_max_attempts'] ?? '5'; ?>" class="form-control mt-2">
                </div>
                <div class="upload-box-wrapper p-3">
                    <label class="upload-label"><i data-lucide="hourglass"></i> Bloqueio</label>
                    <input type="number" name="security_lockout_time" value="<?php echo $settings['security_lockout_time'] ?? '15'; ?>" class="form-control mt-2">
                </div>
                <div class="upload-box-wrapper p-3">
                    <label class="upload-label"><i data-lucide="clock"></i> Inatividade</label>
                    <input type="number" name="security_session_timeout" value="<?php echo $settings['security_session_timeout'] ?? '120'; ?>" class="form-control mt-2">
                </div>
                <div class="upload-box-wrapper p-3">
                    <label class="upload-label"><i data-lucide="log-in"></i> Sessão Única</label>
                    <label class="switch mt-2">
                        <input type="checkbox" name="security_single_session" value="1" <?php echo ($settings['security_single_session'] ?? '0') === '1' ? 'checked' : ''; ?>>
                        <span class="slider"></span>
                    </label>
                </div>
                <div class="upload-box-wrapper p-3" style="grid-column: span 2;">
                    <label class="upload-label"><i data-lucide="shield"></i> IPs Bloqueados</label>
                    <textarea name="security_blocked_ips" class="form-control mt-2" rows="3" placeholder="Um IP por linha..."><?php
                        try {
                            global $pdo;
                            $blocked = $pdo->query("SELECT ip_address FROM cp_blocked_ips")->fetchAll(PDO::FETCH_COLUMN);
                            echo implode("\n", $blocked);
                        } catch(\Exception $e) {}
                    ?></textarea>
                </div>
            </div>

            <div class="settings-footer-section">
                <button type="submit" class="btn-primary settings-save-btn">
                    <i data-lucide="save"></i> Salvar Segurança
                </button>
            </div>
        </form>
    <?php endif; ?>
</div>

<!-- Standard Scripts -->
<script src="<?php echo SITE_URL; ?>/assets/js/components/selectable-cards.js?v=<?php echo time(); ?>"></script>

<script>
/**
 * AJAX Settings Save (Rule 8)
 */
async function saveSettings(e, tab) {
    e.preventDefault();
    const form = e.target;
    const btn = form.querySelector('.settings-save-btn');
    const originalContent = btn.innerHTML;

    btn.disabled = true;
    btn.innerHTML = '<i data-lucide="loader-2" class="animate-spin mr-2"></i> Salvando...';
    if(window.lucide) lucide.createIcons();

    try {
        const formData = new FormData(form);
        const response = await fetch('<?php echo SITE_URL ?>/api/admin/settings/save', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();
        if (result.success) {
            UI.showToast(result.message);
            if (tab === 'themes') {
                setTimeout(() => window.location.reload(), 1000);
            }
        } else {
            UI.showToast(result.message || 'Erro ao sincronizar', 'error');
        }
    } catch (error) {
        UI.showToast('Erro crítico de comunicação', 'error');
    } finally {
        btn.disabled = false;
        btn.innerHTML = originalContent;
        if(window.lucide) lucide.createIcons();
    }
}

/**
 * Migration Runner (Rule 52)
 */
async function runMigrations(btn) {
    if (!await UI.confirm('Deseja executar as migrações estruturais agora?')) return;

    const originalContent = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i data-lucide="loader-2" class="animate-spin mr-2"></i> Processando...';
    if(window.lucide) lucide.createIcons();

    try {
        const response = await fetch('<?php echo SITE_URL ?>/api/admin/migrations/run', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' }
        });

        const result = await response.json();
        if (result.success) {
            UI.showToast('Migrações concluídas com sucesso!');
            setTimeout(() => window.location.reload(), 1500);
        } else {
            UI.showToast(result.message, 'error');
        }
    } catch (e) {
        UI.showToast('Falha na execução', 'error');
    } finally {
        btn.disabled = false;
        btn.innerHTML = originalContent;
        if(window.lucide) lucide.createIcons();
    }
}

/**
 * Image Preview Helper
 */
function previewImage(input, previewId, imgClass) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById(previewId);
            let img = preview.querySelector('img');
            if (!img) {
                preview.innerHTML = `<img src="${e.target.result}" class="${imgClass}"> <div class="upload-hover-overlay"><i data-lucide="upload"></i><span>Alterar</span></div>`;
            } else {
                img.src = e.target.result;
            }
            if(window.lucide) lucide.createIcons();
        }
        reader.readAsDataURL(input.files[0]);
    }
}
</script>


