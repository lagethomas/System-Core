<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmação de E-mail | <?php echo $systemName; ?></title>
    
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/modules/auth.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/theme/<?php echo $theme; ?>.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/fonts.css">
    <script src="<?php echo SITE_URL; ?>/assets/vendor/lucide/lucide.min.js"></script>
</head>
<body class="auth-wrapper">
    <div class="confirm-card">
        <div class="confirm-icon-circle <?php echo $success ? 'success' : 'error'; ?>">
            <i data-lucide="<?php echo $success ? 'check-circle' : 'x-circle'; ?>" class="icon-lg"></i>
        </div>
        <h2 class="confirm-title"><?php echo $success ? 'Sucesso!' : 'Algo deu errado'; ?></h2>
        <p class="confirm-text"><?php echo $message; ?></p>
        <a href="<?php echo SITE_URL; ?>/login" class="btn-primary mt-5">
            Ir para o Painel de Acesso
            <i data-lucide="arrow-right" class="icon-sm"></i>
        </a>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (typeof lucide !== 'undefined') lucide.createIcons();
        });
    </script>
</body>
</html>
