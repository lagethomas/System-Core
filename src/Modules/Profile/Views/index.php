<?php declare(strict_types=1); ?>
<?php
/** @var array $user */
$avatarUrl = !empty($user['avatar']) ? SITE_URL . '/uploads/profile/' . $user['avatar'] : null;
$initials = !empty($user['name']) ? strtoupper(substr($user['name'], 0, 1)) : '?';
?>

<div class="profile-header">
    <div class="flex items-center gap-4">
        <div class="header-icon-box">
            <i data-lucide="user"></i>
        </div>
        <div>
            <h2 class="m-0">Meu Perfil</h2>
            <p class="text-muted m-0">Gerencie suas informações de acesso, dados pessoais e preferências.</p>
        </div>
    </div>
</div>

<div class="profile-layout">
    <div class="profile-sidebar">
        <!-- Card de Avatar -->
        <div class="card glassmorphism p-5 text-center mb-4 flex flex-column items-center">
            <div class="profile-avatar-wrapper mb-4" id="avatar-preview" onclick="document.getElementById('profile_picture').click()" 
                 style="<?php echo $avatarUrl ? "background-image: url('$avatarUrl');" : ""; ?>">
                <?php if (!$avatarUrl): ?>
                    <span class="avatar-initials"><?php echo $initials; ?></span>
                <?php endif; ?>
                <div class="avatar-overlay">
                    <i data-lucide="camera" class="icon-lg"></i>
                </div>
            </div>
            <h3 class="mb-1 fw-800 text-white"><?php echo htmlspecialchars($user['name']); ?></h3>
            <p class="text-muted small mb-4"><?php echo htmlspecialchars($user['email']); ?></p>
            <span class="status-badge status-primary text-uppercase">
                <?php echo htmlspecialchars(str_replace('ROLE_', '', $user['role'] ?? 'Membro')); ?>
            </span>
        </div>

        <!-- Atalhos/Info -->
        <div class="card p-4 border-dashed rounded-20 bg-transparent">
            <h4 class="small fw-800 text-muted text-uppercase tracking-widest mb-4">Estatísticas</h4>
            <div class="flex flex-column gap-3">
                <div class="flex items-center gap-3">
                    <i data-lucide="calendar" class="icon-sm text-primary"></i>
                    <span class="small">Membro desde <?php echo date('d/m/Y', strtotime($user['created_at'])); ?></span>
                </div>
                <div class="flex items-center gap-3">
                    <i data-lucide="shield-check" class="icon-sm text-success"></i>
                    <span class="small">Perfil Verificado</span>
                </div>
            </div>
        </div>
    </div>

    <div class="profile-main">
        <div class="card p-5">
            <form action="<?php echo SITE_URL; ?>/api/profile/save" method="POST" class="ajax-form premium-form" id="profileForm" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?php echo CSRF::generateToken(); ?>">
                <input type="file" id="profile_picture" name="profile_picture" accept="image/*" class="d-none" onchange="previewAvatar(this)">
                
                <h3 class="mb-5 fw-700 text-main">
                    <i data-lucide="user-cog" class="icon-sm mr-2 text-primary"></i> Informações Básicas
                </h3>
                
                <div class="floating-group mb-4">
                    <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($user['name']); ?>" placeholder=" " required>
                    <label class="floating-label">Nome Completo</label>
                </div>

                <div class="form-grid-2 gap-4 mb-4">
                    <div class="floating-group">
                        <input type="text" class="form-control text-muted bg-card-alt" value="<?php echo htmlspecialchars($user['username']); ?>" placeholder=" " readonly>
                        <label class="floating-label">Nome de Usuário (Login)</label>
                    </div>
                    <div class="floating-group">
                        <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" placeholder=" " required>
                        <label class="floating-label">Endereço de E-mail</label>
                    </div>
                </div>

                <div class="form-grid-2 gap-4 mb-5">
                    <div class="floating-group">
                        <input type="text" name="phone" id="profile-phone" class="form-control mask-phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" placeholder=" ">
                        <label class="floating-label">Telefone / WhatsApp</label>
                    </div>
                    <div class="floating-group">
                        <div class="password-toggle-wrapper">
                            <input type="password" name="password" id="profile-password" class="form-control" placeholder=" ">
                            <label class="floating-label">Nova Senha (deixe vazio para manter)</label>
                            <div class="floating-actions">
                                <button type="button" class="btn-password-toggle" onclick="UI.togglePassword(this, 'profile-password')">
                                    <i data-lucide="eye"></i>
                                </button>
                                <button type="button" onclick="UI.generatePassword('profile-password')" class="btn-generate-password" title="Gerar Senha">
                                    <i data-lucide="shuffle"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <h3 class="mb-5 mt-5 fw-700 text-main">
                    <i data-lucide="map" class="icon-sm mr-2 text-primary"></i> Endereço e Localização
                </h3>

                <div class="form-grid-3 gap-4 mb-4">
                    <div class="floating-group">
                        <input type="text" name="zip_code" class="form-control mask-zip" value="<?php echo htmlspecialchars($user['zip_code'] ?? ''); ?>" placeholder=" " onblur="UI.lookupZip(this.value, 'p-city', 'p-state', 'p-street', 'p-neighborhood')">
                        <label class="floating-label">CEP</label>
                    </div>
                    <div class="floating-group grid-span-2">
                        <input type="text" name="street" id="p-street" class="form-control" value="<?php echo htmlspecialchars($user['street'] ?? ''); ?>" placeholder=" ">
                        <label class="floating-label">Rua / Avenida</label>
                    </div>
                </div>

                <div class="form-grid-3 gap-4 mb-5">
                    <div class="floating-group">
                        <input type="text" name="address_number" class="form-control" value="<?php echo htmlspecialchars($user['address_number'] ?? ''); ?>" placeholder=" ">
                        <label class="floating-label">Número</label>
                    </div>
                    <div class="floating-group">
                        <input type="text" name="neighborhood" id="p-neighborhood" class="form-control" value="<?php echo htmlspecialchars($user['neighborhood'] ?? ''); ?>" placeholder=" ">
                        <label class="floating-label">Bairro</label>
                    </div>
                    <div class="floating-group">
                        <input type="text" name="city" id="p-city" class="form-control" value="<?php echo htmlspecialchars($user['city'] ?? ''); ?>" placeholder=" ">
                        <label class="floating-label">Cidade</label>
                    </div>
                </div>

                <div class="floating-group mb-5">
                    <input type="text" name="state" id="p-state" class="form-control" value="<?php echo htmlspecialchars($user['state'] ?? ''); ?>" maxlength="2" placeholder=" ">
                    <label class="floating-label">Estado (UF)</label>
                </div>

                <div class="pt-5 border-top flex justify-end">
                    <button type="submit" class="btn-primary btn-premium">
                        <i data-lucide="check-circle" class="icon-sm mr-2"></i> Atualizar Meu Perfil
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="<?php echo SITE_URL; ?>/assets/js/modules/profile.js"></script>
