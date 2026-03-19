<?php
<<<<<<<< HEAD:src/Views/app/profile.php
/** @var array $user */

include_once __DIR__ . '/../../../includes/header.php';
========
declare(strict_types=1);
require_once '../../includes/DB.php';
require_once dirname(__DIR__, 2) . "/includes/helpers/Auth.php";

Auth::requireLogin();

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM cp_users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
    die("Usuário não encontrado.");
}

include_once '../../includes/header.php';
>>>>>>>> ab660bf99d6d155d59d9302691d0bc8f9c62eeb9:public/app/profile.php
?>

<div class="profile-card">
    <div class="profile-info-header">
        <div class="profile-avatar" id="avatar-preview" title="Alterar Foto" onclick="document.getElementById('profile_picture').click()" style="<?php echo !empty($user['avatar']) ? 'background-image: url('.SITE_URL.'/uploads/profile/'.$user['avatar'].'); background-size: cover; background-position: center; color: transparent;' : ''; ?>">
            <?php echo empty($user['avatar']) ? strtoupper(substr($user['name'], 0, 1)) : ''; ?>
            <div class="avatar-edit-icon">
                <i class="fas fa-camera"></i>
            </div>
        </div>
        <div>
            <h3>Meu Perfil</h3>
            <p>Gerencie suas informações de acesso e dados pessoais.</p>
        </div>
    </div>

    <form action="<?php echo SITE_URL; ?>/api/profile/save" class="ajax-form" id="profileForm" enctype="multipart/form-data">
        <input type="file" id="profile_picture" name="profile_picture" accept="image/*" style="display: none;" onchange="UI.uploadProfilePicture(this)">
        <div class="profile-form-grid">
            <div class="form-group">
                <label class="form-label">Nome Completo</label>
                <input type="text" name="name" class="form-control w-100" value="<?php echo htmlspecialchars($user['name']); ?>" required>
            </div>

            <div class="form-grid-2">
                <div class="form-group">
                    <label class="form-label">Username (Login)</label>
                    <input type="text" class="form-control profile-input-readonly w-100" value="<?php echo htmlspecialchars($user['username']); ?>" readonly>
                </div>
                <div class="form-group">
                    <label class="form-label">E-mail</label>
                    <input type="email" name="email" class="form-control w-100" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                </div>
            </div>

            <div class="form-grid-2">
                <div class="form-group">
                    <label class="form-label">Telefone</label>
                    <input type="text" name="phone" class="form-control mask-phone w-100" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" placeholder="(00) 00000-0000">
                </div>
                <div class="form-group">
                    <label class="form-label">Nova Senha</label>
                    <div class="relative">
                        <input type="password" name="password" id="profile-password" class="form-control w-100 pr-5" placeholder="Deixe em branco para manter">
                        <button type="button" onclick="UI.generatePassword('profile-password')" class="btn-generate-password" title="Gerar Senha">
                            <i class="fas fa-random"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div class="form-grid-2">
                <div class="form-group">
                    <label class="form-label">CEP</label>
                    <input type="text" name="zip_code" class="form-control mask-zip w-100" value="<?php echo htmlspecialchars($user['zip_code'] ?? ''); ?>" onblur="UI.lookupZip(this.value, 'profile-city', 'profile-state', 'profile-street', 'profile-neighborhood')">
                </div>
                <div class="form-group">
                    <label class="form-label">Rua / Logradouro</label>
                    <input type="text" name="street" id="profile-street" class="form-control w-100" value="<?php echo htmlspecialchars($user['street'] ?? ''); ?>">
                </div>
            </div>

            <div class="form-grid-2">
                <div class="form-group">
                    <label class="form-label">Bairro</label>
                    <input type="text" name="neighborhood" id="profile-neighborhood" class="form-control w-100" value="<?php echo htmlspecialchars($user['neighborhood'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Cidade</label>
                    <input type="text" name="city" id="profile-city" class="form-control w-100" value="<?php echo htmlspecialchars($user['city'] ?? ''); ?>">
                </div>
            </div>

            <div class="form-grid-2">
                <div class="form-group">
                    <label class="form-label">UF</label>
                    <input type="text" name="state" id="profile-state" class="form-control w-100" value="<?php echo htmlspecialchars($user['state'] ?? ''); ?>" maxlength="2">
                </div>
                <div class="form-group">
                    <label class="form-label">Número</label>
                    <input type="text" name="address_number" class="form-control w-100" value="<?php echo htmlspecialchars($user['address_number'] ?? ''); ?>">
                </div>
            </div>
        </div>

        <div class="profile-footer-actions">
            <button type="submit" class="btn-primary">
                <i class="fas fa-save"></i> Salvar Alterações
            </button>
        </div>
    </form>
</div>

<<<<<<<< HEAD:src/Views/app/profile.php
<?php include_once __DIR__ . '/../../../includes/footer.php'; ?>
========
<?php include_once '../../includes/footer.php'; ?>
>>>>>>>> ab660bf99d6d155d59d9302691d0bc8f9c62eeb9:public/app/profile.php
