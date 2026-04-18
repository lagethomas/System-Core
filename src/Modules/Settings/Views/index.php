<?php declare(strict_types=1);
/** @var array $settings */
/** @var string $active_tab */

require_once dirname(dirname(dirname(dirname(__DIR__)))) . '/includes/helpers/CSRF.php';
require_once dirname(dirname(dirname(dirname(__DIR__)))) . '/includes/helpers/ThemeHelper.php';
?>

<div class="settings-page-header mb-5">
    <div class="flex items-center gap-4">
        <div class="header-icon-box">
            <i data-lucide="settings"></i>
        </div>
        <div>
            <h2 class="m-0">Configurações do Sistema</h2>
            <p class="text-muted m-0">Gerencie as preferências globais, aparência e segurança da plataforma.</p>
        </div>
    </div>
</div>

<div class="tab-navigation mb-4">
    <a href="?tab=general" class="nav-link-tab <?php echo $active_tab === 'general' ? 'active' : ''; ?>">
        <i data-lucide="settings" class="icon-sm"></i> Geral
    </a>
    <a href="?tab=themes" class="nav-link-tab <?php echo $active_tab === 'themes' ? 'active' : ''; ?>">
        <i data-lucide="palette" class="icon-sm"></i> Temas
    </a>
    <a href="?tab=security" class="nav-link-tab <?php echo $active_tab === 'security' ? 'active' : ''; ?>">
        <i data-lucide="shield" class="icon-sm"></i> Segurança
    </a>
</div>

<div class="card card-settings p-5">
    <!-- General Settings Tab -->
    <?php if ($active_tab === 'general'): ?>
        <form id="form-general" action="<?php echo SITE_URL ?>/api/admin/settings/save" method="POST" class="ajax-form" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?php echo CSRF::generateToken(); ?>">
            <input type="hidden" name="tab" value="general">
            
            <h3 class="fw-700 text-main mb-1">Configurações Gerais</h3>
            <p class="text-muted small mb-5">Defina a identidade da sua plataforma e o comportamento do sistema.</p>
            
            <div class="form-grid-2 gap-4">
                <div class="floating-group">
                    <input type="text" name="system_name" value="<?php echo htmlspecialchars($settings['system_name'] ?? ''); ?>" class="form-control" placeholder=" " required>
                    <label class="floating-label">Nome da Plataforma</label>
                </div>

                <div class="switch-container">
                    <div>
                        <span class="switch-label fw-700">Ativar Logs do Sistema</span>
                        <p class="text-muted small m-0">Registra erros técnicos para auditoria</p>
                    </div>
                    <label class="switch">
                        <input type="checkbox" name="enable_system_logs" value="1" <?php echo ($settings['enable_system_logs'] ?? '0') === '1' ? 'checked' : ''; ?>>
                        <span class="slider"></span>
                    </label>
                </div>

                <div class="floating-group">
                    <input type="number" name="items_per_page" value="<?php echo htmlspecialchars($settings['items_per_page'] ?? '25'); ?>" class="form-control" placeholder=" " required min="5" max="100">
                    <label class="floating-label">Itens por Página (Tabelas)</label>
                </div>
            </div>

            <div class="form-grid-2 mt-5 gap-4">
                <div>
                    <label class="small fw-800 text-muted text-uppercase mb-3 d-block">Logotipo da Plataforma</label>
                    <input type="hidden" name="remove_logo" id="logo-remove-flag" value="0">
                    <div id="preview-logo" class="upload-selector" onclick="document.getElementById('logo-upload').click()">
                        <?php if (!empty($settings['system_logo'])): ?>
                            <img src="<?php echo SITE_URL; ?>/uploads/logos/<?php echo $settings['system_logo']; ?>" class="preview-img" id="img-logo">
                            <button type="button" class="btn-remove-image" onclick="removeImage(event, 'preview-logo', 'logo-remove-flag', 'logo-upload')" title="Remover Logo">
                                <i data-lucide="x"></i>
                            </button>
                        <?php else: ?>
                            <div class="upload-empty">
                                <i data-lucide="image"></i>
                                <span>Selecionar Imagem</span>
                            </div>
                        <?php endif; ?>
                        <div class="upload-overlay"><i data-lucide="upload-cloud"></i> <span>Alterar Logo</span></div>
                    </div>
                    <input type="file" id="logo-upload" name="system_logo" accept="image/*" onchange="previewImage(this, 'preview-logo', 'logo-remove-flag')" style="display: none;">
                </div>

                <div>
                    <label class="small fw-800 text-muted text-uppercase mb-3 d-block">Wallpaper da Tela de Login</label>
                    <input type="hidden" name="remove_login_bg" id="bg-remove-flag" value="0">
                    <div id="preview-bg" class="upload-selector" onclick="document.getElementById('bg-upload').click()">
                        <?php if (!empty($settings['login_background'])): ?>
                            <img src="<?php echo SITE_URL; ?>/uploads/backgrounds/<?php echo $settings['login_background']; ?>" class="preview-img object-cover" id="img-bg">
                            <button type="button" class="btn-remove-image" onclick="removeImage(event, 'preview-bg', 'bg-remove-flag', 'bg-upload')" title="Remover Fundo">
                                <i data-lucide="x"></i>
                            </button>
                        <?php else: ?>
                            <div class="upload-empty">
                                <i data-lucide="monitor"></i>
                                <span>Selecionar Wallpaper</span>
                            </div>
                        <?php endif; ?>
                        <div class="upload-overlay"><i data-lucide="upload-cloud"></i> <span>Alterar Fundo</span></div>
                    </div>
                    <input type="file" id="bg-upload" name="login_background" accept="image/*" onchange="previewImage(this, 'preview-bg', 'bg-remove-flag')" style="display: none;">
                </div>
            </div>

            <div class="pt-5 mt-5 border-top flex justify-end">
                <button type="submit" class="btn-primary" style="padding: 15px 40px; border-radius: 12px; font-weight: 800;">
                    <i data-lucide="save" class="icon-sm mr-2"></i> Atualizar Configurações Gerais
                </button>
            </div>
        </form>

    <!-- Themes Tab -->
    <?php elseif ($active_tab === 'themes'): ?>
        <form id="form-themes" action="<?php echo SITE_URL ?>/api/admin/settings/save" method="POST" class="ajax-form">
            <input type="hidden" name="csrf_token" value="<?php echo CSRF::generateToken(); ?>">
            <input type="hidden" name="tab" value="themes">
            
            <h3 class="fw-700 text-main mb-1">Personalização Visual</h3>
            <p class="text-muted small mb-5">Escolha a paleta de cores que melhor define sua marca.</p>

            <div class="accordion-container mt-4">
                <!-- System Theme Section -->
                <details class="settings-accordion" open>
                    <summary class="accordion-header">
                        <div class="flex items-center gap-3">
                            <div class="header-icon-box" style="width: 40px; height: 40px;">
                                <i data-lucide="layout" class="icon-sm"></i>
                            </div>
                            <div>
                                <h5 class="m-0 fw-800 text-main">Aparência do Painel Administrativo</h5>
                                <p class="text-muted small m-0">Define as cores de toda a interface interna do sistema.</p>
                            </div>
                        </div>
                        <i data-lucide="chevron-down" class="accordion-chevron"></i>
                    </summary>
                    <div class="accordion-content">
                        <div class="theme-grid mt-4">
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
                        <div class="flex items-center gap-3">
                            <div class="header-icon-box" style="width: 40px; height: 40px;">
                                <i data-lucide="log-in" class="icon-sm"></i>
                            </div>
                            <div>
                                <h5 class="m-0 fw-800 text-main">Aparência da Tela de Login</h5>
                                <p class="text-muted small m-0">Personalize o tema que seus clientes verão ao acessar.</p>
                            </div>
                        </div>
                        <i data-lucide="chevron-down" class="accordion-chevron"></i>
                    </summary>
                    <div class="accordion-content">
                        <div class="theme-grid mt-4">
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

            <div class="pt-5 mt-5 border-top flex justify-end">
                <button type="submit" class="btn-primary" style="padding: 15px 40px; border-radius: 12px; font-weight: 800;">
                    <i data-lucide="save" class="icon-sm mr-2"></i> Aplicar Alterações Visuais
                </button>
            </div>
        </form>

    <!-- Security Tab -->
    <?php elseif ($active_tab === 'security'): ?>
        <form id="form-security" action="<?php echo SITE_URL ?>/api/admin/settings/save" method="POST" class="ajax-form">
            <input type="hidden" name="csrf_token" value="<?php echo CSRF::generateToken(); ?>">
            <input type="hidden" name="tab" value="security">
            
            <h3 class="fw-700 text-main mb-1">Governança & Segurança</h3>
            <p class="text-muted small mb-5">Configure políticas restritivas para proteger o acesso ao sistema.</p>

            <div class="form-grid-3 gap-4">
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
                </div>
            </div>

            <div class="form-grid-2 gap-4 mt-5">
                <div class="switch-container">
                    <div>
                        <span class="switch-label fw-700">Sessão Única por Conta</span>
                        <p class="text-muted small m-0">Impedir múltiplos acessos simultâneos</p>
                    </div>
                    <label class="switch">
                        <input type="checkbox" name="security_single_session" value="1" <?php echo ($settings['security_single_session'] ?? '0') === '1' ? 'checked' : ''; ?>>
                        <span class="slider"></span>
                    </label>
                </div>

                <div class="switch-container">
                    <div>
                        <span class="switch-label fw-700">Gravação de Logs Globais</span>
                        <p class="text-muted small m-0">Registrar atividades críticas de segurança</p>
                    </div>
                    <label class="switch">
                        <input type="checkbox" name="security_enable_logs" value="1" <?php echo ($settings['security_enable_logs'] ?? '1') === '1' ? 'checked' : ''; ?>>
                        <span class="slider"></span>
                    </label>
                </div>
            </div>

            <div class="pt-5 mt-5 border-top flex justify-end">
                <button type="submit" class="btn-primary" style="padding: 15px 40px; border-radius: 12px; font-weight: 800;">
                    <i data-lucide="shield-check" class="icon-sm mr-2"></i> Salvar Políticas de Segurança
                </button>
            </div>
        </form>
    <?php endif; ?>
</div>

<script src="<?php echo SITE_URL; ?>/assets/js/modules/settings.js"></script>
