<?php declare(strict_types=1);
/** @var array $company */
/** @var string $nonce */
/** @var string $active_tab */
/** @var array $themes */

$current_theme = $company['theme'] ?? 'gold-black';
?>

<div class="settings-page-header">
    <div class="flex items-center gap-4">
        <div class="header-icon-box md">
            <i data-lucide="building" class="icon-md"></i>
        </div>
        <div>
            <h2 class="m-0">Minha Unidade</h2>
            <p class="text-muted m-0">Gerencie a identidade visual e informações da sua empresa.</p>
        </div>
    </div>
</div>

<div class="tab-navigation mb-6">
    <a href="?tab=general" class="nav-link-tab <?php echo ($active_tab === 'general' || empty($active_tab)) ? 'active' : ''; ?>">
        <i data-lucide="settings" class="icon-sm"></i> Geral
    </a>
    <a href="?tab=themes" class="nav-link-tab <?php echo $active_tab === 'themes' ? 'active' : ''; ?>">
        <i data-lucide="palette" class="icon-sm"></i> Temas
    </a>
</div>

<div class="card p-5">
    <form id="company-settings-form" action="<?php echo SITE_URL; ?>/api/company-settings/save" method="POST" class="ajax-form" enctype="multipart/form-data">
        <input type="hidden" name="nonce" value="<?php echo htmlspecialchars($nonce); ?>">
        <input type="hidden" name="tab" value="<?php echo $active_tab ?: 'general'; ?>">
        
        <?php if ($active_tab === 'general' || empty($active_tab)): ?>
            <div class="flex items-center gap-3 mb-6">
                <div class="header-icon-box md">
                    <i data-lucide="settings" class="icon-sm"></i>
                </div>
                <div>
                    <h3 class="text-main m-0 fw-800">Configurações Gerais</h3>
                    <p class="text-muted small m-0">Defina os dados fundamentais da sua empresa.</p>
                </div>
            </div>
            
            <div class="form-grid-3 gap-4 mb-4">
                <div class="floating-group">
                    <input type="text" name="name" value="<?php echo htmlspecialchars($company['name'] ?? ''); ?>" class="form-control" placeholder=" " required>
                    <label class="floating-label">Nome da Empresa</label>
                </div>

                <div class="floating-group">
                    <input type="text" name="document" value="<?php echo htmlspecialchars($company['document'] ?? ''); ?>" class="form-control mask-cnpj" placeholder=" ">
                    <label class="floating-label">CNPJ / CPF</label>
                </div>

                <div class="floating-group">
                    <input type="email" name="email" value="<?php echo htmlspecialchars($company['email'] ?? ''); ?>" class="form-control" placeholder=" ">
                    <label class="floating-label">E-mail de Contato</label>
                </div>
            </div>

            <div class="form-grid-2 gap-4 mb-6">
                <div class="floating-group">
                    <input type="text" name="phone" value="<?php echo htmlspecialchars($company['phone'] ?? ''); ?>" class="form-control mask-phone" placeholder=" ">
                    <label class="floating-label">WhatsApp</label>
                </div>

                <div class="floating-group">
                    <input type="text" name="custom_domain" value="<?php echo htmlspecialchars($company['custom_domain'] ?? ''); ?>" class="form-control" placeholder=" ">
                    <label class="floating-label">Domínio Personalizado</label>
                </div>
            </div>

            <div class="form-grid-2 gap-4 mb-6">
                <!-- Logo Card -->
                <div>
                    <label class="settings-upload-label">Logotipo</label>
                    <div id="preview-logo" class="upload-selector" onclick="document.getElementById('logo-upload').click()">
                        <?php if (!empty($company['logo'])): ?>
                            <img src="<?php echo SITE_URL . $company['logo']; ?>" alt="Logo" class="preview-img">
                        <?php else: ?>
                            <div class="upload-empty">
                                <i data-lucide="image"></i>
                                <span>Selecionar Logo</span>
                            </div>
                        <?php endif; ?>
                        <div class="upload-overlay"><i data-lucide="upload-cloud"></i> <span>Alterar Logo</span></div>
                    </div>
                    <input type="file" id="logo-upload" name="logo" accept="image/*" class="d-none" onchange="previewImage(this, 'preview-logo', 'logo-img')">
                    <small class="text-muted d-block mt-2">Imagem usada em orçamentos e no menu lateral.</small>
                </div>

                <!-- Background Card -->
                <div>
                    <label class="settings-upload-label">Fundo da Tela de Login</label>
                    <div id="preview-bg" class="upload-selector" onclick="document.getElementById('bg-upload').click()">
                        <?php if (!empty($company['background_image'])): ?>
                            <img src="<?php echo SITE_URL . $company['background_image']; ?>" alt="BG" class="preview-img object-cover">
                        <?php else: ?>
                            <div class="upload-empty">
                                <i data-lucide="monitor"></i>
                                <span>Selecionar Wallpaper</span>
                            </div>
                        <?php endif; ?>
                        <div class="upload-overlay"><i data-lucide="upload-cloud"></i> <span>Alterar Fundo</span></div>
                    </div>
                    <input type="file" id="bg-upload" name="background_image" accept="image/*" class="d-none" onchange="previewImage(this, 'preview-bg', 'bg-img')">
                    <small class="text-muted d-block mt-2">Imagem de fundo na página de login.</small>
                </div>
            </div>

            <div class="pt-5 border-top flex justify-end">
                <button type="submit" class="btn-primary btn-premium">
                    <i data-lucide="save" class="icon-sm mr-2"></i> Salvar Configurações
                </button>
            </div>

        <?php elseif ($active_tab === 'themes'): ?>
            <div class="flex items-center gap-3 mb-6">
                <div class="header-icon-box md">
                    <i data-lucide="palette" class="icon-sm"></i>
                </div>
                <div>
                    <h3 class="text-main m-0 fw-800">Temas e Cores</h3>
                    <p class="text-muted small m-0">Escolha as cores predominantes do seu sistema.</p>
                </div>
            </div>
            
            <div class="theme-grid">
                <?php foreach ($themes as $slug => $theme): 
                    $isSelected = ($slug === $current_theme);
                ?>
                    <div class="selectable-card <?php echo $isSelected ? 'active' : ''; ?>" onclick="toggleSelectableCard(this, 'theme_<?php echo $slug; ?>')">
                        <input type="radio" name="theme" id="theme_<?php echo $slug; ?>" value="<?php echo $slug; ?>" <?php echo $isSelected ? 'checked' : ''; ?> class="d-none">
                        <div class="theme-preview-box" style="--preview-bg: <?php echo $theme['bg']; ?>;">
                            <div class="theme-center-dot" style="--preview-accent: <?php echo $theme['color']; ?>;"></div>
                        </div>
                        <span class="theme-name"><?php echo $theme['name']; ?></span>
                        <div class="theme-status-icon"><i data-lucide="check"></i></div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="flex items-center gap-3 mt-8 mb-6">
                <div class="header-icon-box md">
                    <i data-lucide="log-in" class="icon-sm"></i>
                </div>
                <div>
                    <h3 class="text-main m-0 fw-800">Tema da Tela de Login</h3>
                    <p class="text-muted small m-0">Paleta aplicada à sua página pública.</p>
                </div>
            </div>

            <div class="theme-grid">
                <?php 
                $current_login_theme = $company['login_theme'] ?? 'gold-black';
                foreach ($themes as $slug => $theme): 
                    $isSelected = ($slug === $current_login_theme);
                ?>
                    <div class="selectable-card <?php echo $isSelected ? 'active' : ''; ?>" onclick="toggleSelectableCard(this, 'login_theme_<?php echo $slug; ?>')">
                        <input type="radio" name="login_theme" id="login_theme_<?php echo $slug; ?>" value="<?php echo $slug; ?>" <?php echo $isSelected ? 'checked' : ''; ?> class="d-none">
                        <div class="theme-preview-box" style="--preview-bg: <?php echo $theme['bg']; ?>;">
                            <div class="theme-center-dot" style="--preview-accent: <?php echo $theme['color']; ?>;"></div>
                        </div>
                        <span class="theme-name"><?php echo $theme['name']; ?></span>
                        <div class="theme-status-icon"><i data-lucide="check"></i></div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="pt-5 mt-6 border-top flex justify-end">
                <button type="submit" class="btn-primary btn-premium">
                    <i data-lucide="palette" class="icon-sm mr-2"></i> Aplicar Temas
                </button>
            </div>
        <?php endif; ?>
    </form>
</div>

<script src="<?php echo SITE_URL; ?>/assets/js/modules/company-settings.js"></script>
