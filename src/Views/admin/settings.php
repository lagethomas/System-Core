<?php
/** @var array $settings */
/** @var string $active_tab */
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
    <?php if ($active_tab === 'general'): ?>
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?php echo CSRF::generateToken(); ?>">
            
            <div class="settings-header-box">
                <h5><i data-lucide="settings" class="text-primary"></i> Configurações Gerais</h5>
                <p>Gerencie a identidade básica e funcionamento do sistema.</p>
            </div>
            
            <div class="form-grid-5 mb-4">
                <!-- Nome Card -->
                <div class="upload-box-wrapper p-3">
                    <label class="upload-label"><i data-lucide="info"></i> Nome</label>
                    <div class="form-group mt-2">
                        <input type="text" name="system_name" value="<?php echo htmlspecialchars($settings['system_name'] ?? ''); ?>" class="form-control" placeholder="ex: SaaSFlow">
                    </div>
                    <small class="text-muted d-block mt-auto pt-2">Identificação principal do sistema.</small>
                </div>

                <!-- Logs Card -->
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

                <!-- Logo Card -->
                <div class="upload-box-wrapper p-3">
                    <label class="upload-label"><i data-lucide="image"></i> Logo</label>
                    <div class="upload-clickable-container">
                        <div id="preview-logo" class="upload-preview-box upload-clickable" onclick="document.getElementById('logo-upload').click()" title="Clique para selecionar imagem">
                            <?php if (!empty($settings['system_logo'])): ?>
                                <img src="<?php echo SITE_URL; ?>/uploads/logos/<?php echo $settings['system_logo']; ?>" alt="Logo" class="logo-img">
                            <?php else: ?>
                                <i data-lucide="upload" class="upload-placeholder-icon"></i>
                                <span class="upload-placeholder-text">Clique para enviar</span>
                            <?php endif; ?>
                            <div class="upload-hover-overlay">
                                <i data-lucide="upload"></i>
                                <span>Alterar</span>
                            </div>
                        </div>
                        <input type="file" id="logo-upload" name="system_logo" accept="image/*" onchange="previewImage(this, 'preview-logo', 'logo-img')" style="display: none;">
                        <?php if (!empty($settings['system_logo'])): ?>
                            <button type="submit" name="remove_logo" value="1" class="btn-danger upload-delete-overlay" title="Remover logo" onclick="event.stopPropagation()">
                                <i data-lucide="trash-2"></i>
                            </button>
                        <?php endif; ?>
                    </div>
                    <small class="text-muted d-block mt-auto pt-2">Imagem exibida no menu lateral.</small>
                </div>

                <!-- Background Card -->
                <div class="upload-box-wrapper p-3">
                    <label class="upload-label"><i data-lucide="monitor"></i> Background</label>
                    <div class="upload-clickable-container">
                        <div id="preview-bg" class="upload-preview-box upload-clickable" onclick="document.getElementById('bg-upload').click()" title="Clique para selecionar imagem">
                            <?php if (!empty($settings['login_background'])): ?>
                                <img src="<?php echo SITE_URL; ?>/uploads/backgrounds/<?php echo $settings['login_background']; ?>" alt="BG" class="bg-img">
                            <?php else: ?>
                                <i data-lucide="upload" class="upload-placeholder-icon"></i>
                                <span class="upload-placeholder-text">Clique para enviar</span>
                            <?php endif; ?>
                            <div class="upload-hover-overlay">
                                <i data-lucide="upload"></i>
                                <span>Alterar</span>
                            </div>
                        </div>
                        <input type="file" id="bg-upload" name="login_background" accept="image/*" onchange="previewImage(this, 'preview-bg', 'bg-img')" style="display: none;">
                        <?php if (!empty($settings['login_background'])): ?>
                            <button type="submit" name="remove_login_bg" value="1" class="btn-danger upload-delete-overlay" title="Remover background" onclick="event.stopPropagation()">
                                <i data-lucide="trash-2"></i>
                            </button>
                        <?php endif; ?>
                    </div>
                    <small class="text-muted d-block mt-auto pt-2">Imagem de fundo da tela de login.</small>
                </div>
            </div>

            <div class="settings-footer-section">
                <button type="submit" name="save_general" class="btn-primary settings-save-btn">
                    <i data-lucide="save"></i> Salvar Agora
                </button>
            </div>
        </form>

    <?php elseif ($active_tab === 'themes'): ?>
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo CSRF::generateToken(); ?>">
            <div class="settings-header-box">
                <h5><i data-lucide="palette" class="text-primary"></i> Personalização de Tema</h5>
                <p>Selecione a identidade visual que será aplicada a todos os usuários do sistema.</p>
            </div>

            <div class="theme-grid">
                <?php 
                $themes = ThemeHelper::getAvailableThemes();
                $current_theme = $settings['system_theme'] ?? 'gold-black';
                
                foreach ($themes as $slug => $theme): 
                    $isSelected = ($slug === $current_theme);
                ?>
                    <label class="theme-card-label">
                        <input type="radio" name="system_theme" value="<?php echo $slug; ?>" <?php echo $isSelected ? 'checked' : ''; ?> style="display: none;">
                        <div class="theme-card-ui">
                            <div class="theme-card-preview" style="background: <?php echo $theme['bg']; ?>;">
                                <div class="theme-card-accent" style="background: <?php echo $theme['color']; ?>; box-shadow: 0 0 15px <?php echo $theme['color']; ?>88;"></div>
                                <div class="theme-card-subaccent" style="background: <?php echo ($theme['bg'] == '#ffffff' || $theme['bg'] == 'white') ? '#eee' : 'rgba(255,255,255,0.1)'; ?>;"></div>
                            </div>
                            <div class="text-center">
                                <span class="theme-card-name"><?php echo $theme['name']; ?></span>
                            </div>
                            <div class="theme-check-icon">
                                <i data-lucide="check"></i>
                            </div>
                        </div>
                    </label>
                <?php endforeach; ?>
            </div>

            <div class="settings-header-box mt-5">
                <h5><i data-lucide="monitor" class="text-primary"></i> Tema Global da Tela de Login</h5>
                <p>Escolha o tema padrão que será aplicado à página de login global e empresas sem tema próprio.</p>
            </div>

            <div class="theme-grid">
                <?php 
                $current_login_theme = $settings['system_login_theme'] ?? 'gold-black';
                
                foreach ($themes as $slug => $theme): 
                    $isSelected = ($slug === $current_login_theme);
                ?>
                    <label class="theme-card-label">
                        <input type="radio" name="system_login_theme" value="<?php echo $slug; ?>" <?php echo $isSelected ? 'checked' : ''; ?> style="display: none;">
                        <div class="theme-card-ui">
                            <div class="theme-card-preview" style="background: <?php echo $theme['bg']; ?>;">
                                <div class="theme-card-accent" style="background: <?php echo $theme['color']; ?>; box-shadow: 0 0 15px <?php echo $theme['color']; ?>88;"></div>
                            </div>
                            <div class="text-center">
                                <span class="theme-card-name"><?php echo $theme['name']; ?></span>
                            </div>
                            <div class="theme-check-icon">
                                <i data-lucide="check"></i>
                            </div>
                        </div>
                    </label>
                <?php endforeach; ?>
            </div>

            <div class="settings-footer-section">
                <button type="submit" name="save_theme" class="btn-primary settings-save-btn">
                    <i data-lucide="save"></i> Aplicar Tema
                </button>
            </div>

    <?php elseif ($active_tab === 'security'): ?>
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo CSRF::generateToken(); ?>">
            <div class="settings-header-box">
                <h5><i data-lucide="shield" class="text-primary"></i> Segurança do Sistema</h5>
                <p>Gerencie autenticação, sessões e logs.</p>
            </div>

            <div class="form-grid-5">
                <!-- Max Attempts -->
                <div class="upload-box-wrapper p-3">
                    <label class="upload-label"><i data-lucide="user-x"></i> Tentativas</label>
                    <div class="form-group mt-2">
                        <input type="number" name="security_max_attempts" value="<?php echo $settings['security_max_attempts'] ?? '5'; ?>" class="form-control p-2">
                    </div>
                    <small class="text-muted d-block mt-auto pt-2">Limite de erros antes do bloqueio.</small>
                </div>

                <!-- Lockout -->
                <div class="upload-box-wrapper p-3">
                    <label class="upload-label"><i data-lucide="hourglass"></i> Bloqueio</label>
                    <div class="form-group mt-2">
                        <input type="number" name="security_lockout_time" value="<?php echo $settings['security_lockout_time'] ?? '15'; ?>" class="form-control p-2">
                    </div>
                    <small class="text-muted d-block mt-auto pt-2">Tempo de espera (minutos) após o bloqueio.</small>
                </div>

                <!-- Timeout -->
                <div class="upload-box-wrapper p-3">
                    <label class="upload-label"><i data-lucide="clock"></i> Inatividade</label>
                    <div class="form-group mt-2">
                        <input type="number" name="security_session_timeout" value="<?php echo $settings['security_session_timeout'] ?? '120'; ?>" class="form-control p-2">
                    </div>
                    <small class="text-muted d-block mt-auto pt-2">Tempo em <b>minutos</b> (ex: 120 = 2h).</small>
                </div>

                <!-- Single Session -->
                <div class="upload-box-wrapper p-3">
                    <label class="upload-label"><i data-lucide="log-in"></i> Sessão</label>
                    <div class="form-group mt-2">
                        <label class="switch-label d-flex align-items-center justify-content-between cursor-pointer p-0">
                            <span class="fs-11 opacity-08">Sessão Única</span>
                            <label class="switch scale-08 mr-n5">
                                <input type="checkbox" name="security_single_session" value="1" <?php echo ($settings['security_single_session'] ?? '0') === '1' ? 'checked' : ''; ?>>
                                <span class="slider"></span>
                            </label>
                        </label>
                    </div>
                    <small class="text-muted d-block mt-auto pt-2">Derruba acessos simultâneos por conta.</small>
                </div>

                <!-- IP Lockout -->
                <div class="upload-box-wrapper p-3">
                    <label class="upload-label"><i data-lucide="ban"></i> IP Block</label>
                    <div class="form-group mt-2">
                        <label class="switch-label d-flex align-items-center justify-content-between cursor-pointer p-0">
                            <span class="fs-11 opacity-08">Bloqueio por IP</span>
                            <label class="switch scale-08 mr-n5">
                                <input type="checkbox" name="security_ip_lockout" value="1" <?php echo ($settings['security_ip_lockout'] ?? '0') === '1' ? 'checked' : ''; ?>>
                                <span class="slider"></span>
                            </label>
                        </label>
                    </div>
                    <small class="text-muted d-block mt-auto pt-2">Bloqueia o endereço IP do invasor.</small>
                </div>

                <!-- Strong Password -->
                <div class="upload-box-wrapper p-3">
                    <label class="upload-label"><i data-lucide="key"></i> Senhas</label>
                    <div class="form-group mt-2">
                        <label class="switch-label d-flex align-items-center justify-content-between cursor-pointer p-0">
                            <span class="fs-11 opacity-08">Senhas Fortes</span>
                            <label class="switch scale-08 mr-n5">
                                <input type="checkbox" name="security_strong_password" value="1" <?php echo ($settings['security_strong_password'] ?? '0') === '1' ? 'checked' : ''; ?>>
                                <span class="slider"></span>
                            </label>
                        </label>
                    </div>
                    <small class="text-muted d-block mt-auto pt-2">Exige letras, números e símbolos.</small>
                </div>

                <!-- Log Days -->
                <div class="upload-box-wrapper p-3">
                    <label class="upload-label"><i data-lucide="calendar"></i> Retenção</label>
                    <div class="form-group mt-2">
                        <input type="number" name="security_log_days" value="<?php echo $settings['security_log_days'] ?? '30'; ?>" class="form-control p-2">
                    </div>
                    <small class="text-muted d-block mt-auto pt-2">Dias que os logs ficam guardados.</small>
                </div>

                <!-- Log Limit -->
                <div class="upload-box-wrapper p-3">
                    <label class="upload-label"><i data-lucide="list-ordered"></i> Limite Logs</label>
                    <div class="form-group mt-2">
                        <input type="number" name="security_log_limit" value="<?php echo $settings['security_log_limit'] ?? '10000'; ?>" class="form-control p-2">
                    </div>
                    <small class="text-muted d-block mt-auto pt-2">Volume total de registros permitidos.</small>
                </div>

                <!-- Blocked IPs Management (Rule 39) -->
                <div class="upload-box-wrapper p-3" style="grid-column: span 2;">
                    <label class="upload-label"><i data-lucide="shield"></i> Gestão de IPs Bloqueados</label>
                    <div class="form-group mt-2">
                        <textarea name="security_blocked_ips" class="form-control p-2" rows="3" placeholder="Insira um IP por linha..."><?php
                            try {
                                $pdo = \DB::getInstance();
                                $blocked = $pdo->query("SELECT ip_address FROM cp_blocked_ips");
                                if ($blocked) {
                                    echo implode("\n", $blocked->fetchAll(PDO::FETCH_COLUMN));
                                }
                            } catch (\PDOException $e) {
                                // Tabela pode não existir ainda se a migração não foi rodada
                            }
                        ?></textarea>
                    </div>
                    <small class="text-muted d-block mt-auto pt-2">IPs listados aqui terão acesso negado permanentemente.</small>
                </div>
            </div>

            <div class="settings-footer-section">
                <button type="submit" name="save_security" class="btn-primary settings-save-btn">
                    <i data-lucide="save"></i> Salvar Configurações
                </button>
            </div>
        </form>
    <?php endif; ?>
</div>

<script>
/**
 * Visual Preview for image uploads
 */
function previewImage(input, previewId, imgClass) {
    const preview = document.getElementById(previewId);
    if (!preview) return;

    if (input.files && input.files[0]) {
        const reader = new FileReader();

        reader.onload = function(e) {
            // Clear placeholder icons and text, keep overlay
            const placeholderIcon = preview.querySelector('.upload-placeholder-icon');
            const placeholderText = preview.querySelector('.upload-placeholder-text');
            if (placeholderIcon) placeholderIcon.remove();
            if (placeholderText) placeholderText.remove();

            // Clear or update existing img
            let img = preview.querySelector('img');
            if (!img) {
                img = document.createElement('img');
                img.className = imgClass;
                // Insert before the overlay
                const overlay = preview.querySelector('.upload-hover-overlay');
                if (overlay) {
                    preview.insertBefore(img, overlay);
                } else {
                    preview.appendChild(img);
                }
            }
            img.src = e.target.result;
            
            // Ensure hover overlay exists
            let overlay = preview.querySelector('.upload-hover-overlay');
            if (!overlay) {
                overlay = document.createElement('div');
                overlay.className = 'upload-hover-overlay';
                overlay.innerHTML = '<i data-lucide="upload"></i><span>Alterar</span>';
                preview.appendChild(overlay);
                if (window.lucide) lucide.createIcons();
            }
            
            // Visual feedback
            preview.style.borderColor = 'var(--primary)';
            preview.classList.add('pulse-preview');
            
            // Show a tiny toast confirming selection
            if(window.UI && window.UI.showToast) {
                UI.showToast('Imagem selecionada!', 'info');
            }
        }

        reader.readAsDataURL(input.files[0]);
    }
}
</script>



<script>
/**
 * Highlight and scroll to a specific setting if the 'highlight' parameter is present in the URL.
 */
document.addEventListener('DOMContentLoaded', () => {
    const urlParams = new URLSearchParams(window.location.search);
    const highlightKey = urlParams.get('highlight');
    
    if (highlightKey) {
        // Try to find the element by name attribute (common for inputs in the settings)
        const targetElement = document.querySelector(`[name="${highlightKey}"]`);
        
        if (targetElement) {
            // Find the parent wrapper to highlight
            const wrapper = targetElement.closest('.upload-box-wrapper') || targetElement.parentElement;
            
            // Scroll to it
            wrapper.scrollIntoView({ behavior: 'smooth', block: 'center' });
            
            // Add highlight animation/style
            wrapper.style.transition = 'all 0.5s ease';
            wrapper.style.boxShadow = '0 0 20px rgba(var(--primary-rgb), 0.5)';
            wrapper.style.borderColor = 'var(--primary)';
            wrapper.style.transform = 'scale(1.02)';
            
            // Remove highlight after a few seconds
            setTimeout(() => {
                wrapper.style.boxShadow = '';
                wrapper.style.transform = '';
            }, 3000);
        }
    }
});
</script>


