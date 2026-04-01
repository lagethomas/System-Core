<?php
/** @var array $company */
/** @var array $invoices */
/** @var array $users */
?>

<div class="page-header" style="margin-bottom: 25px;">
    <div>
        <a href="<?php echo SITE_URL; ?>/admin/companies" class="btn-secondary" style="margin-bottom: 15px; display: inline-flex; align-items: center; gap: 8px; text-decoration: none; padding: 8px 15px; border-radius: 8px; font-size: 13px;">
            <i data-lucide="arrow-left" class="icon-lucide"></i> Voltar para Empresas
        </a>
        <h2 style="color: var(--primary); margin: 0; display: flex; align-items: center; gap: 12px;">
            <i data-lucide="building" class="icon-lucide"></i> <?php echo htmlspecialchars($company['name']); ?>
        </h2>
    </div>
    <div class="page-header-actions" style="display: flex; gap: 10px;">
        <button class="btn-primary" onclick="openInvoiceModal()" style="padding: 10px 20px; border-radius: 10px;">
            <i data-lucide="receipt" class="icon-lucide"></i> Gerar Fatura
        </button>
        <a href="<?php echo SITE_URL; ?>/<?php echo $company['slug']; ?>" target="_blank" class="btn-secondary" style="padding: 10px 20px; border-radius: 10px; text-decoration: none;">
            <i data-lucide="monitor" class="icon-lucide"></i> Acessar Sistema
        </a>
    </div>
</div>

<div class="profile-header-card card" style="background: linear-gradient(135deg, rgba(var(--primary-rgb), 0.05) 0%, rgba(0,0,0,0) 100%); border: 1px solid rgba(var(--primary-rgb), 0.1); margin-bottom: 30px; padding: 30px;">
    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 40px;">
        <div class="info-group">
            <label style="display: block; font-size: 11px; text-transform: uppercase; letter-spacing: 1px; color: var(--text-muted); margin-bottom: 10px;">Informações Gerais</label>
            <div style="display: flex; flex-direction: column; gap: 12px;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <i data-lucide="link" class="icon-lucide"></i>
                    <code style="background: rgba(var(--primary-rgb), 0.1); color: var(--primary); padding: 4px 10px; border-radius: 6px; font-size: 12px; cursor: pointer;" onclick="UI.copyToClipboard('<?php echo SITE_URL . '/' . $company['slug']; ?>', 'Slug copiada!')" title="Clique para copiar"><?php echo htmlspecialchars($company['slug']); ?> <i data-lucide="copy" class="icon-lucide"></i></code>
                </div>
                <div style="display: flex; align-items: center; gap: 10px; color: var(--text-main);">
                    <i data-lucide="mail" class="icon-lucide"></i>
                    <span style="font-size: 14px;"><?php echo htmlspecialchars($company['email'] ?: 'Não informado'); ?></span>
                </div>
                <div style="display: flex; align-items: center; gap: 10px; color: var(--text-main);">
                    <i data-lucide="phone" class="icon-lucide"></i>
                    <span style="font-size: 14px;"><?php echo htmlspecialchars($company['phone'] ?: 'Não informado'); ?></span>
                </div>
            </div>
        </div>

        <div class="info-group">
            <label style="display: block; font-size: 11px; text-transform: uppercase; letter-spacing: 1px; color: var(--text-muted); margin-bottom: 10px;">Assinatura & Plano</label>
            <div style="display: flex; flex-direction: column; gap: 12px;">
                <div style="display: flex; align-items: center; gap: 10px; color: var(--text-main);">
                    <i data-lucide="package-open" class="icon-lucide"></i>
                    <span style="font-weight: 700;"><?php echo htmlspecialchars($company['plan_name'] ?: 'Sem plano'); ?></span>
                </div>
                <div style="display: flex; align-items: center; gap: 10px; color: var(--text-main);">
                    <i data-lucide="calendar-check" class="icon-lucide"></i>
                    <span>Vencimento: 
                        <strong id="company-expires-display" style="color: var(--primary); cursor: pointer; border-bottom: 1px dotted var(--primary);" onclick="editCompanyExpires()">
                            <?php echo $company['expires_at'] ? date('d/m/Y', strtotime($company['expires_at'])) : 'Indefinido'; ?>
                        </strong>
                        <div id="company-expires-edit" style="display: none; align-items: center; gap: 5px; margin-top: 5px;">
                            <input type="date" id="company-expires-input" value="<?php echo $company['expires_at'] ? date('Y-m-d', strtotime($company['expires_at'])) : ''; ?>" style="padding: 2px 5px; border-radius: 5px; background: #1a1c23; border: 1px solid var(--border); color: white; font-size: 13px;">
                            <button onclick="saveCompanyExpires()" style="background: var(--primary); border: none; border-radius: 4px; padding: 2px 6px; cursor: pointer; color: black; font-size: 10px;"><i data-lucide="check" class="icon-lucide"></i></button>
                            <button onclick="cancelEditCompanyExpires()" style="background: rgba(255,255,255,0.05); border: none; border-radius: 4px; padding: 2px 6px; cursor: pointer; color: white; font-size: 10px;"><i data-lucide="x" class="icon-lucide"></i></button>
                        </div>
                    </span>
                </div>
                <div style="display: flex; align-items: center; gap: 10px; color: var(--text-main);">
                    <i data-lucide="circle" style="width: 12px; height: 12px; fill: <?php echo $company['active'] ? '#22c55e' : '#ef4444'; ?>; stroke: none;"></i>
                    <span>Status: <?php echo $company['active'] ? '<span style="color: #22c55e;">Ativo</span>' : '<span style="color: #ef4444;">Suspenso</span>'; ?></span>
                </div>
            </div>
        </div>

        <div class="info-group">
            <label style="display: block; font-size: 11px; text-transform: uppercase; letter-spacing: 1px; color: var(--text-muted); margin-bottom: 10px;">Dados do Responsável</label>
            <div style="display: flex; flex-direction: column; gap: 12px;">
                <?php 
                $owner = null;
                foreach($users as $u) if ($u['role'] === 'proprietario') { $owner = $u; break; }
                ?>
                <div style="display: flex; align-items: center; gap: 10px; color: var(--text-main);">
                    <i data-lucide="briefcase" class="icon-lucide"></i>
                    <span style="font-weight: 600;"><?php echo htmlspecialchars($owner['name'] ?? 'Não vinculado'); ?></span>
                </div>
                <?php if ($owner): ?>
                <div style="display: flex; align-items: center; gap: 10px; color: var(--text-muted);">
                    <i data-lucide="shield" class="icon-lucide"></i>
                    <span style="font-size: 13px;">ID Usuário: #<?php echo $owner['id']; ?></span>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 30px;">
    <!-- Faturas -->
    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
            <h3 style="margin: 0; font-size: 18px; color: var(--text-main);">Faturas e Cobranças</h3>
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
                                    <span id="due-date-display-<?php echo $inv['id']; ?>" class="due-date-dotted" onclick="editDueDate(<?php echo $inv['id']; ?>, '<?php echo $inv['due_date']; ?>')" style="cursor: pointer; border-bottom: 1px dotted var(--text-muted);">
                                        <?php echo date('d/m/Y', strtotime($inv['due_date'])); ?>
                                    </span>
                                    <div id="due-date-edit-<?php echo $inv['id']; ?>" style="display: none; align-items: center; gap: 5px;">
                                        <input type="date" id="due-date-input-<?php echo $inv['id']; ?>" value="<?php echo $inv['due_date']; ?>" style="padding: 2px 5px; border-radius: 5px; background: #1a1c23; border: 1px solid var(--border); color: white; font-size: 12px;">
                                        <button onclick="saveDueDate(<?php echo $inv['id']; ?>)" style="background: var(--primary); border: none; border-radius: 4px; padding: 2px 6px; cursor: pointer; color: black; font-size: 10px;"><i data-lucide="check" class="icon-lucide"></i></button>
                                        <button onclick="cancelUpdateDate(<?php echo $inv['id']; ?>)" style="background: rgba(255,255,255,0.05); border: none; border-radius: 4px; padding: 2px 6px; cursor: pointer; color: white; font-size: 10px;"><i data-lucide="x" class="icon-lucide"></i></button>
                                    </div>
                                <?php else: ?>
                                    <?php echo date('d/m/Y', strtotime($inv['due_date'])); ?>
                                <?php endif; ?>
                            </td>
                            <td style="font-weight: 700; color: var(--primary);">R$ <?php echo number_format($inv['amount'], 2, ',', '.'); ?></td>
                            <td>
                                <?php if ($inv['status'] === 'paid'): ?>
                                    <span class="badge status-active"><i data-lucide="check" class="icon-lucide"></i> Pago</span>
                                <?php else: ?>
                                    <span class="badge status-pending" title="Vencido em <?php echo date('d/m/Y', strtotime($inv['due_date'])); ?>">Pendente</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-right">
                                <?php if ($inv['status'] === 'pending'): ?>
                                    <button class="btn-user-action" style="color: #22c55e;" onclick="markAsPaid(<?php echo $inv['id']; ?>)" title="Marcar como Pago"><i data-lucide="check-circle" class="icon-lucide"></i></button>
                                    <button class="btn-user-action btn-user-delete" onclick="deleteInvoice(<?php echo $inv['id']; ?>)" title="Excluir"><i data-lucide="trash" class="icon-lucide"></i></button>
                                <?php endif; ?>
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
        <h3 style="margin-bottom: 25px; font-size: 18px; color: var(--text-main);">Colaboradores Associados</h3>
        <div style="display: flex; flex-direction: column; gap: 15px;">
            <?php if (empty($users)): ?>
                <p class="text-muted text-center" style="padding: 20px;">Nenhum usuário cadastrado.</p>
            <?php else: ?>
                <?php foreach($users as $u): ?>
                <div style="background: rgba(255,255,255,0.02); padding: 15px; border-radius: 12px; border: 1px solid var(--border); display: flex; align-items: center; gap: 15px;">
                    <div style="width: 40px; height: 40px; background: rgba(var(--primary-rgb), 0.1); border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 18px; color: var(--primary);">
                        <i data-lucide="<?php echo $u['role'] === 'proprietario' ? 'briefcase' : 'user'; ?>" class="icon-lucide"></i>
                    </div>
                    <div style="flex: 1;">
                        <div style="font-weight: 600; color: var(--text-main); font-size: 14px;"><?php echo htmlspecialchars($u['name']); ?></div>
                        <div style="font-size: 12px; color: var(--text-muted);"><?php echo htmlspecialchars($u['email']); ?></div>
                    </div>
                    <div style="text-align: right;">
                        <span class="badge" style="font-size: 9px; text-transform: uppercase;">
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
    document.getElementById('company-expires-display').style.display = 'none';
    document.getElementById('company-expires-edit').style.display = 'flex';
}

function cancelEditCompanyExpires() {
    document.getElementById('company-expires-display').style.display = 'inline';
    document.getElementById('company-expires-edit').style.display = 'none';
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
    document.getElementById(`due-date-display-${id}`).style.display = 'none';
    document.getElementById(`due-date-edit-${id}`).style.display = 'flex';
}

function cancelUpdateDate(id) {
    document.getElementById(`due-date-display-${id}`).style.display = 'inline';
    document.getElementById(`due-date-edit-${id}`).style.display = 'none';
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
            
            <div class="form-group mb-3">
                <label class="form-label">Descrição</label>
                <input type="text" name="description" class="form-control" placeholder="Ex: Mensalidade SaaS, Taxa de Adesão..." required>
            </div>

            <div class="form-grid-2 mb-3">
                <div class="form-group">
                    <label class="form-label">Valor (R$)</label>
                    <input type="number" step="0.01" name="amount" class="form-control" placeholder="0.00" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Vencimento</label>
                    <input type="date" name="due_date" class="form-control" value="<?php echo date('Y-m-d', strtotime('+3 days')); ?>" required>
                </div>
            </div>

            <div class="form-group mb-3">
                <label class="form-label">Tipo de Lançamento</label>
                <select name="type" class="form-control">
                    <option value="recurring">Mensalidade (Recorrente)</option>
                    <option value="single">Pagamento Único / Avulso</option>
                </select>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="UI.closeModal()">Cancelar</button>
                <button type="submit" class="btn-primary">Gerar Fatura</button>
            </div>
        </form>
    `;
    UI.showModal('Nova Fatura Manual', html);
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
