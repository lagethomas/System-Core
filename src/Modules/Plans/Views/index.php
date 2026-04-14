<?php
/** @var array $plans */
/** @var array $nonces */
/** @var array $pagination */
$v = (string)time();
?>
<link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/modules/plans.css?v=<?php echo $v; ?>">

<div class="page-header">
    <div>
        <h2 class="text-primary m-0 fw-800">Pacotes de Assinatura</h2>
        <p class="text-muted small">Defina os planos de cobrança para os seus clientes SaaS.</p>
    </div>
    <div class="page-header-actions">
        <button class="btn-primary" onclick="openPlanModal()">
            <i data-lucide="plus" class="icon-lucide"></i> Novo Pacote
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
                    <tr><td colspan="6" class="text-center py-5 text-muted">Nenhum pacote cadastrado.</td></tr>
                <?php else: ?>
                    <?php foreach ($plans as $p): ?>
                    <tr>
                        <td class="plan-name-cell"><?php echo htmlspecialchars($p['name']); ?></td>
                        <td class="plan-pricing">R$ <?php echo number_format((float)$p['base_price'], 2, ',', '.'); ?></td>
                        <td><?php echo $p['included_users']; ?> <span class="text-muted small">incluídos</span></td>
                        <td class="text-muted">R$ <?php echo number_format((float)$p['extra_user_price'], 2, ',', '.'); ?></td>
                        <td>
                            <span class="plan-badge">
                                <i data-lucide="clock"></i> <?php echo $p['trial_days']; ?> dias
                            </span>
                        </td>
                        <td class="text-right">
                            <div class="company-actions">
                                <button onclick='openPlanModal(<?php echo json_encode($p, JSON_HEX_APOS | JSON_HEX_QUOT); ?>)' class="btn-user-action" title="Editar"><i data-lucide="edit" class="icon-lucide"></i></button>
                                <button onclick="deletePlan(<?php echo $p['id']; ?>)" class="btn-user-action btn-user-delete" title="Remover"><i data-lucide="trash" class="icon-lucide"></i></button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php echo \App\Core\Pagination::render($pagination['page'], $pagination['totalPages'], SITE_URL . '/admin/plans', $pagination['totalItems'], $pagination['limit']); ?>
</div>

<script>
function openPlanModal(data = null) {
    const html = `
        <form action="<?php echo SITE_URL; ?>/api/admin/plans/save" method="POST" class="ajax-form" data-no-reload="true">
            <input type="hidden" name="nonce" value="<?php echo $nonces['save']; ?>">
            <input type="hidden" name="id" value="${data ? data.id : ''}">
            
            <div class="floating-group">
                <input type="text" name="name" class="form-control" value="${data ? data.name : ''}" placeholder=" " required>
                <label class="floating-label">Nome do Pacote (Ex: Bronze, Prata, Gold)</label>
            </div>

            <div class="form-grid-2 mt-4">
                <div class="floating-group">
                    <input type="number" step="0.01" name="base_price" class="form-control" value="${data ? data.base_price : '40.00'}" required placeholder=" ">
                    <label class="floating-label">Preço Base (R$)</label>
                </div>
                <div class="floating-group">
                    <input type="number" name="included_users" class="form-control" value="${data ? data.included_users : '4'}" required placeholder=" ">
                    <label class="floating-label">Usuários Incluídos</label>
                </div>
            </div>

            <div class="form-grid-2 mt-4">
                <div class="floating-group">
                    <input type="number" step="0.01" name="extra_user_price" class="form-control" value="${data ? data.extra_user_price : '30.00'}" required placeholder=" ">
                    <label class="floating-label">Extra / Usuário (R$)</label>
                </div>
                <div class="floating-group">
                    <input type="number" name="trial_days" class="form-control" value="${data ? data.trial_days : '7'}" required placeholder=" ">
                    <label class="floating-label">Trial (Dias de Experiência)</label>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-dark" onclick="UI.closeModal()">Cancelar</button>
                <button type="submit" class="btn-primary">
                    <i data-lucide="save" class="icon-lucide icon-sm mr-2"></i> ${data ? 'Salvar Alterações' : 'Criar Pacote Agora'}
                </button>
            </div>
        </form>
    `;
    
    UI.showModal(data ? 'Editar Pacote' : 'Novo Pacote', html);
    if(window.lucide) lucide.createIcons();
}

document.addEventListener('ajaxSuccess', (e) => {
    if (e.target.action.includes('/api/admin/plans/save')) {
        setTimeout(() => window.location.reload(), 800);
    }
});

async function deletePlan(id) {
    if (await UI.confirm('Deseja realmente excluir este pacote? Empresas associadas ficarão sem pacote definido.')) {
        try {
            const formData = new FormData();
            formData.append('id', id);
            formData.append('nonce', '<?php echo $nonces['delete']; ?>');
            
            const result = await UI.request('<?php echo SITE_URL; ?>/api/admin/plans/delete', formData);
            
            if (result && result.success) {
                const row = document.querySelector(`button[onclick*="deletePlan(${id})"]`)?.closest('tr');
                if (row) {
                    row.style.transition = 'all 0.4s ease';
                    row.style.opacity = '0';
                    row.style.transform = 'translateX(20px)';
                    setTimeout(() => row.remove(), 400);
                }
            }
        } catch (error) {}
    }
}
</script>
