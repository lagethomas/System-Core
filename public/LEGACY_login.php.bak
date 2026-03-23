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
    <title>Login - <?php echo htmlspecialchars($platform_settings['system_name'] ?? 'SaaSFlow Core'); ?></title>
    <link rel="stylesheet" href="assets/css/style.css?v=<?php echo (string)time(); ?>">
    <link rel="stylesheet" href="assets/css/modules/auth.css?v=<?php echo (string)time(); ?>">
    <?php 
        $theme_slug = $platform_settings['system_theme'] ?? 'gold-black';
        echo '<link rel="stylesheet" href="assets/css/theme/' . $theme_slug . '.css?v=' . (string)time() . '">';
    ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body class="auth-wrapper" <?php if (!empty($platform_settings['login_background'])): ?> style="background: url('uploads/backgrounds/<?php echo $platform_settings['login_background']; ?>') no-repeat center center fixed; background-size: cover;" <?php endif; ?>>
    
    <?php if (!empty($platform_settings['login_background'])): ?>
        <div class="auth-overlay" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.4); z-index: 0;"></div>
    <?php endif; ?>
    <div class="auth-card" style="position: relative; z-index: 1; <?php echo !empty($platform_settings['login_background']) ? 'backdrop-filter: blur(12px); background: rgba(15, 17, 21, 0.85); border: 1px solid rgba(255,255,255,0.1);' : ''; ?>">
        <div class="auth-header">
            <div class="auth-logo-box">
                <?php if (!empty($platform_settings['system_logo'])): ?>
                    <img src="uploads/logos/<?php echo $platform_settings['system_logo']; ?>" alt="Logo" style="max-width: 100%; max-height: 100%; object-fit: contain;">
                <?php else: ?>
                    <i class="fas fa-layer-group"></i>
                <?php endif; ?>
            </div>
            <h2 class="auth-title"><?php echo htmlspecialchars($platform_settings['system_name'] ?? 'SaaSFlow'); ?></h2>
            <p class="auth-subtitle">Acesse sua conta para continuar</p>
        </div>
        
        <?php if ($error): ?>
            <div class="alert-error">
                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST" id="loginForm">
            <input type="hidden" name="csrf_token" value="<?php echo CSRF::generateToken(); ?>">
            <div class="form-group">
                <label class="auth-label">Usuário</label>
                <input type="text" name="username" class="form-control" placeholder="Seu usuário" required autofocus>
            </div>

            <div class="form-group mt-3">
                <label class="auth-label">Senha</label>
                <div class="password-toggle-wrapper">
                    <input type="password" name="password" id="password" class="form-control pr-10" placeholder="Sua senha" required>
                    <button type="button" class="btn-password-toggle" onclick="togglePassword('password')">
                        <i class="fas fa-key" id="password-toggle-icon"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn-primary btn-block mt-4" id="btnLogin">
                <span class="btn-text">Entrar no Sistema <i class="fas fa-arrow-right ml-2"></i></span>
                <span class="btn-loader" style="display: none;">
                    <i class="fas fa-circle-notch fa-spin mr-2"></i> Processando...
                </span>
            </button>
        </form>
    </div>

    <script>
        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById('password-toggle-icon');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-key');
                icon.classList.add('fa-unlock-alt');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-unlock-alt');
                icon.classList.add('fa-key');
            }
        }

        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const btn = document.getElementById('btnLogin');
            const btnText = btn.querySelector('.btn-text');
            const btnLoader = btn.querySelector('.btn-loader');
            
            // Desabilitar para evitar múltiplos cliques
            btn.disabled = true;
            btnText.style.display = 'none';
            btnLoader.style.display = 'flex';
            btnLoader.style.alignItems = 'center';
            btnLoader.style.justifyContent = 'center';
        });
    </script>
</body>
</html>
