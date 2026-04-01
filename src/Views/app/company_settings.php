<?php
/** @var array $company */
/** @var string $nonce */
/** @var string $active_tab */
/** @var array $themes */

$v = time();
$current_theme = $company['theme'] ?? 'gold-black';
?>

<link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/modules/settings.css?v=<?php echo $v; ?>">

<div class="settings-tab-nav">
    <a href="?tab=general" class="nav-link-tab <?php echo ($active_tab === 'general' || empty($active_tab)) ? 'active' : ''; ?>">
        <i data-lucide="settings"></i> Geral
    </a>
    <a href="?tab=themes" class="nav-link-tab <?php echo $active_tab === 'themes' ? 'active' : ''; ?>">
        <i data-lucide="palette"></i> Temas
    </a>
</div>

<div class="card settings-main-card">
    <form id="company-settings-form" enctype="multipart/form-data" onsubmit="saveCompanySettings(event)">
        <input type="hidden" name="nonce" value="<?php echo htmlspecialchars($nonce); ?>">
        
        <?php if ($active_tab === 'general' || empty($active_tab)): ?>
            <div class="settings-header-box">
                <h5><i data-lucide="settings" class="text-primary"></i> Configurações da Empresa</h5>
                <p>Gerencie a identidade visual e informações básicas da sua unidade corporativa.</p>
            </div>
            
            <div class="form-grid-5 mb-4">
                <!-- Nome Card -->
                <div class="upload-box-wrapper p-3">
                    <label class="upload-label"><i data-lucide="info"></i> Nome</label>
                    <div class="form-group mt-2">
                        <input type="text" name="name" value="<?php echo htmlspecialchars($company['name'] ?? ''); ?>" class="form-control" placeholder="ex: Minha Empresa" required>
                    </div>
                    <small class="text-muted d-block mt-auto pt-2">Nome fantasia exibido no painel.</small>
                </div>

                <!-- Document Card -->
                <div class="upload-box-wrapper p-3">
                    <label class="upload-label"><i data-lucide="contact"></i> Documento</label>
                    <div class="form-group mt-2">
                        <input type="text" name="document" value="<?php echo htmlspecialchars($company['document'] ?? ''); ?>" class="form-control mask-cnpj" placeholder="00.000.000/0000-00">
                    </div>
                    <small class="text-muted d-block mt-auto pt-2">CNPJ ou CPF da sua empresa.</small>
                </div>

                <!-- Email Card -->
                <div class="upload-box-wrapper p-3">
                    <label class="upload-label"><i data-lucide="mail"></i> E-mail</label>
                    <div class="form-group mt-2">
                        <input type="email" name="email" value="<?php echo htmlspecialchars($company['email'] ?? ''); ?>" class="form-control" placeholder="contato@empresa.com">
                    </div>
                    <small class="text-muted d-block mt-auto pt-2">E-mail principal para contatos.</small>
                </div>

                <!-- WhatsApp Card -->
                <div class="upload-box-wrapper p-3">
                    <label class="upload-label"><i data-lucide="phone"></i> WhatsApp</label>
                    <div class="form-group mt-2">
                        <input type="text" name="phone" value="<?php echo htmlspecialchars($company['phone'] ?? ''); ?>" class="form-control mask-phone" placeholder="(00) 00000-0000">
                    </div>
                    <small class="text-muted d-block mt-auto pt-2">Número de contato direto.</small>
                </div>

                <!-- Custom Domain Card (Rule 39 / White Label) -->
                <div class="upload-box-wrapper p-3">
                    <label class="upload-label"><i data-lucide="globe"></i> Domínio Personalizado</label>
                    <div class="form-group mt-2">
                        <input type="text" name="custom_domain" value="<?php echo htmlspecialchars($company['custom_domain'] ?? ''); ?>" class="form-control" placeholder="ex: orcamento.empresa.com.br">
                    </div>
                    <small class="text-muted d-block mt-auto pt-2">Aponta seu domínio (CNAME) para este portal.</small>
                </div>
            </div>

            <div class="form-grid-5 mb-4">
                <!-- Logo Card -->
                <div class="upload-box-wrapper p-3" style="grid-column: span 2;">
                    <label class="upload-label"><i data-lucide="image"></i> Logotipo</label>
                    <div class="upload-clickable-container">
                        <div id="preview-logo" class="upload-preview-box upload-clickable" onclick="document.getElementById('logo-upload').click()">
                            <?php if (!empty($company['logo'])): ?>
                                <img src="<?php echo SITE_URL . $company['logo']; ?>" alt="Logo" class="logo-img">
                            <?php else: ?>
                                <i data-lucide="upload" class="upload-placeholder-icon"></i>
                                <span class="upload-placeholder-text">Clique para enviar logo</span>
                            <?php endif; ?>
                            <div class="upload-hover-overlay">
                                <i data-lucide="upload"></i>
                                <span>Alterar</span>
                            </div>
                        </div>
                        <input type="file" id="logo-upload" name="logo" accept="image/*" onchange="previewImage(this, 'preview-logo', 'logo-img')" style="display: none;">
                    </div>
                    <small class="text-muted d-block mt-auto pt-2">Imagem usada em orçamentos e no menu lateral.</small>
                </div>

                <!-- Background Card -->
                <div class="upload-box-wrapper p-3" style="grid-column: span 3;">
                    <label class="upload-label"><i data-lucide="monitor"></i> Background da Tela de Login</label>
                    <div class="upload-clickable-container">
                        <div id="preview-bg" class="upload-preview-box upload-clickable" onclick="document.getElementById('bg-upload').click()">
                            <?php if (!empty($company['background_image'])): ?>
                                <img src="<?php echo SITE_URL . $company['background_image']; ?>" alt="BG" class="bg-img">
                            <?php else: ?>
                                <i data-lucide="upload" class="upload-placeholder-icon"></i>
                                <span class="upload-placeholder-text">Clique para enviar fundo de login</span>
                            <?php endif; ?>
                            <div class="upload-hover-overlay">
                                <i data-lucide="upload"></i>
                                <span>Alterar</span>
                            </div>
                        </div>
                        <input type="file" id="bg-upload" name="background_image" accept="image/*" onchange="previewImage(this, 'preview-bg', 'bg-img')" style="display: none;">
                    </div>
                    <small class="text-muted d-block mt-auto pt-2">Imagem que aparece no fundo da página de login da sua empresa.</small>
                </div>
            </div>

            <div class="settings-footer-section">
                <button type="submit" class="btn-primary settings-save-btn">
                    <span class="btn-text"><i data-lucide="save"></i> Salvar Agora</span>
                    <span class="btn-loader" style="display: none;"><i data-lucide="loader"></i> Salvando...</span>
                </button>
            </div>

        <?php elseif ($active_tab === 'themes'): ?>
            <div class="settings-header-box">
                <h5><i data-lucide="palette" class="text-primary"></i> Tema do Sistema</h5>
                <p>Personalize as cores predominantes do painel administrativo para sua marca.</p>
            </div>
            
            <div class="theme-grid">
                <?php foreach ($themes as $slug => $theme): 
                    $isSelected = ($slug === $current_theme);
                ?>
                    <label class="theme-card-label">
                        <input type="radio" name="theme" value="<?php echo $slug; ?>" <?php echo $isSelected ? 'checked' : ''; ?> style="display: none;">
                        <div class="theme-card-ui">
                            <div class="theme-card-preview" style="background: <?php echo $theme['bg']; ?>;">
                                <div class="theme-card-accent" style="background: <?php echo $theme['color']; ?>; box-shadow: 0 0 15px <?php echo $theme['color']; ?>88;"></div>
                                <div class="theme-card-subaccent" style="background: rgba(255,255,255,0.1);"></div>
                            </div>
                            <div class="text-center">
                                <span class="theme-card-name"><?php echo $theme['name']; ?></span>
                            </div>
                            <div class="theme-check-icon"><i data-lucide="check"></i></div>
                        </div>
                    </label>
                <?php endforeach; ?>
            </div>

            <div class="settings-footer-section">
                <button type="submit" class="btn-primary settings-save-btn">
                    <span class="btn-text"><i data-lucide="save"></i> Aplicar Tema</span>
                    <span class="btn-loader" style="display: none;"><i data-lucide="loader"></i> Aplicando...</span>
                </button>
            </div>
        <?php endif; ?>
    </form>
</div>

<script>
/**
 * Visual Preview for image uploads (Reused from core settings)
 */
function previewImage(input, previewId, imgClass) {
    const preview = document.getElementById(previewId);
    if (!preview) return;

    if (input.files && input.files[0]) {
        const reader = new FileReader();

        reader.onload = function(e) {
            // Remove placeholders
            const placeholderIcon = preview.querySelector('.upload-placeholder-icon');
            const placeholderText = preview.querySelector('.upload-placeholder-text');
            if (placeholderIcon) placeholderIcon.remove();
            if (placeholderText) placeholderText.remove();

            // Clear or update existing img
            let img = preview.querySelector('img');
            if (!img) {
                img = document.createElement('img');
                img.className = imgClass;
                const overlay = preview.querySelector('.upload-hover-overlay');
                if (overlay) {
                    preview.insertBefore(img, overlay);
                } else {
                    preview.appendChild(img);
                }
            }
            img.src = e.target.result;
            
            // Visual feedback
            preview.style.borderColor = 'var(--primary)';
            preview.classList.add('pulse-preview');
            setTimeout(() => preview.classList.remove('pulse-preview'), 1500);

            if(window.UI && window.UI.showToast) {
                UI.showToast('Imagem selecionada!', 'info');
            }
        }

        reader.readAsDataURL(input.files[0]);
    }
}

async function saveCompanySettings(e) {
    e.preventDefault();
    const form = e.target;
    const btn = form.querySelector('button[type="submit"]');
    const text = btn.querySelector('.btn-text');
    const loader = btn.querySelector('.btn-loader');
    
    if (btn.disabled) return;
    btn.disabled = true;
    if(text) text.style.display = 'none';
    if(loader) loader.style.display = 'inline-block';

    try {
        const formData = new FormData(form);
        const res = await fetch('<?php echo SITE_URL; ?>/api/company-settings/save', {
            method: 'POST',
            body: formData
        });
        const data = await res.json();
        if (data.success) {
            window.notify(data.message, 'success');
            setTimeout(() => window.location.reload(), 1500);
        } else {
            window.notify(data.message || 'Erro ao salvar.', 'error');
            btn.disabled = false;
            if(text) text.style.display = 'inline-block';
            if(loader) loader.style.display = 'none';
        }
    } catch (err) {
        window.notify('Não foi possível salvar as configurações.', 'error');
        btn.disabled = false;
        if(text) text.style.display = 'inline-block';
        if(loader) loader.style.display = 'none';
    }
}

// Input Masks
document.addEventListener('DOMContentLoaded', function() {
    const docInput = document.querySelector('.mask-cnpj');
    if(docInput) {
        docInput.addEventListener('input', function(e) {
            let v = e.target.value.replace(/\D/g, '');
            if (v.length > 14) v = v.substring(0, 14);
            if (v.length <= 11) {
                v = v.replace(/(\d{3})(\d)/, '$1.$2');
                v = v.replace(/(\d{3})(\d)/, '$1.$2');
                v = v.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
            } else {
                v = v.replace(/^(\d{2})(\d)/, '$1.$2');
                v = v.replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3');
                v = v.replace(/\.(\d{3})(\d)/, '.$1/$2');
                v = v.replace(/(\d{4})(\d)/, '$1-$2');
            }
            e.target.value = v;
        });
    }

    const phoneInput = document.querySelector('.mask-phone');
    if(phoneInput) {
        phoneInput.addEventListener('input', function(e) {
            let v = e.target.value.replace(/\D/g, '');
            if (v.length > 11) v = v.substring(0, 11);
            if (v.length > 10) {
                v = v.replace(/^(\d{2})(\d{5})(\d{4}).*/, '($1) $2-$3');
            } else if (v.length > 5) {
                v = v.replace(/^(\d{2})(\d{4})(\d{0,4}).*/, '($1) $2-$3');
            } else if (v.length > 2) {
                v = v.replace(/^(\d{2})(\d{0,5})/, '($1) $2');
            }
            e.target.value = v;
        });
    }
    
    // Create icons after render
    if (window.lucide) {
        lucide.createIcons();
    }
});
</script>
