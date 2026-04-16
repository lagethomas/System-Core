<?php
/** @var string $user_name */
/** @var int $total_users */
/** @var int $total_logs */
/** @var bool $is_admin */
$v = (string)time();
?>
<link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/modules/dashboard.css?v=<?php echo $v; ?>">

<div class="welcome-header">
    <h1>Olá, <?php echo htmlspecialchars($user_name); ?>! 👋</h1>
    <p class="text-muted">Bem-vindo ao centro de controle modular do seu sistema.</p>
</div>

<div class="welcome-container mb-5">
    <div class="row align-items-center mt-4">
        <div class="col-md-4 text-md-right">
            <div class="date-badge">
                <i data-lucide="calendar" class="icon-lucide icon-sm mr-2"></i>
                <?php echo date('d \d\e F, Y'); ?>
            </div>
        </div>
    </div>
</div>

<?php if ($is_admin): ?>
<div class="stats-grid mb-5">
    <a href="<?php echo SITE_URL; ?>/admin/users" class="stat-card-premium">
        <div class="stat-icon-box blue">
            <i data-lucide="users" class="icon-lucide"></i>
        </div>
        <div class="stat-info-premium">
            <span class="stat-label-premium">Usuários</span>
            <h3 class="stat-value-premium"><?php echo $total_users; ?></h3>
        </div>
        <div class="stat-action-hint">
            <i data-lucide="arrow-up-right" class="icon-lucide icon-sm"></i>
        </div>
    </a>

    <a href="<?php echo SITE_URL; ?>/admin/logs" class="stat-card-premium">
        <div class="stat-icon-box green">
            <i data-lucide="activity" class="icon-lucide"></i>
        </div>
        <div class="stat-info-premium">
            <span class="stat-label-premium">Atividades</span>
            <h3 class="stat-value-premium"><?php echo $total_logs; ?></h3>
        </div>
        <div class="stat-action-hint">
            <i data-lucide="arrow-up-right" class="icon-lucide icon-sm"></i>
        </div>
    </a>
</div>

<div class="dashboard-grid">
    <div class="dashboard-card">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="m-0 fw-800 text-main font-md">Resumo do Sistema</h3>
            <span class="badge badge-primary-lite">Módulo Ativo</span>
        </div>
        
        <p class="text-muted small mb-4 line-height-md">
            O sistema foi atualizado para uma <strong>arquitetura modular</strong>. Este painel faz parte do <code>Module/Dashboard</code>, garantindo maior isolamento e facilidade de manutenção.
        </p>

        <div class="info-block mb-4">
            <ul class="info-list">
                <li class="d-flex align-items-center gap-2 small text-muted">
                    <i data-lucide="check-circle" class="icon-lucide icon-xs text-primary"></i> Controladores Modulares (Namespaces)
                </li>
                <li class="d-flex align-items-center gap-2 small text-muted">
                    <i data-lucide="check-circle" class="icon-lucide icon-xs text-primary"></i> Views internas por domínio
                </li>
                <li class="d-flex align-items-center gap-2 small text-muted">
                    <i data-lucide="check-circle" class="icon-lucide icon-xs text-primary"></i> Padronização AJAX/JSON
                </li>
            </ul>
        </div>

        <a href="<?php echo SITE_URL; ?>/admin/settings" class="btn-primary-glass">
            Configurações <i data-lucide="settings" class="icon-sm"></i>
        </a>
    </div>

    <div class="dashboard-card">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="m-0 fw-800 text-main font-md">Acesso Rápido</h3>
            <span class="badge badge-primary-lite">Links</span>
        </div>
        <div class="form-grid-1">
            <a href="<?php echo SITE_URL; ?>/admin/users" class="card-link-premium">
                <div class="d-flex align-items-center gap-3 w-100">
                    <div class="icon-box-lite"><i data-lucide="user-plus" class="icon-sm"></i></div>
                    <span class="small fw-700">Gerenciar Usuários</span>
                    <i data-lucide="chevron-right" class="icon-lucide icon-xs ml-auto"></i>
                </div>
            </a>
            <a href="<?php echo SITE_URL; ?>/profile" class="card-link-premium">
                <div class="d-flex align-items-center gap-3 w-100">
                    <div class="icon-box-lite"><i data-lucide="user-circle" class="icon-sm"></i></div>
                    <span class="small fw-700">Meu Perfil</span>
                    <i data-lucide="chevron-right" class="icon-lucide icon-xs ml-auto"></i>
                </div>
            </a>
            <a href="<?php echo SITE_URL; ?>/admin/logs" class="card-link-premium">
                <div class="d-flex align-items-center gap-3 w-100">
                    <div class="icon-box-lite"><i data-lucide="activity" class="icon-sm"></i></div>
                    <span class="small fw-700">Logs do Sistema</span>
                    <i data-lucide="chevron-right" class="icon-lucide icon-xs ml-auto"></i>
                </div>
            </a>
        </div>
    </div>
</div>
<?php else: ?>
    <div class="unauthorized-container">
        <div class="unauthorized-icon-box">
            <i data-lucide="shield-check" class="icon-xl"></i>
        </div>
        <h2 class="unauthorized-title">Acesso Autorizado</h2>
        <p class="unauthorized-text">Bem-vindo ao sistema modular. Utilize o menu lateral para gerenciar sua conta e as ferramentas disponíveis.</p>
        <a href="<?php echo SITE_URL; ?>/profile" class="btn-primary mt-4">
            Ver Meu Perfil <i data-lucide="arrow-right" class="icon-sm"></i>
        </a>
    </div>
<?php endif; ?>
