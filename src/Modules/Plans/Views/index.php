<?php declare(strict_types=1);
/** @var array $plans */
/** @var array $nonces */
/** @var array $pagination */
?>

<div class="page-header">
    <div class="flex items-center gap-4">
        <div class="header-icon-box md">
            <i data-lucide="package" class="icon-md"></i>
        </div>
        <div>
            <h2 class="m-0">Pacotes de Assinatura</h2>
            <p class="text-muted m-0">Defina os planos de cobrança para os seus clientes SaaS.</p>
        </div>
    </div>
    <div class="page-header-actions">
        <button class="btn-primary btn-premium" onclick="openPlanModal()">
            <i data-lucide="plus" class="icon-sm"></i> Novo Pacote
        </button>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="premium-table">
            <thead>
                <tr>
                    <th>Plano</th>
                    <th>Preço Base</th>
                    <th>Usuários</th>
                    <th>Extra/Usuário</th>
                    <th>Trial</th>
                    <th class="text-right">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($plans)): ?>
                    <tr><td colspan="6" class="no-results">Nenhum pacote cadastrado.</td></tr>
                <?php else: ?>
                    <?php foreach ($plans as $p): ?>
                    <tr>
                        <td class="fw-800 text-main"><?php echo htmlspecialchars($p['name']); ?></td>
                        <td class="fw-800 text-primary font-sm">R$ <?php echo number_format((float)$p['base_price'], 2, ',', '.'); ?></td>
                        <td><?php echo $p['included_users']; ?> <span class="text-muted small">incluídos</span></td>
                        <td class="text-muted small">R$ <?php echo number_format((float)$p['extra_user_price'], 2, ',', '.'); ?></td>
                        <td>
                            <span class="status-badge status-primary">
                                <i data-lucide="clock" class="icon-xs mr-1"></i> <?php echo $p['trial_days']; ?> dias
                            </span>
                        </td>
                        <td class="text-right">
                            <div class="flex justify-end gap-2">
                                <button onclick='openPlanModal(<?php echo json_encode($p, JSON_HEX_APOS | JSON_HEX_QUOT); ?>)' class="btn-user-action" title="Editar"><i data-lucide="edit" class="icon-sm"></i></button>
                                <button onclick="deletePlan(<?php echo $p['id']; ?>)" class="btn-user-action btn-user-delete" title="Remover"><i data-lucide="trash-2" class="icon-sm"></i></button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <div class="mt-4 p-5 pt-0">
        <?php echo \App\Core\Pagination::render($pagination['page'], $pagination['totalPages'], SITE_URL . '/admin/plans', $pagination['totalItems'], $pagination['limit']); ?>
    </div>
</div>

<script>
    window.PLANS_DATA = {
        nonces: <?php echo json_encode($nonces); ?>
    };
</script>
<script src="<?php echo SITE_URL; ?>/assets/js/modules/plans.js"></script>
