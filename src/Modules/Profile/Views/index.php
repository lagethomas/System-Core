<?php
/** @var array $user */
?>

<div class="card" style="padding: 30px; position: relative;">
    <div style="display: flex; align-items: center; gap: 20px; margin-bottom: 40px; border-bottom: 1px solid var(--border); padding-bottom: 30px;">
        <div id="avatar-preview" onclick="document.getElementById('profile_picture').click()" 
             style="width: 100px; height: 100px; border-radius: 50%; background: var(--bg-surface); border: 2px solid var(--primary); display: flex; align-items: center; justify-content: center; font-size: 32px; font-weight: 800; color: var(--primary); cursor: pointer; position: relative; overflow: hidden; <?php echo !empty($user['avatar']) ? 'background-image: url('.SITE_URL.'/uploads/profile/'.$user['avatar'].'); background-size: cover; background-position: center; color: transparent;' : ''; ?>">
            <?php echo empty($user['avatar']) ? strtoupper(substr($user['name'], 0, 1)) : ''; ?>
            <div style="position: absolute; bottom: 0; left: 0; width: 100%; background: rgba(0,0,0,0.6); color: #fff; font-size: 10px; padding: 4px 0; text-align: center;">
                <i data-lucide="camera" style="width: 12px; height: 12px;"></i> EDITAR
            </div>
        </div>
        <div>
            <h3 style="color: var(--text-main); margin-bottom: 5px;">Meu Perfil</h3>
            <p style="color: var(--text-muted); font-size: 14px;">Mantenha seus dados atualizados para garantir a segurança da conta.</p>
        </div>
    </div>

    <form onsubmit="saveProfile(event, this)">
        <input type="file" id="profile_picture" name="profile_picture" accept="image/*" style="display: none;" onchange="previewAvatar(this)">
        
        <div class="form-grid-2">
            <div class="floating-group">
                <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($user['name']); ?>" required placeholder=" ">
                <label class="floating-label">Nome Completo</label>
            </div>
            <div class="floating-group">
                <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['username']); ?>" readonly placeholder=" " style="background: rgba(var(--primary-rgb), 0.03); opacity: 0.7;">
                <label class="floating-label">Usuário (Login)</label>
            </div>
        </div>

        <div class="form-grid-2">
            <div class="floating-group">
                <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" required placeholder=" ">
                <label class="floating-label">E-mail</label>
            </div>
            <div class="floating-group">
                <div class="password-toggle-wrapper">
                    <input type="password" name="password" id="profile-password" class="form-control" placeholder=" ">
                    <label class="floating-label">Nova Senha (deixe em branco para manter)</label>
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

        <div class="form-grid-2">
            <div class="floating-group">
                <input type="text" name="phone" class="form-control mask-phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" placeholder=" ">
                <label class="floating-label">Telefone / WhatsApp</label>
            </div>
            <div class="floating-group">
                <input type="text" name="zip_code" class="form-control mask-zip" value="<?php echo htmlspecialchars($user['zip_code'] ?? ''); ?>" placeholder=" " onblur="UI.lookupZip(this.value, 'p-city', 'p-state', 'p-street', 'p-neighborhood')">
                <label class="floating-label">CEP</label>
            </div>
        </div>

        <div class="form-grid-2">
            <div class="floating-group">
                <input type="text" name="street" id="p-street" class="form-control" value="<?php echo htmlspecialchars($user['street'] ?? ''); ?>" placeholder=" ">
                <label class="floating-label">Rua / Logradouro</label>
            </div>
            <div class="floating-group">
                <input type="text" name="neighborhood" id="p-neighborhood" class="form-control" value="<?php echo htmlspecialchars($user['neighborhood'] ?? ''); ?>" placeholder=" ">
                <label class="floating-label">Bairro</label>
            </div>
        </div>

        <div class="form-grid-3">
            <div class="floating-group">
                <input type="text" name="city" id="p-city" class="form-control" value="<?php echo htmlspecialchars($user['city'] ?? ''); ?>" placeholder=" ">
                <label class="floating-label">Cidade</label>
            </div>
            <div class="floating-group">
                <input type="text" name="state" id="p-state" class="form-control" value="<?php echo htmlspecialchars($user['state'] ?? ''); ?>" maxlength="2" placeholder=" ">
                <label class="floating-label">UF</label>
            </div>
            <div class="floating-group">
                <input type="text" name="address_number" class="form-control" value="<?php echo htmlspecialchars($user['address_number'] ?? ''); ?>" placeholder=" ">
                <label class="floating-label">Número</label>
            </div>
        </div>

        <div style="margin-top: 40px; text-align: right;">
            <button type="submit" class="btn-primary">
                <i data-lucide="save" class="icon-lucide icon-sm mr-2"></i> Salvar Minhas Alterações
            </button>
        </div>
    </form>
</div>

<script>
async function saveProfile(e, form) {
    e.preventDefault();
    const btn = form.querySelector('button[type="submit"]');
    if (btn) btn.disabled = true;

    try {
        const formData = new FormData(form);
        const res = await fetch('<?php echo SITE_URL; ?>/api/profile/save', {
            method: 'POST',
            body: formData
        });
        const result = await res.json();
        if (result.success) {
            UI.showToast(result.message);
        } else {
            UI.showToast(result.message || 'Erro ao salvar', 'error');
        }
    } catch (e) {
        UI.showToast('Erro de conexão', 'error');
    } finally {
        if (btn) btn.disabled = false;
    }
}

function previewAvatar(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('avatar-preview');
            preview.style.backgroundImage = `url(${e.target.result})`;
            preview.style.color = 'transparent';
        }
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
