<?php
/** @var array $settings */
/** @var string $active_tab */
?>


<div class="settings-tab-nav">
    <a href="?tab=general" class="nav-link-tab <?php echo $active_tab === 'general' ? 'active' : ''; ?>">
        <i class="fas fa-cog"></i> Geral
    </a>
    <a href="?tab=themes" class="nav-link-tab <?php echo $active_tab === 'themes' ? 'active' : ''; ?>">
        <i class="fas fa-palette"></i> Temas
    </a>
    <a href="?tab=security" class="nav-link-tab <?php echo $active_tab === 'security' ? 'active' : ''; ?>">
        <i class="fas fa-shield-alt"></i> Segurança
    </a>
</div>

<div class="card settings-main-card">
    <?php if ($active_tab === 'general'): ?>
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="nonce" value="<?php echo $nonces['general']; ?>">
            <div class="settings-header-box">
                <h5><i class="fas fa-cog text-primary"></i> Configurações Gerais</h5>
                <p>Configurações básicas de identidade e comportamento do sistema.</p>
            </div>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">
                <div class="form-group mb-4">
                    <label class="form-label">Nome do Sistema</label>
                    <input type="text" name="system_name" value="<?php echo htmlspecialchars($settings['system_name'] ?? ''); ?>" class="form-control w-100">
                </div>

                <div class="form-group mb-4">
                    <label class="form-label">Logo do Sistema (WebP Recomendado)</label>
                    <div style="display: flex; gap: 15px; align-items: center;">
                        <?php if (!empty($settings['system_logo'])): ?>
                            <img src="<?php echo SITE_URL . $settings['system_logo']; ?>" style="height: 40px; border-radius: 4px; border: 1px solid var(--border);">
                        <?php endif; ?>
                        <input type="file" name="system_logo" class="form-control" accept="image/*">
                    </div>
                </div>

                <div class="form-group mb-4">
                    <label class="form-label">Fundo do Cardápio (Página Pública)</label>
                    <div style="display: flex; gap: 15px; align-items: center;">
                        <?php if (!empty($settings['cardapio_bg'])): ?>
                            <div style="width: 40px; height: 40px; background: url('<?php echo SITE_URL . $settings['cardapio_bg']; ?>') center/cover; border-radius: 4px; border: 1px solid var(--border);"></div>
                        <?php endif; ?>
                        <input type="file" name="cardapio_bg" class="form-control" accept="image/*">
                    </div>
                </div>
            </div>

            <div class="form-group mb-4 mt-2">
                <label class="switch-label" style="display: flex; align-items: center; justify-content: space-between; cursor: pointer;">
                    <div>
                        <h6 class="mb-0">Ativar Logs do Sistema</h6>
                        <small class="text-muted">Registrar erros e atividades no diretório /logs</small>
                    </div>
                    <label class="switch">
                        <input type="checkbox" name="enable_system_logs" value="1" <?php echo ($settings['enable_system_logs'] ?? '0') === '1' ? 'checked' : ''; ?>>
                        <span class="slider"></span>
                    </label>
                </label>
            </div>

            <button type="submit" name="save_general" class="btn-primary">
                <i class="fas fa-save"></i> Salvar Alterações
            </button>
        </form>

    <?php elseif ($active_tab === 'themes'): ?>
        <form method="POST">
            <input type="hidden" name="nonce" value="<?php echo $nonces['theme']; ?>">
            <div class="settings-header-box">
                <h5><i class="fas fa-palette text-primary"></i> Personalização de Tema</h5>
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
                            <div class="theme-check-icon" style="display: <?php echo $isSelected ? 'flex' : 'none'; ?>;">
                                <i class="fas fa-check"></i>
                            </div>
                        </div>
                    </label>
                <?php endforeach; ?>
            </div>

            <button type="submit" name="save_theme" class="btn-primary">
                <i class="fas fa-save"></i> Aplicar Tema Selecionado
            </button>
        </form>

    <?php elseif ($active_tab === 'security'): ?>
        <form method="POST">
            <input type="hidden" name="nonce" value="<?php echo $nonces['security']; ?>">
            <div class="settings-header-box">
                <h5><i class="fas fa-shield-alt text-primary"></i> Segurança e Proteção</h5>
                <p>Configure políticas de retenção de dados e proteção contra acessos indevidos.</p>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 20px; margin-bottom: 30px;">
                <!-- Retenção de Logs -->
                <div class="card p-4" style="background: rgba(var(--primary-rgb), 0.03); border: 1px solid var(--border); border-radius: 15px;">
                    <h6 style="font-weight: 700; margin-bottom: 20px; color: var(--text-main); display: flex; align-items: center; gap: 10px;">
                        <i class="fas fa-history text-primary"></i> Retenção de Histórico
                    </h6>
                    <div class="form-group mb-4">
                        <label class="form-label" style="font-size:13px;">Manter Logs por (Dias)</label>
                        <input type="number" name="security_log_days" value="<?php echo htmlspecialchars($settings['security_log_days'] ?? '30'); ?>" class="form-control" placeholder="Ex: 30">
                        <small class="text-muted" style="font-size: 11px;">Logs mais antigos que este período serão excluídos automaticamente.</small>
                    </div>
                    <div class="form-group">
                        <label class="form-label" style="font-size:13px;">Limite de Registros (Quantidade)</label>
                        <input type="number" name="security_log_limit" value="<?php echo htmlspecialchars($settings['security_log_limit'] ?? '10000'); ?>" class="form-control" placeholder="Ex: 5000">
                        <small class="text-muted" style="font-size: 11px;">Cap máximo de logs no banco de dados.</small>
                    </div>
                </div>

                <!-- Proteção de Login -->
                <div class="card p-4" style="background: rgba(var(--primary-rgb), 0.03); border: 1px solid var(--border); border-radius: 15px;">
                    <h6 style="font-weight: 700; margin-bottom: 20px; color: var(--text-main); display: flex; align-items: center; gap: 10px;">
                        <i class="fas fa-user-shield text-primary"></i> Proteção de Acesso
                    </h6>
                    <div class="form-group mb-4">
                        <label class="form-label" style="font-size:13px;">Máximo de Tentativas Erradas</label>
                        <input type="number" name="security_max_attempts" value="<?php echo htmlspecialchars($settings['security_max_attempts'] ?? '5'); ?>" class="form-control">
                        <small class="text-muted" style="font-size: 11px;">Bloqueia o usuário após atingir este limite.</small>
                    </div>
                    <div class="form-group mb-4">
                        <label class="form-label" style="font-size:13px;">Tempo de Bloqueio (Minutos)</label>
                        <input type="number" name="security_lockout_time" value="<?php echo htmlspecialchars($settings['security_lockout_time'] ?? '15'); ?>" class="form-control">
                    </div>
                    <div class="form-group">
                        <label class="switch-label" style="display: flex; align-items: center; justify-content: space-between; cursor: pointer;">
                            <div>
                                <h6 class="mb-0" style="font-size:13px;">Exigir Senha Forte</h6>
                                <small class="text-muted" style="font-size: 11px;">Mínimo 8 caracteres, números e letras.</small>
                            </div>
                            <label class="switch">
                                <input type="checkbox" name="security_strong_password" value="1" <?php echo ($settings['security_strong_password'] ?? '0') === '1' ? 'checked' : ''; ?>>
                                <span class="slider"></span>
                            </label>
                        </label>
                    </div>
                </div>

                <!-- Sessão e Sessões Ativas -->
                <div class="card p-4" style="background: rgba(var(--primary-rgb), 0.03); border: 1px solid var(--border); border-radius: 15px;">
                    <h6 style="font-weight: 700; margin-bottom: 20px; color: var(--text-main); display: flex; align-items: center; gap: 10px;">
                        <i class="fas fa-clock text-primary"></i> Gerenciamento de Sessão
                    </h6>
                    <div class="form-group mb-4">
                        <label class="form-label" style="font-size:13px;">Tempo de Inatividade (Minutos)</label>
                        <input type="number" name="security_session_timeout" value="<?php echo htmlspecialchars($settings['security_session_timeout'] ?? '60'); ?>" class="form-control">
                        <small class="text-muted" style="font-size: 11px;">Desloga o usuário automaticamente após este tempo.</small>
                    </div>
                    <div class="form-group mb-4">
                        <label class="switch-label" style="display: flex; align-items: center; justify-content: space-between; cursor: pointer;">
                            <div>
                                <h6 class="mb-0" style="font-size:13px;">Sessão Única por Usuário</h6>
                                <small class="text-muted" style="font-size: 11px;">Derruba logins anteriores ao entrar.</small>
                            </div>
                            <label class="switch">
                                <input type="checkbox" name="security_single_session" value="1" <?php echo ($settings['security_single_session'] ?? '0') === '1' ? 'checked' : ''; ?>>
                                <span class="slider"></span>
                            </label>
                        </label>
                    </div>
                    <div class="form-group">
                        <label class="switch-label" style="display: flex; align-items: center; justify-content: space-between; cursor: pointer;">
                            <div>
                                <h6 class="mb-0" style="font-size:13px;">Bloqueio de IP por Erros</h6>
                                <small class="text-muted" style="font-size: 11px;">Bloqueia o IP globalmente no servidor.</small>
                            </div>
                            <label class="switch">
                                <input type="checkbox" name="security_ip_lockout" value="1" <?php echo ($settings['security_ip_lockout'] ?? '0') === '1' ? 'checked' : ''; ?>>
                                <span class="slider"></span>
                            </label>
                        </label>
                    </div>
                </div>
            </div>

            <button type="submit" name="save_security" class="btn-primary">
                <i class="fas fa-save"></i> Salvar Configurações de Segurança
            </button>
        </form>
    <?php endif; ?>
</div>


