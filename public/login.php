<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/logs.php';

if (Auth::isLoggedIn()) {
    header('Location: dashboard');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF Validation
    if (!CSRF::verifyToken($_POST['csrf_token'] ?? '')) {
        $error = 'Erro de segurança (CSRF). Tente novamente.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($username && $password) {
            $stmt = $pdo->prepare('SELECT * FROM cp_users WHERE username = ?');
            $stmt->execute([$username]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                session_regenerate_id(true);
                Auth::login($user);
                
                Logger::log('login', "Login realizado.");
                
                header('Location: dashboard');
                exit;
            } else {
                $error = 'Credenciais inválidas.';
            }
        } else {
            $error = 'Preencha todos os campos.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | <?php echo htmlspecialchars($platform_settings['system_name'] ?? 'SaaSFlow Core'); ?></title>
    
    <!-- Rule 34: CSS External Stylesheets -->
    <link rel="stylesheet" href="assets/css/style.css?v=<?php echo (string)time(); ?>">
    <link rel="stylesheet" href="assets/css/modules/auth.css?v=<?php echo (string)time(); ?>">
    <?php 
        $theme_slug = $platform_settings['system_theme'] ?? 'gold-black';
        echo '<link rel="stylesheet" href="assets/css/theme/' . $theme_slug . '.css?v=' . (string)time() . '">';
    ?>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Dynamic Theme-Aware Background -->
    <?php if (!empty($platform_settings['login_background'])): ?>
    <style>
        body.auth-wrapper.has-bg {
            background-image: url('uploads/backgrounds/<?php echo $platform_settings['login_background']; ?>');
        }
    </style>
    <?php endif; ?>

    <!-- Rule 96: SweetAlert2 (Must be before UI helpers) -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Rule 36: Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="auth-wrapper <?php echo !empty($platform_settings['login_background']) ? 'has-bg' : ''; ?>">
    
    <?php if (!empty($platform_settings['login_background'])): ?>
        <div class="auth-overlay"></div>
    <?php endif; ?>

    <div class="auth-card <?php echo !empty($platform_settings['login_background']) ? 'glassmorphism' : ''; ?>">
        <div class="auth-header">
            <div class="auth-logo-box">
                <?php if (!empty($platform_settings['system_logo'])): ?>
                    <img src="uploads/logos/<?php echo $platform_settings['system_logo']; ?>" alt="Logo">
                <?php else: ?>
                    <i data-lucide="shield" class="icon-lg"></i>
                <?php endif; ?>
            </div>
            <h2 class="auth-title"><?php echo htmlspecialchars($platform_settings['system_name'] ?? 'SaaSFlow'); ?></h2>
            <p class="auth-subtitle">Acesse sua conta para continuar</p>
        </div>
        
        <?php if ($error): ?>
            <div class="alert-error">
                <i data-lucide="alert-circle"></i> <span><?php echo htmlspecialchars($error); ?></span>
            </div>
        <?php endif; ?>

        <form method="POST" id="loginForm">
            <input type="hidden" name="csrf_token" value="<?php echo CSRF::generateToken(); ?>">
            <div class="form-group">
                <label class="auth-label">Usuário</label>
                <div class="input-with-icon">
                    <input type="text" name="username" class="form-control" placeholder="Seu usuário" required autofocus>
                </div>
            </div>

            <div class="form-group mt-3">
                <label class="auth-label">Senha</label>
                <div class="password-toggle-wrapper">
                    <input type="password" name="password" id="password" class="form-control pr-10" placeholder="Sua senha" required>
                    <button type="button" class="btn-password-toggle" onclick="UI.togglePassword(this, 'password')">
                        <i data-lucide="lock"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn-primary btn-block mt-4" id="btnLogin">
                <span class="btn-text">Entrar no Sistema <i data-lucide="arrow-right" class="ml-2"></i></span>
                <span class="btn-loader" style="display: none;">
                    <i data-lucide="loader-2" class="mr-2 animate-spin"></i> Processando...
                </span>
            </button>
        </form>
    </div>

    <!-- UI Helpers -->
    <script src="assets/js/components/ui-core.js?v=<?php echo (string)time(); ?>"></script>

    <script>
        // Start UI Components
        document.addEventListener('DOMContentLoaded', () => {
            // Init Lucide
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }

            // Error Feedback via Rule 97 (notify helper from ui-core)
            <?php if ($error): ?>
            if (typeof window.notify === 'function') {
                window.notify("<?php echo addslashes($error); ?>", "error");
            }
            <?php endif; ?>
        });

        // Form Submission Logic
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const btn = document.getElementById('btnLogin');
            const btnText = btn.querySelector('.btn-text');
            const btnLoader = btn.querySelector('.btn-loader');
            
            btn.disabled = true;
            btnText.style.display = 'none';
            btnLoader.style.display = 'flex';
            btnLoader.style.alignItems = 'center';
            btnLoader.style.justifyContent = 'center';
        });
    </script>
</body>
</html>
