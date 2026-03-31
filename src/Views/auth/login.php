<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | <?php echo $system_name; ?></title>
    
    <!-- Rule 34: CSS External Stylesheets -->
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css?v=<?php echo $v; ?>">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/modules/auth.css?v=<?php echo $v; ?>">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/theme/<?php echo $theme_slug; ?>.css?v=<?php echo $v; ?>">
    
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="<?php echo SITE_URL; ?>/assets/img/icon-192.png">

    <!-- Dynamic Theme-Aware Background (Rule 34: Minimal Dynamic Handling via Style Tag) -->
    <?php if (!empty($settings['login_background'])): ?>
    <style>
        body.auth-wrapper.has-bg {
            background-image: url('<?php echo SITE_URL; ?>/uploads/backgrounds/<?php echo $settings['login_background']; ?>');
        }
    </style>
    <?php endif; ?>

    <!-- Rule 96: SweetAlert2 (Must be before UI helpers) -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- Rule 36: Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="auth-wrapper <?php echo !empty($settings['login_background']) ? 'has-bg' : ''; ?>">
    
    <?php if (!empty($settings['login_background'])): ?>
        <div class="auth-overlay"></div>
    <?php endif; ?>

    <div class="auth-card <?php echo !empty($settings['login_background']) ? 'glassmorphism' : ''; ?>">
        <div class="auth-header">
            <div class="auth-logo-box">
                <?php if (!empty($settings['system_logo'])): ?>
                    <img src="<?php echo SITE_URL; ?>/uploads/logos/<?php echo $settings['system_logo']; ?>" alt="Logo">
                <?php else: ?>
                    <i data-lucide="shield" class="icon-xl"></i>
                <?php endif; ?>
            </div>
            <h2 class="auth-title"><?php echo $system_name; ?></h2>
            <p class="auth-subtitle">Acesse sua conta para continuar</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert-error">
                <i data-lucide="alert-circle"></i> 
                <span><?php echo htmlspecialchars($error); ?></span>
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
                <div class="password-toggle-wrapper">
                    <input type="password" name="password" id="password" class="form-control pr-10" 
                           value="<?php echo htmlspecialchars($pre_password ?? ''); ?>"
                           placeholder="Sua senha" required>
                    <button type="button" class="btn-password-toggle" onclick="UI.togglePassword(this, 'password')">
                        <i data-lucide="lock"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn-primary btn-block mt-4" id="btnLogin">
                <span class="btn-text">Entrar no Sistema <i data-lucide="arrow-right" class="ml-2"></i></span>
                <span class="btn-loader" style="display: none;">
                    <i data-lucide="loader-2" class="animate-spin mr-2"></i> Processando...
                </span>
            </button>

            <?php if ($warn_session): ?>
                <div class="alert-session-bottom">
                    <div class="alert-header">
                        <i data-lucide="shield-alert"></i>
                        <span>Você já possui uma sessão ativa. Se deseja encerrá-la e entrar neste dispositivo:</span>
                    </div>
                    <button type="submit" name="force_login" value="1" class="btn-danger-soft">
                         <span style="display: flex; align-items: center; justify-content: center; gap: 8px;">
                            <i data-lucide="log-in" style="width: 16px; height: 16px;"></i> 
                            Encerrar outra sessão e entrar aqui
                         </span>
                    </button>
                </div>
            <?php endif; ?>
        </form>
    </div>

    <!-- Rule 34: Javascript External Helpers -->
    <script src="<?php echo SITE_URL; ?>/assets/js/components/ui-core.js?v=<?php echo $v; ?>"></script>
    
    <script>
        // Initialize UI Elements
        document.addEventListener('DOMContentLoaded', () => {
            // Rule 36: Lucide Init
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }

            // Rule 97: Global Notifications via UI Core
            <?php if (!empty($error)): ?>
            if (typeof window.notify === 'function') {
                window.notify("<?php echo addslashes($error); ?>", "error");
            }
            <?php endif; ?>
        });

        // Rule 35: Form Interactivity
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const btn = document.getElementById('btnLogin');
            const btnText = btn.querySelector('.btn-text');
            const btnLoader = btn.querySelector('.btn-loader');
            
            // Avoid multiple submits
            btn.disabled = true;
            if (btnText) btnText.style.display = 'none';
            if (btnLoader) {
                btnLoader.style.display = 'flex';
                btnLoader.style.alignItems = 'center';
                btnLoader.style.justifyContent = 'center';
            }
        });
    </script>
</body>
</html>
