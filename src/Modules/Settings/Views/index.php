<?php
/** @var array $settings */
/** @var string $active_tab */

require_once dirname(dirname(dirname(dirname(__DIR__)))) . '/includes/helpers/CSRF.php';
require_once dirname(dirname(dirname(dirname(__DIR__)))) . '/includes/helpers/ThemeHelper.php';
$v = (string)time();
?>

<div class="tab-navigation">
    <a href="?tab=general" class="nav-link-tab <?php echo $active_tab === 'general' ? 'active' : ''; ?>">
        <i data-lucide="settings" class="icon-lucide icon-sm"></i> Geral
    </a>
    <a href="?tab=themes" class="nav-link-tab <?php echo $active_tab === 'themes' ? 'active' : ''; ?>">
        <i data-lucide="palette" class="icon-lucide icon-sm"></i> Temas
    </a>
    <a href="?tab=security" class="nav-link-tab <?php echo $active_tab === 'security' ? 'active' : ''; ?>">
        <i data-lucide="shield" class="icon-lucide icon-sm"></i> Segurança
    </a>
</div>

<div class="card card-settings">
    <!-- General Settings Tab -->
    <?php if ($active_tab === 'general'): ?>
        <form id="form-general" action="<?php echo SITE_URL ?>/api/admin/settings/save" method="POST" class="ajax-form" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?php echo CSRF::generateToken(); ?>">
            <input type="hidden" name="tab" value="general">
            
            <div class="settings-header">
                <h3 class="settings-title">Configurações Gerais</h3>
                <p class="settings-subtitle">Defina a identidade da sua plataforma e o comportamento do sistema.</p>
            </div>
            
            <div class="form-grid-2 gap-md">
                <div class="floating-group">
                    <input type="text" name="system_name" value="<?php echo htmlspecialchars($settings['system_name'] ?? ''); ?>" class="form-control" placeholder=" " required>
                    <label class="floating-label">Nome da Plataforma</label>
                </div>

                <div class="switch-container">
                    <div>
                        <span class="switch-label">Ativar Logs do Sistema</span>
                        <small class="text-muted font-xs">Registra erros técnicos para auditoria</small>
                    </div>
                    <label class="switch">
                        <input type="checkbox" name="enable_system_logs" value="1" <?php echo ($settings['enable_system_logs'] ?? '0') === '1' ? 'checked' : ''; ?>>
                        <span class="slider"></span>
                    </label>
                </div>

                <div class="floating-group">
                    <input type="number" name="items_per_page" value="<?php echo htmlspecialchars($settings['items_per_page'] ?? '25'); ?>" class="form-control" placeholder=" " required min="5" max="100">
                    <label class="floating-label">Itens por Página (Tabelas)</label>
                    <small class="text-muted font-xs">Padrão do sistema: 25 itens</small>
                </div>
            </div>

            <div class="form-grid-2 mt-5 gap-md">
                <div>
                    <label class="settings-upload-label">Logotipo da Plataforma</label>
                    <input type="hidden" name="remove_logo" id="logo-remove-flag" value="0">
                    <div id="preview-logo" class="upload-selector" onclick="document.getElementById('logo-upload').click()">
                        <?php if (!empty($settings['system_logo'])): ?>
                            <img src="<?php echo SITE_URL; ?>/uploads/logos/<?php echo $settings['system_logo']; ?>" class="preview-img" id="img-logo">
                            <button type="button" class="btn-remove-image" onclick="removeImage(event, 'preview-logo', 'logo-remove-flag', 'logo-upload')" title="Remover Logo">
                                <i data-lucide="x"></i>
                            </button>
                        <?php else: ?>
                            <div class="upload-empty">
                                <i data-lucide="image" class="icon-lucide"></i>
                                <span>Selecionar Imagem</span>
                            </div>
                        <?php endif; ?>
                        <div class="upload-overlay"><i data-lucide="upload-cloud"></i> <span>Alterar Logo</span></div>
                    </div>
                    <input type="file" id="logo-upload" name="system_logo" accept="image/*" onchange="previewImage(this, 'preview-logo', 'logo-remove-flag')" style="display: none;">
                    <p class="font-xs text-muted mt-2 italic">* Recomendado: Fundo transparente (PNG/SVG)</p>
                </div>

                <div>
                    <label class="settings-upload-label">Wallpaper da Tela de Login</label>
                    <input type="hidden" name="remove_login_bg" id="bg-remove-flag" value="0">
                    <div id="preview-bg" class="upload-selector" onclick="document.getElementById('bg-upload').click()">
                        <?php if (!empty($settings['login_background'])): ?>
                            <img src="<?php echo SITE_URL; ?>/uploads/backgrounds/<?php echo $settings['login_background']; ?>" class="preview-img object-cover" id="img-bg">
                            <button type="button" class="btn-remove-image" onclick="removeImage(event, 'preview-bg', 'bg-remove-flag', 'bg-upload')" title="Remover Fundo">
                                <i data-lucide="x"></i>
                            </button>
                        <?php else: ?>
                            <div class="upload-empty">
                                <i data-lucide="monitor" class="icon-lucide"></i>
                                <span>Selecionar Wallpaper</span>
                            </div>
                        <?php endif; ?>
                        <div class="upload-overlay"><i data-lucide="upload-cloud"></i> <span>Alterar Fundo</span></div>
                    </div>
                    <input type="file" id="bg-upload" name="login_background" accept="image/*" onchange="previewImage(this, 'preview-bg', 'bg-remove-flag')" style="display: none;">
                    <p class="font-xs text-muted mt-2 italic">* Recomendado: 1920x1080 (JPG/WebP)</p>
                </div>
            </div>

            <div class="settings-footer">
                <button type="submit" class="btn-primary settings-save-btn px-4">
                    <i data-lucide="save" class="icon-lucide icon-sm mr-2"></i> Atualizar Configurações Gerais
                </button>
            </div>
        </form>

    <!-- Themes Tab -->
    <?php elseif ($active_tab === 'themes'): ?>
        <form id="form-themes" action="<?php echo SITE_URL ?>/api/admin/settings/save" method="POST" class="ajax-form">
            <input type="hidden" name="csrf_token" value="<?php echo CSRF::generateToken(); ?>">
            <input type="hidden" name="tab" value="themes">
            
            <div class="settings-header">
                <h3 class="settings-title">Personalização Visual</h3>
                <p class="settings-subtitle">Escolha a paleta de cores que melhor define sua marca.</p>
            </div>

            <div class="accordion-container mt-4">
                <!-- System Theme Section -->
                <details class="settings-accordion" open>
                    <summary class="accordion-header">
                        <div class="d-flex align-items-center gap-3">
                            <div class="icon-circle-box-sm bg-primary-glass text-primary">
                                <i data-lucide="layout" class="icon-sm"></i>
                            </div>
                            <div>
                                <h5 class="m-0 fw-800 text-main font-sm">Aparência do Painel Administrativo</h5>
                                <p class="text-muted font-xs m-0">Define as cores de toda a interface interna do sistema.</p>
                            </div>
                        </div>
                        <i data-lucide="chevron-down" class="accordion-chevron"></i>
                    </summary>
                    <div class="accordion-content">
                        <div class="theme-grid">
                            <?php 
                            $themes = ThemeHelper::getAvailableThemes();
                            $current_theme = $settings['system_theme'] ?? 'gold-black';
                            foreach ($themes as $slug => $theme): 
                                $isSelected = ($slug === $current_theme);
                            ?>
                                <div class="selectable-card <?php echo $isSelected ? 'active' : ''; ?>" onclick="toggleSelectableCard(this, 'theme_<?php echo $slug; ?>')">
                                    <input type="radio" name="system_theme" id="theme_<?php echo $slug; ?>" value="<?php echo $slug; ?>" <?php echo $isSelected ? 'checked' : ''; ?> class="hidden">
                                    
                                    <div class="theme-status-icon"><i data-lucide="check"></i></div>
                                    
                                    <div class="theme-preview-box" style="--preview-bg: <?php echo $theme['bg']; ?>;">
                                        <div class="theme-center-dot" style="--preview-accent: <?php echo $theme['color']; ?>;"></div>
                                    </div>
                                    
                                    <span class="theme-name"><?php echo $theme['name']; ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </details>

                <!-- Login Theme Section -->
                <details class="settings-accordion mt-3">
                    <summary class="accordion-header">
                        <div class="d-flex align-items-center gap-3">
                            <div class="icon-circle-box-sm bg-accent-glass text-muted">
                                <i data-lucide="log-in" class="icon-sm"></i>
                            </div>
                            <div>
                                <h5 class="m-0 fw-800 text-main font-sm">Aparência da Tela de Login</h5>
                                <p class="text-muted font-xs m-0">Personalize o tema que seus clientes verão ao acessar.</p>
                            </div>
                        </div>
                        <i data-lucide="chevron-down" class="accordion-chevron"></i>
                    </summary>
                    <div class="accordion-content">
                        <div class="theme-grid">
                            <?php 
                            $current_login_theme = $settings['system_login_theme'] ?? 'gold-black';
                            foreach ($themes as $slug => $theme): 
                                $isSelected = ($slug === $current_login_theme);
                            ?>
                                <div class="selectable-card <?php echo $isSelected ? 'active' : ''; ?>" onclick="toggleSelectableCard(this, 'login_theme_<?php echo $slug; ?>')">
                                    <input type="radio" name="system_login_theme" id="login_theme_<?php echo $slug; ?>" value="<?php echo $slug; ?>" <?php echo $isSelected ? 'checked' : ''; ?> class="hidden">
                                    
                                    <div class="theme-status-icon"><i data-lucide="check"></i></div>
                                    
                                    <div class="theme-preview-box" style="--preview-bg: <?php echo $theme['bg']; ?>;">
                                        <div class="theme-center-dot" style="--preview-accent: <?php echo $theme['color']; ?>;"></div>
                                    </div>
                                    
                                    <span class="theme-name"><?php echo $theme['name']; ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </details>
            </div>

            <div class="settings-footer">
                <button type="submit" class="btn-primary settings-save-btn px-4">
                    <i data-lucide="save" class="icon-lucide icon-sm mr-2"></i> Aplicar Alterações Visuais
                </button>
            </div>
        </form>

    <!-- Security Tab -->
    <?php elseif ($active_tab === 'security'): ?>
        <form id="form-security" action="<?php echo SITE_URL ?>/api/admin/settings/save" method="POST" class="ajax-form">
            <input type="hidden" name="csrf_token" value="<?php echo CSRF::generateToken(); ?>">
            <input type="hidden" name="tab" value="security">
            
            <div class="settings-header">
                <h3 class="settings-title">Governança & Segurança</h3>
                <p class="settings-subtitle">Configure políticas restritivas para proteger o acesso ao sistema.</p>
            </div>

            <div class="form-grid-3 gap-md">
                <div class="floating-group">
                    <input type="number" name="security_max_attempts" value="<?php echo $settings['security_max_attempts'] ?? '5'; ?>" class="form-control" placeholder=" " required>
                    <label class="floating-label">Tentativas de Login</label>
                </div>
                <div class="floating-group">
                    <input type="number" name="security_lockout_time" value="<?php echo $settings['security_lockout_time'] ?? '15'; ?>" class="form-control" placeholder=" " required>
                    <label class="floating-label">Bloqueio (minutos)</label>
                </div>
                <div class="floating-group">
                    <input type="number" name="security_session_timeout" value="<?php echo $settings['security_session_timeout'] ?? '120'; ?>" class="form-control" placeholder=" " required>
                    <label class="floating-label">Inatividade (minutos)</label>
                </div>
                <div class="floating-group">
                    <input type="number" name="security_log_limit" value="<?php echo $settings['security_log_limit'] ?? '500'; ?>" class="form-control" placeholder=" " required min="100">
                    <label class="floating-label">Limite de Logs Globais</label>
                    <small class="text-muted font-xs">O sistema deletará os mais antigos ao atingir este limite.</small>
                </div>
            </div>

            <div class="form-grid-2 gap-md mt-4">
                <div class="switch-container">
                    <div>
                        <span class="switch-label">Sessão Única por Conta</span>
                        <small class="text-muted font-xs">Impedir múltiplos acessos simultâneos</small>
                    </div>
                    <label class="switch">
                        <input type="checkbox" name="security_single_session" value="1" <?php echo ($settings['security_single_session'] ?? '0') === '1' ? 'checked' : ''; ?>>
                        <span class="slider"></span>
                    </label>
                </div>

                <div class="switch-container">
                    <div>
                        <span class="switch-label">Gravação de Logs Globais</span>
                        <small class="text-muted font-xs">Registrar atividades críticas de segurança</small>
                    </div>
                    <label class="switch">
                        <input type="checkbox" name="security_enable_logs" value="1" <?php echo ($settings['security_enable_logs'] ?? '1') === '1' ? 'checked' : ''; ?>>
                        <span class="slider"></span>
                    </label>
                </div>
            </div>

            <div class="settings-footer">
                <button type="submit" class="btn-primary settings-save-btn px-4">
                    <i data-lucide="shield-check" class="icon-lucide icon-sm mr-2"></i> Salvar Políticas de Segurança
                </button>
            </div>
        </form>
    <?php endif; ?>
</div>

<script>
// Force icon recreation on load
document.addEventListener('DOMContentLoaded', () => {
    if(window.lucide) lucide.createIcons();
});

// Settings success handler - Handles dynamic theme switching without reload
document.addEventListener('ajaxSuccess', (e) => {
    const result = e.detail;
    
    // Only proceed if it was a theme update
    const form = document.querySelector('.ajax-form[action*="settings/save"]');
    if (!form) return;

    const formData = new FormData(form);
    const tab = formData.get('tab');
    
    if (tab === 'themes') {
        const newTheme = formData.get('system_theme');
        if (newTheme) {
            // Find theme link in head and update it
            const themeLink = document.querySelector('link[href*="/assets/css/theme/"]');
            if (themeLink) {
                const baseUrl = themeLink.href.split('/assets/css/theme/')[0];
                const version = new Date().getTime(); // Cache bust
                themeLink.href = `${baseUrl}/assets/css/theme/${newTheme}.css?v=${version}`;
            }
        }
    }
});

function previewImage(input, previewId, flagId) {
    if (input.files && input.files[0]) {
        // Clear removal flag if a new file is chosen
        if (flagId) document.getElementById(flagId).value = "0";

        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById(previewId);
            const isBg = previewId === 'preview-bg';
            const btnTitle = isBg ? 'Remover Fundo' : 'Remover Logo';
            const overlayText = isBg ? 'Alterar Fundo' : 'Alterar Logo';

            preview.innerHTML = `
                <img src="${e.target.result}" class="preview-img ${isBg ? 'object-cover' : ''}"> 
                <button type="button" class="btn-remove-image" onclick="removeImage(event, '${previewId}', '${flagId}', '${input.id}')" title="${btnTitle}">
                    <i data-lucide="x"></i>
                </button>
                <div class="upload-overlay"><i data-lucide="upload-cloud"></i> <span>${overlayText}</span></div>
            `;
            if(window.lucide) lucide.createIcons();
        }
        reader.readAsDataURL(input.files[0]);
    }
}

function removeImage(event, previewId, flagId, inputId) {
    event.stopPropagation();
    
    // Set removal flag
    if (flagId) document.getElementById(flagId).value = "1";
    
    // Clear file input
    if (inputId) document.getElementById(inputId).value = "";
    
    // Update UI
    const preview = document.getElementById(previewId);
    const isBg = previewId === 'preview-bg';
    const icon = isBg ? 'monitor' : 'image';
    const text = isBg ? 'Selecionar Wallpaper' : 'Selecionar Imagem';
    const overlayText = isBg ? 'Alterar Fundo' : 'Alterar Logo';

    preview.innerHTML = `
        <div class="upload-empty">
            <i data-lucide="${icon}" class="icon-lucide"></i>
            <span>${text}</span>
        </div>
        <div class="upload-overlay"><i data-lucide="upload-cloud"></i> <span>${overlayText}</span></div>
    `;
    if(window.lucide) lucide.createIcons();
}
</script>
