<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login | <?php echo $system_name; ?></title>

<link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css?v=<?php echo $v; ?>">
<link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/app-premium.css?v=<?php echo $v; ?>">
<link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/modules/auth.css?v=<?php echo $v; ?>">
<link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/theme/<?php echo $theme_slug; ?>.css?v=<?php echo $v; ?>">

<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="icon" type="image/png" href="<?php echo SITE_URL; ?>/assets/img/icon-192.png">

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="auth-wrapper" <?php if (!empty($login_background)): ?>style="--auth-bg: url('<?php echo (strpos($login_background, '/') === 0 ? SITE_URL . $login_background : SITE_URL . '/uploads/backgrounds/' . $login_background); ?>');"<?php endif; ?>>

<div class="auth-overlay"></div>

<div class="auth-card glassmorphism">

<div class="auth-header">
<div class="auth-logo-box">
<?php if (!empty($system_logo)): ?>
<img src="<?php echo (strpos($system_logo, '/') === 0 || strpos($system_logo, 'http') === 0) ? SITE_URL . $system_logo : SITE_URL . '/uploads/logos/' . $system_logo; ?>" alt="Logo">
<?php else: ?>
<i data-lucide="shield-check" class="icon-lg"></i>
<?php endif; ?>
</div>
<h2 class="auth-title"><?php echo $system_name; ?></h2>
<p class="auth-subtitle">Bem-vindo(a)! Identifique-se para acessar.</p>
</div>

<?php if (!empty($error)): ?>
<div class="auth-alert-error">
<i data-lucide="alert-triangle"></i>
<span><?php echo htmlspecialchars($error); ?></span>
</div>
<?php endif; ?>

<form method="POST" action="<?php echo SITE_URL; ?>/login" id="loginForm" class="auth-form">
<input type="hidden" name="csrf_token" value="<?php echo \CSRF::generateToken(); ?>">
<input type="hidden" name="company_id" value="<?php echo $company ? $company['id'] : ''; ?>">

<div class="floating-group">
<input type="text" name="username" class="form-control" value="<?php echo htmlspecialchars($pre_username ?? ''); ?>" placeholder=" " required autofocus>
<label class="floating-label">Usuário de Acesso</label>
</div>

<div class="floating-group mt-4">
<div class="password-toggle-wrapper">
<input type="password" name="password" id="password" class="form-control" value="<?php echo htmlspecialchars($pre_password ?? ''); ?>" placeholder=" " required>
<label class="floating-label">Sua Senha</label>
<div class="floating-actions">
<button type="button" class="btn-password-toggle" onclick="UI.togglePassword(this, 'password')">
<i data-lucide="eye"></i>
</button>
</div>
</div>
</div>

<button type="submit" class="btn-primary btn-auth-submit" id="btnLogin">
<span class="btn-text">ACESSAR PAINEL</span>
<i data-lucide="arrow-right" class="icon-sm"></i>
</button>

<?php if ($warn_session): ?>
<div class="auth-session-warn">
<p class="auth-session-warn-text">Detectamos uma sessão ativa em outro local.</p>
<button type="submit" name="force_login" value="1" class="btn-secondary" style="border-color: rgba(245, 158, 11, 0.3); color: #fbbf24; width: auto !important; padding: 10px 20px;">
Limpar Sessão e Entrar Agora
</button>
</div>
<?php endif; ?>
</form>
</div>

<script src="<?php echo SITE_URL; ?>/assets/js/components/ui-core.js?v=<?php echo $v; ?>"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    if (typeof lucide !== 'undefined') lucide.createIcons();
});

document.getElementById('loginForm').addEventListener('submit', function(e) {
    const btn = document.getElementById('btnLogin');
    btn.disabled = true;
    btn.innerHTML = '<i data-lucide="loader-2" class="animate-spin icon-sm"></i> Autenticando...';
lucide.createIcons();
});
</script>
</body>
</html>
