<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo $system_name; ?></title>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css?v=<?php echo $v; ?>">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/modules/auth.css?v=<?php echo $v; ?>">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/theme/<?php echo $theme_slug; ?>.css?v=<?php echo $v; ?>">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="<?php echo SITE_URL; ?>/assets/img/icon-192.png">

    <style>
        @keyframes slideIn {
            from { opacity: 0; transform: translateY(-8px); }
            to   { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body class="auth-wrapper" <?php if (!empty($settings['login_background'])): ?> style="background: url('<?php echo SITE_URL; ?>/uploads/backgrounds/<?php echo $settings['login_background']; ?>') no-repeat center center fixed; background-size: cover;" <?php endif; ?>>
    
    <?php if (!empty($settings['login_background'])): ?>
        <div class="auth-overlay" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.4); z-index: 0;"></div>
    <?php endif; ?>

    <div class="auth-card" style="position: relative; z-index: 1; <?php echo !empty($settings['login_background']) ? 'backdrop-filter: blur(12px); background: rgba(15, 17, 21, 0.85); border: 1px solid rgba(255,255,255,0.1); shadow: 0 25px 50px rgba(0,0,0,0.5);' : ''; ?>">
        <div class="auth-header">
            <div class="auth-logo-box">
                <?php if (!empty($settings['system_logo'])): ?>
                    <img src="<?php echo SITE_URL; ?>/uploads/logos/<?php echo $settings['system_logo']; ?>" alt="Logo" style="max-width: 100%; max-height: 100%; object-fit: contain;">
                <?php else: ?>
                    <i data-lucide="layers"></i>
                <?php endif; ?>
            </div>
            <h2 class="auth-title"><?php echo $system_name; ?></h2>
            <p class="auth-subtitle">Acesse sua conta para continuar</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert-error">
                <i data-lucide="alert-circle"></i> <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?php echo SITE_URL; ?>/login" id="loginForm">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            <div class="form-group">
                <label class="auth-label">Usuário</label>
                <input type="text" name="username" class="form-control"
                       value="<?php echo htmlspecialchars($pre_username ?? ''); ?>"
                       placeholder="Seu usuário" required autofocus>
            </div>

            <div class="form-group mt-3">
                <label class="auth-label">Senha</label>
                <input type="password" name="password" class="form-control" 
                       value="<?php echo htmlspecialchars($pre_password ?? ''); ?>"
                       placeholder="Sua senha" required>
            </div>

            <button type="submit" class="btn-primary btn-block mt-4" id="btnLogin">
                <span class="btn-text">Entrar no Sistema <i data-lucide="arrow-right" class="ml-2"></i></span>
                <span class="btn-loader" style="display: none;">
                    <i data-lucide="loader-2" class="animate-spin mr-2"></i> Processando...
                </span>
            </button>

            <?php if ($warn_session): ?>
                <div class="alert-session-bottom" style="margin-top: 20px; padding: 12px; background: rgba(220, 38, 38, 0.1); border-radius: 8px; border: 1px solid rgba(220, 38, 38, 0.2); color: #ef4444; font-size: 0.85rem; display: flex; flex-direction: column; gap: 8px; animation: slideIn 0.3s ease;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <i data-lucide="user-x" style="font-size: 1.1rem;"></i>
                        <span>Você já possui uma sessão ativa. Se deseja encerrá-la e entrar neste dispositivo:</span>
                    </div>
                    <button type="submit" name="force_login" value="1" class="btn-danger btn-block" style="background: #ef4444; border: none; color: white; padding: 8px 12px; border-radius: 6px; cursor: pointer; font-size: 0.8rem; font-weight: 600; transition: all 0.2s;">
                        <i data-lucide="log-in" class="mr-2"></i> Encerrar outra sessão e entrar aqui
                    </button>
                </div>
            <?php endif; ?>
        </form>
    </div>

    <script src="<?php echo SITE_URL; ?>/assets/js/components/ui-core.js"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script>
        if (typeof UI !== 'undefined') UI.initPasswordToggles();
        if (typeof lucide !== 'undefined') lucide.createIcons();

        function lockBtn(formId, btnId) {
            const form = document.getElementById(formId);
            if (!form) return;
            form.addEventListener('submit', function() {
                const btn = document.getElementById(btnId);
                if (!btn) return;
                btn.disabled = true;
                const text   = btn.querySelector('.btn-text');
                const loader = btn.querySelector('.btn-loader');
                if (text)   text.style.display  = 'none';
                if (loader) { loader.style.display = 'flex'; loader.style.alignItems = 'center'; }
            });
        }

        lockBtn('loginForm', 'btnLogin');
    </script>
</body>
</html>
