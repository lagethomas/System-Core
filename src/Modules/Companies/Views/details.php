<?php
/** @var array $company */
/** @var array $invoices */
/** @var array $users */
$v = (string)time();
?>
<link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/modules/companies.css?v=<?php echo $v; ?>">

<div class="page-header">
    <div>
        <a href="<?php echo SITE_URL; ?>/admin/companies" class="btn-secondary details-back-link">
            <i data-lucide="arrow-left" class="icon-lucide"></i> Voltar para Empresas
        </a>
        <h2 class="details-title">
            <i data-lucide="building" class="icon-lucide"></i> <?php echo htmlspecialchars($company['name']); ?>
        </h2>
    </div>
    <div class="page-header-actions d-flex gap-2">
        <button class="btn-primary" onclick="openInvoiceModal()">
            <i data-lucide="receipt" class="icon-lucide"></i> Gerar Fatura
        </button>
        <a href="<?php echo SITE_URL; ?>/<?php echo $company['slug']; ?>" target="_blank" class="btn-secondary text-decoration-none">
            <i data-lucide="monitor" class="icon-lucide"></i> Acessar Sistema
        </a>
    </div>
</div>

<div class="details-header-card card">
    <div class="details-info-grid">
        <div class="info-group">
            <label class="details-info-label">Informações Gerais</label>
            <div class="details-info-list">
                <div class="details-info-item">
                    <i data-lucide="link" class="icon-lucide"></i>
                    <code class="company-slug-badge" onclick="UI.copyToClipboard('<?php echo SITE_URL . '/' . $company['slug']; ?>', 'Slug copiada!')" title="Clique para copiar"><?php echo htmlspecialchars($company['slug']); ?> <i data-lucide="copy" class="icon-lucide icon-xs"></i></code>
                </div>
                <div class="details-info-item">
                    <i data-lucide="mail" class="icon-lucide"></i>
                    <span><?php echo htmlspecialchars($company['email'] ?: 'Não informado'); ?></span>
                </div>
                <div class="details-info-item">
                    <i data-lucide="phone" class="icon-lucide"></i>
                    <span><?php echo htmlspecialchars($company['phone'] ?: 'Não informado'); ?></span>
                </div>
            </div>
        </div>

        <div class="info-group">
            <label class="details-info-label">Assinatura & Plano</label>
            <div class="details-info-list">
                <div class="details-info-item">
                    <i data-lucide="package-open" class="icon-lucide"></i>
                    <span class="fw-700"><?php echo htmlspecialchars($company['plan_name'] ?: 'Sem plano'); ?></span>
                </div>
                <div class="details-info-item">
                    <i data-lucide="calendar-check" class="icon-lucide"></i>
                    <span>Vencimento: 
                        <strong id="company-expires-display" class="editable-value" onclick="editCompanyExpires()">
                            <?php echo $company['expires_at'] ? date('d/m/Y', strtotime($company['expires_at'])) : 'Indefinido'; ?>
                        </strong>
                        <div id="company-expires-edit" class="edit-inline-box hidden">
                            <input type="date" id="company-expires-input" value="<?php echo $company['expires_at'] ? date('Y-m-d', strtotime($company['expires_at'])) : ''; ?>" class="edit-inline-input">
                            <button onclick="saveCompanyExpires()" class="btn-inline-save"><i data-lucide="check" class="icon-lucide"></i></button>
                            <button onclick="cancelEditCompanyExpires()" class="btn-inline-cancel"><i data-lucide="x" class="icon-lucide"></i></button>
                        </div>
                    </span>
                </div>
                <div class="details-info-item">
                    <i data-lucide="circle" class="icon-xs" style="fill: <?php echo $company['active'] ? '#22c55e' : '#ef4444'; ?>; stroke: none;"></i>
                    <span>Status: <?php echo $company['active'] ? '<span class="text-success">Ativo</span>' : '<span class="text-danger">Suspenso</span>'; ?></span>
                </div>
            </div>
        </div>

        <div class="info-group">
            <label class="details-info-label">Dados do Responsável</label>
            <div class="details-info-list">
                <?php 
                $owner = null;
                foreach($users as $u) if ($u['role'] === 'proprietario') { $owner = $u; break; }
                ?>
                <div class="details-info-item">
                    <i data-lucide="briefcase" class="icon-lucide"></i>
                    <span class="fw-700"><?php echo htmlspecialchars($owner['name'] ?? 'Não vinculado'); ?></span>
                </div>
                <?php if ($owner): ?>
                <div class="details-info-item text-muted">
                    <i data-lucide="shield" class="icon-lucide"></i>
                    <span class="font-xs">ID Usuário: #<?php echo $owner['id']; ?></span>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="form-grid-2-1 gap-lg">
    <!-- Faturas -->
    <div class="card">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="m-0 font-sm fw-800 text-main">Faturas e Cobranças</h3>
        </div>
        <div class="table-responsive">
            <table class="premium-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Vencimento</th>
                        <th>Valor</th>
                        <th>Status</th>
                        <th class="text-right">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($invoices)): ?>
                        <tr><td colspan="5" class="text-center py-5 text-muted">Transações financeiras aparecerão aqui.</td></tr>
                    <?php else: ?>
                        <?php foreach($invoices as $inv): ?>
                        <tr>
                            <td class="text-muted">#<?php echo $inv['id']; ?></td>
                            <td>
                                <?php if ($inv['status'] === 'pending'): ?>
                                    <span id="due-date-display-<?php echo $inv['id']; ?>" class="editable-value" onclick="editDueDate(<?php echo $inv['id']; ?>, '<?php echo $inv['due_date']; ?>')">
                                        <?php echo date('d/m/Y', strtotime($inv['due_date'])); ?>
                                    </span>
                                    <div id="due-date-edit-<?php echo $inv['id']; ?>" class="edit-inline-box hidden">
                                        <input type="date" id="due-date-input-<?php echo $inv['id']; ?>" value="<?php echo $inv['due_date']; ?>" class="edit-inline-input font-xs">
                                        <button onclick="saveDueDate(<?php echo $inv['id']; ?>)" class="btn-inline-save"><i data-lucide="check" class="icon-lucide"></i></button>
                                        <button onclick="cancelUpdateDate(<?php echo $inv['id']; ?>)" class="btn-inline-cancel"><i data-lucide="x" class="icon-lucide"></i></button>
                                    </div>
                                <?php else: ?>
                                    <?php echo date('d/m/Y', strtotime($inv['due_date'])); ?>
                                <?php endif; ?>
                            </td>
                            <td class="fw-800 text-primary">R$ <?php echo number_format($inv['amount'], 2, ',', '.'); ?></td>
                            <td>
                                <?php if ($inv['status'] === 'paid'): ?>
                                    <span class="badge status-active"><i data-lucide="check" class="icon-lucide"></i> Pago</span>
                                <?php else: ?>
                                    <span class="badge status-pending" title="Vencido em <?php echo date('d/m/Y', strtotime($inv['due_date'])); ?>">Pendente</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-right">
                                <div class="company-actions">
                                    <?php if ($inv['status'] === 'pending'): ?>
                                        <button class="btn-user-action text-success" onclick="markAsPaid(<?php echo $inv['id']; ?>)" title="Marcar como Pago"><i data-lucide="check-circle" class="icon-lucide"></i></button>
                                        <button class="btn-user-action btn-user-delete" onclick="deleteInvoice(<?php echo $inv['id']; ?>)" title="Excluir"><i data-lucide="trash" class="icon-lucide"></i></button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Usuários -->
    <div class="card">
        <h3 class="font-sm fw-800 text-main mb-4">Colaboradores Associados</h3>
        <div class="details-info-list gap-3">
            <?php if (empty($users)): ?>
                <p class="text-muted text-center p-4">Nenhum usuário cadastrado.</p>
            <?php else: ?>
                <?php foreach($users as $u): ?>
                <div class="colaborator-item">
                    <div class="colaborator-icon-box">
                        <i data-lucide="<?php echo $u['role'] === 'proprietario' ? 'briefcase' : 'user'; ?>" class="icon-lucide"></i>
                    </div>
                    <div class="flex-1">
                        <div class="fw-800 text-main font-sm"><?php echo htmlspecialchars($u['name']); ?></div>
                        <div class="font-xs text-muted"><?php echo htmlspecialchars($u['email']); ?></div>
                    </div>
                    <div class="text-right">
                        <span class="badge font-xs text-uppercase">
                            <?php 
                            if ($u['role'] === 'proprietario') echo 'Proprietário';
                            elseif ($u['role'] === 'vendedor') echo 'Vendedor';
                            else echo 'Usuário';
                            ?>
                        </span>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function editCompanyExpires() {
    document.getElementById('company-expires-display').classList.add('hidden');
    document.getElementById('company-expires-edit').classList.replace('hidden', 'd-flex');
}

function cancelEditCompanyExpires() {
    document.getElementById('company-expires-display').classList.remove('hidden');
    document.getElementById('company-expires-edit').classList.replace('d-flex', 'hidden');
}

async function saveCompanyExpires() {
    const newDate = document.getElementById('company-expires-input').value;
    if (!newDate) return;

    try {
        const response = await fetch('<?php echo SITE_URL; ?>/api/admin/companies/update-expiration', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `company_id=<?php echo $company['id']; ?>&expires_at=${newDate}`
        });
        const data = await response.json();
        if (data.success) {
            UI.showToast('Vencimento atualizado!');
            window.location.reload();
        } else {
            UI.showToast(data.message || 'Erro ao atualizar', 'error');
        }
    } catch (e) {
        UI.showToast('Erro de conexão', 'error');
    }
}

async function markAsPaid(id) {
    if (await UI.confirm('Deseja marcar esta fatura como paga manualmente?', {
        title: 'Baixa de Fatura',
        confirmText: 'Confirmar Pagamento',
        type: 'success',
        icon: 'check-circle'
    })) {
        const result = await UI.request('<?php echo SITE_URL; ?>/api/admin/invoices/pay', { id });
        if (result && result.success) {
            UI.showToast(result.message);
            window.location.reload();
        }
    }
}

async function deleteInvoice(id) {
    if (await UI.confirm('Remover fatura pendente?', {
        title: 'Remover Fatura',
        confirmText: 'Sim, Remover',
        type: 'danger'
    })) {
        const result = await UI.request('<?php echo SITE_URL; ?>/api/admin/invoices/delete', { id });
        if (result && result.success) {
            UI.showToast(result.message);
            window.location.reload();
        }
    }
}

function editDueDate(id) {
    document.getElementById(`due-date-display-${id}`).classList.add('hidden');
    document.getElementById(`due-date-edit-${id}`).classList.replace('hidden', 'd-flex');
}

function cancelUpdateDate(id) {
    document.getElementById(`due-date-display-${id}`).classList.remove('hidden');
    document.getElementById(`due-date-edit-${id}`).classList.replace('d-flex', 'hidden');
}

async function saveDueDate(id) {
    const newDate = document.getElementById(`due-date-input-${id}`).value;
    if (!newDate) return;

    try {
        const response = await fetch('<?php echo SITE_URL; ?>/api/admin/invoices/update-date', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `invoice_id=${id}&due_date=${newDate}`
        });
        const data = await response.json();
        if (data.success) {
            UI.showToast('Data atualizada!');
            window.location.reload();
        } else {
            UI.showToast(data.message || 'Erro ao atualizar', 'error');
        }
    } catch (e) {
        UI.showToast('Erro de conexão', 'error');
    }
}

function openInvoiceModal() {
    const html = `
        <form onsubmit="generateInvoice(event, this)">
            <input type="hidden" name="company_id" value="<?php echo $company['id']; ?>">
            
            <div class="floating-group">
                <input type="text" name="description" class="form-control" placeholder=" " required>
                <label class="floating-label">Descrição (Ex: Mensalidade)</label>
            </div>

            <div class="form-grid-2 mt-4">
                <div class="floating-group">
                    <input type="number" step="0.01" name="amount" class="form-control" placeholder=" " required>
                    <label class="floating-label">Valor (R$)</label>
                </div>
                <div class="floating-group">
                    <input type="date" name="due_date" class="form-control" value="<?php echo date('Y-m-d', strtotime('+3 days')); ?>" required placeholder=" ">
                    <label class="floating-label">Vencimento</label>
                </div>
            </div>

            <div class="floating-group mt-4">
                <select name="type" class="form-control" placeholder=" ">
                    <option value="recurring">Mensalidade (Recorrente)</option>
                    <option value="single">Pagamento Único / Avulso</option>
                </select>
                <label class="floating-label">Tipo de Lançamento</label>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-dark" onclick="UI.closeModal()">Cancelar</button>
                <button type="submit" class="btn-primary">Gerar Fatura Agora</button>
            </div>
        </form>
    `;
    UI.showModal('Gerar Fatura Manual', html);
    if(window.lucide) lucide.createIcons();
}

async function generateInvoice(e, form) {
    e.preventDefault();
    try {
        const formData = new FormData(form);
        const response = await fetch('<?php echo SITE_URL; ?>/api/admin/invoices/generate', {
            method: 'POST',
            body: formData
        });
        const result = await response.json();
        if (result.success) {
            UI.showToast('Fatura gerada com sucesso!');
            window.location.reload();
        } else {
            UI.showToast(result.message || 'Erro ao gerar', 'error');
        }
    } catch (error) {
        UI.showToast('Erro de conexão', 'error');
    }
}
</script>
