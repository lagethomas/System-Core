<?php declare(strict_types=1);
/** @var string $user_name */
/** @var int $total_users */
/** @var int $total_logs */
/** @var bool $is_admin */
?>

<div class="dashboard-header mb-8">
    <div class="flex items-center gap-4">
        <div class="header-icon-box">
            <i data-lucide="layout-dashboard"></i>
        </div>
        <div>
            <h2 class="m-0">Olá, <?php echo htmlspecialchars($user_name); ?>! 👋</h2>
            <p class="text-muted m-0">Bem-vindo ao centro de controle modular do seu sistema.</p>
        </div>
    </div>
</div>

<?php if ($is_admin): ?>
<div class="stats-grid mb-5">
    <a href="<?php echo SITE_URL; ?>/admin/users" class="stat-card-premium">
        <div class="stat-icon-box blue">
            <i data-lucide="users"></i>
        </div>
        <div class="stat-info-premium">
            <span class="stat-label-premium">Usuários</span>
            <h3 class="stat-value-premium"><?php echo $total_users; ?></h3>
        </div>
        <div class="stat-action-hint">
            <i data-lucide="arrow-up-right" class="icon-sm"></i>
        </div>
    </a>

    <a href="<?php echo SITE_URL; ?>/admin/logs" class="stat-card-premium">
        <div class="stat-icon-box green">
            <i data-lucide="activity"></i>
        </div>
        <div class="stat-info-premium">
            <span class="stat-label-premium">Atividades</span>
            <h3 class="stat-value-premium"><?php echo $total_logs; ?></h3>
        </div>
        <div class="stat-action-hint">
            <i data-lucide="arrow-up-right" class="icon-sm"></i>
        </div>
    </a>
</div>

<div class="dashboard-grid">
    <div class="dashboard-card card p-5">
        <div class="d-flex justify-content-between align-items-center mb-5">
            <h3 class="m-0 fw-800 text-main font-md">Resumo do Sistema</h3>
            <span class="status-badge status-primary">Módulo Ativo</span>
        </div>
        
        <p class="text-muted small mb-4 line-height-md">
            O sistema utiliza uma <strong>arquitetura modular</strong>. Este painel faz parte do domínio <code>Dashboard</code>, garantindo isolamento e performance.
        </p>

        <div class="info-block mb-5">
            <ul class="info-list" style="list-style: none; padding: 0;">
                <li class="flex items-center gap-3 small text-muted mb-3">
                    <i data-lucide="check-circle" class="icon-sm text-primary"></i> Controladores Modulares (Namespaces)
                </li>
                <li class="flex items-center gap-3 small text-muted mb-3">
                    <i data-lucide="check-circle" class="icon-sm text-primary"></i> Views internas por domínio
                </li>
                <li class="flex items-center gap-3 small text-muted">
                    <i data-lucide="check-circle" class="icon-sm text-primary"></i> Padronização AJAX-First
                </li>
            </ul>
        </div>

        <a href="<?php echo SITE_URL; ?>/admin/settings" class="btn-primary-glass w-100 justify-content-center">
             Gerenciar Configurações <i data-lucide="settings" class="icon-sm ml-2"></i>
        </a>
    </div>

    <div class="dashboard-card card p-5">
        <div class="flex justify-between items-center mb-5">
            <h3 class="m-0 fw-800 text-main font-md">Acesso Rápido</h3>
            <span class="status-badge status-primary">Links</span>
        </div>
        <div class="flex flex-column gap-3">
            <a href="<?php echo SITE_URL; ?>/admin/users" class="card-link-premium p-4 rounded-16 bg-card-alt flex items-center gap-3">
                <div class="header-icon-box" style="width: 40px; height: 40px; border-radius: 10px;">
                    <i data-lucide="user-plus" class="icon-sm"></i>
                </div>
                <span class="small fw-700">Gerenciar Usuários</span>
                <i data-lucide="chevron-right" class="icon-sm ml-auto opacity-30"></i>
            </a>
            <a href="<?php echo SITE_URL; ?>/profile" class="card-link-premium p-4 rounded-16 bg-card-alt flex items-center gap-3">
                <div class="header-icon-box" style="width: 40px; height: 40px; border-radius: 10px;">
                    <i data-lucide="user-circle" class="icon-sm"></i>
                </div>
                <span class="small fw-700">Meu Perfil</span>
                <i data-lucide="chevron-right" class="icon-sm ml-auto opacity-30"></i>
            </a>
            <a href="<?php echo SITE_URL; ?>/admin/logs" class="card-link-premium p-4 rounded-16 bg-card-alt flex items-center gap-3">
                <div class="header-icon-box" style="width: 40px; height: 40px; border-radius: 10px;">
                    <i data-lucide="activity" class="icon-sm"></i>
                </div>
                <span class="small fw-700">Logs do Sistema</span>
                <i data-lucide="chevron-right" class="icon-sm ml-auto opacity-30"></i>
            </a>
        </div>
    </div>
</div>
<?php else: ?>
    <div class="unauthorized-container text-center py-5">
        <div class="header-icon-box mx-auto mb-4" style="width: 80px; height: 80px; border-radius: 24px;">
            <i data-lucide="shield-check" style="width: 40px; height: 40px;"></i>
        </div>
        <h2 class="fw-800">Acesso Autorizado</h2>
        <p class="text-muted">Bem-vindo ao sistema modular. Utilize o menu lateral para gerenciar sua conta e as ferramentas disponíveis.</p>
        <a href="<?php echo SITE_URL; ?>/profile" class="btn-primary mt-4" style="padding: 12px 30px; border-radius: 12px;">
            Ver Meu Perfil <i data-lucide="arrow-right" class="icon-sm ml-2"></i>
        </a>
    </div>
<?php endif; ?>
