<?php
/** @var array $plans */
/** @var array $nonces */
/** @var array $pagination */
?>

<div class="page-header">
    <div>
        <h2 style="color: var(--primary); margin-bottom: 5px;">Pacotes de Assinatura</h2>
        <p style="color: var(--text-muted);">Defina os planos de cobrança para os seus clientes SaaS.</p>
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
                    <tr><td colspan="6" class="text-center" style="padding: 40px; color: var(--text-muted);">Nenhum pacote cadastrado.</td></tr>
                <?php else: ?>
                    <?php foreach ($plans as $p): ?>
                    <tr>
                        <td class="fw-bold text-main"><?php echo htmlspecialchars($p['name']); ?></td>
                        <td style="color: var(--primary); font-weight: 700;">R$ <?php echo number_format((float)$p['base_price'], 2, ',', '.'); ?></td>
                        <td><?php echo $p['included_users']; ?> <span class="text-muted small">incluídos</span></td>
                        <td>R$ <?php echo number_format((float)$p['extra_user_price'], 2, ',', '.'); ?></td>
                        <td>
                            <span class="badge" style="background: rgba(var(--primary-rgb), 0.1); color: var(--primary);">
                                <?php echo $p['trial_days']; ?> dias
                            </span>
                        </td>
                        <td class="text-right">
                            <button onclick='openPlanModal(<?php echo json_encode($p, JSON_HEX_APOS | JSON_HEX_QUOT); ?>)' class="btn-user-action" title="Editar"><i data-lucide="edit" class="icon-lucide"></i></button>
                            <button onclick="deletePlan(<?php echo $p['id']; ?>)" class="btn-user-action btn-user-delete" title="Remover"><i data-lucide="trash" class="icon-lucide"></i></button>
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
        <form onsubmit="savePlan(event, this)">
            <input type="hidden" name="nonce" value="<?php echo $nonces['save']; ?>">
            <input type="hidden" name="id" value="${data ? data.id : ''}">
            
            <div class="form-group mb-3">
                <label class="form-label">Nome do Pacote</label>
                <input type="text" name="name" class="form-control" value="${data ? data.name : ''}" placeholder="Ex: Bronze, Prata, Gold" required>
            </div>

            <div class="form-grid-2 mb-3">
                <div class="form-group">
                    <label class="form-label">Preço Base (R$)</label>
                    <input type="number" step="0.01" name="base_price" class="form-control" value="${data ? data.base_price : '40.00'}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Usuários Incluídos</label>
                    <input type="number" name="included_users" class="form-control" value="${data ? data.included_users : '4'}" required>
                </div>
            </div>

            <div class="form-grid-2 mb-4">
                <div class="form-group">
                    <label class="form-label">Extra / Usuário (R$)</label>
                    <input type="number" step="0.01" name="extra_user_price" class="form-control" value="${data ? data.extra_user_price : '30.00'}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Trial (Dias de Experiência)</label>
                    <input type="number" name="trial_days" class="form-control" value="${data ? data.trial_days : '7'}" required>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="UI.closeModal()">Cancelar</button>
                <button type="submit" class="btn-primary">
                    <i data-lucide="save" class="icon-lucide icon-sm mr-2"></i> ${data ? 'Salvar Alterações' : 'Criar Pacote'}
                </button>
            </div>
        </form>
    `;
    
    UI.showModal(data ? 'Editar Pacote' : 'Novo Pacote', html);
    if(window.lucide) lucide.createIcons();
}

async function savePlan(e, form) {
    e.preventDefault();
    const submitBtn = form.querySelector('button[type="submit"]');
    let originalText = '';
    
    if (submitBtn) {
        originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i data-lucide="loader" class="icon-lucide icon-spin"></i> Salvando...';
        submitBtn.disabled = true;
        if(window.lucide) lucide.createIcons();
    }

    try {
        const formData = new FormData(form);
        const response = await fetch('<?php echo SITE_URL; ?>/api/admin/plans/save', {
            method: 'POST',
            body: formData
        });
        const result = await response.json();
        
        if (result.success) {
            UI.showToast('Pacote salvo com sucesso!');
            window.location.reload();
        } else {
            UI.showToast(result.message || 'Erro ao salvar', 'error');
        }
    } catch (error) {
        UI.showToast('Erro de conexão', 'error');
    } finally {
        if (submitBtn) {
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
            if(window.lucide) lucide.createIcons();
        }
    }
}

async function deletePlan(id) {
    if (await UI.confirm('Deseja realmente excluir este pacote? Empresas associadas ficarão sem pacote definido.')) {
        try {
            const formData = new FormData();
            formData.append('id', id);
            
            const response = await fetch('<?php echo SITE_URL; ?>/api/admin/plans/delete', {
                method: 'POST',
                body: formData
            });
            const result = await response.json();
            
            if (result && result.success) {
                UI.showToast('Pacote removido');
                window.location.reload();
            } else {
                UI.showToast(result.message || 'Erro ao excluir', 'error');
            }
        } catch (error) {
            UI.showToast('Erro de conexão', 'error');
        }
    }
}
</script>
