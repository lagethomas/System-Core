<?php
/** @var string $user_name */
/** @var int $total_users */
/** @var int $total_logs */
/** @var PDO $pdo */
?>


<div class="welcome-header" style="margin-bottom: 30px; background: linear-gradient(135deg, var(--primary) 0%, #fff 300%); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
    <h1 style="font-size: 32px; font-weight: 800; margin-bottom: 5px;">Olá, <?php echo htmlspecialchars($user_name); ?>! 👋</h1>
    <p style="color: var(--text-muted); font-size: 16px; -webkit-text-fill-color: var(--text-muted);">Bem-vindo ao centro de controle do seu novo sistema.</p>
</div>

<div class="welcome-container mb-5">
    <div class="row align-items-center">
        <div class="col-md-4 text-md-right">
            <div class="date-badge">
                <i data-lucide="calendar" class="icon-lucide icon-sm mr-2"></i>
                <?php echo date('d \d\e F, Y'); ?>
            </div>
        </div>
    </div>
</div>

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
            <i data-lucide="terminal" class="icon-lucide"></i>
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
            <h3 class="m-0 fw-800" style="font-size: 17px; color: var(--text-main);">Resumo do Sistema</h3>
            <span class="badge badge-primary-lite">Info</span>
        </div>
        <p class="text-muted small mb-4" style="line-height: 1.6;">
            Este é o seu sistema base **SaaSFlow Core**. Ele foi limpo e otimizado para servir como ponto de partida para novos projetos.
        </p>
        <div class="info-block mb-4">
            <ul class="m-0 p-0" style="list-style: none; display: flex; flex-direction: column; gap: 8px;">
                <li class="d-flex align-items-center gap-2 small text-muted">
                    <i data-lucide="check-circle-2" class="icon-lucide icon-xs text-primary"></i> Estrutura de pastas profissional
                </li>
                <li class="d-flex align-items-center gap-2 small text-muted">
                    <i data-lucide="check-circle-2" class="icon-lucide icon-xs text-primary"></i> Autenticação segura e ACL
                </li>
                <li class="d-flex align-items-center gap-2 small text-muted">
                    <i data-lucide="check-circle-2" class="icon-lucide icon-xs text-primary"></i> Gerenciamento de usuários
                </li>
                <li class="d-flex align-items-center gap-2 small text-muted">
                    <i data-lucide="check-circle-2" class="icon-lucide icon-xs text-primary"></i> Logs globais de atividades
                </li>
                <li class="d-flex align-items-center gap-2 small text-muted">
                    <i data-lucide="check-circle-2" class="icon-lucide icon-xs text-primary"></i> Sistema de temas dinâmicos
                </li>
            </ul>
        </div>
        <a href="<?php echo SITE_URL; ?>/admin/settings" class="btn-primary-glass">
            Configurar Sistema <i data-lucide="settings" class="icon-sm"></i>
        </a>
    </div>

    <div class="dashboard-card">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="m-0 fw-800" style="font-size: 17px; color: var(--text-main);">Links Rápidos</h3>
            <span class="badge badge-primary-lite">Ações</span>
        </div>
        <div style="display: grid; grid-template-columns: 1fr; gap: 12px;">
            <a href="<?php echo SITE_URL; ?>/admin/users" class="card-link-premium p-3">
                <div class="d-flex align-items-center gap-3 w-100">
                    <div class="icon-box-lite"><i data-lucide="user-plus" class="icon-sm"></i></div>
                    <span class="small fw-700">Gerenciar Usuários</span>
                    <i data-lucide="chevron-right" class="icon-lucide icon-xs ml-auto"></i>
                </div>
            </a>
            <a href="<?php echo SITE_URL; ?>/profile" class="card-link-premium p-3">
                <div class="d-flex align-items-center gap-3 w-100">
                    <div class="icon-box-lite"><i data-lucide="user-circle" class="icon-sm"></i></div>
                    <span class="small fw-700">Meu Perfil</span>
                    <i data-lucide="chevron-right" class="icon-lucide icon-xs ml-auto"></i>
                </div>
            </a>
            <a href="<?php echo SITE_URL; ?>/admin/logs" class="card-link-premium p-3">
                <div class="d-flex align-items-center gap-3 w-100">
                    <div class="icon-box-lite"><i data-lucide="list" class="icon-sm"></i></div>
                    <span class="small fw-700">Ver Logs Globais</span>
                    <i data-lucide="chevron-right" class="icon-lucide icon-xs ml-auto"></i>
                </div>
            </a>
        </div>
    </div>
</div>

<style>
.icon-box-lite {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    background: rgba(var(--primary-rgb), 0.1);
    color: var(--primary);
    display: flex;
    align-items: center;
    justify-content: center;
}
.card-link-premium {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: 12px;
    text-decoration: none;
    color: var(--text-main);
    transition: var(--transition-smooth);
    display: flex;
    align-items: center;
}
.card-link-premium:hover {
    transform: translateX(5px);
    border-color: var(--primary);
    background: rgba(var(--primary-rgb), 0.02);
}
</style>


