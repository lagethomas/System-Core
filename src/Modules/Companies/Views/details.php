<?php declare(strict_types=1);
/** @var array $company */
/** @var array $invoices */
/** @var array $users */
?>

<div class="page-header">
    <div>
        <a href="<?php echo SITE_URL; ?>/admin/companies" class="btn-dark btn-premium font-sm mb-3">
            <i data-lucide="arrow-left" class="icon-sm mr-2"></i> Voltar para Empresas
        </a>
        <h2 class="m-0">
            <i data-lucide="building" class="icon-md mr-2"></i> <?php echo htmlspecialchars($company['name']); ?>
        </h2>
    </div>
    <div class="page-header-actions flex gap-2">
        <button class="btn-primary btn-premium" onclick="openInvoiceModal()">
            <i data-lucide="receipt" class="icon-sm"></i> Gerar Fatura
        </button>
        <a href="<?php echo SITE_URL; ?>/<?php echo $company['slug']; ?>" target="_blank" class="btn-dark btn-premium">
            <i data-lucide="monitor" class="icon-sm"></i> Acessar Sistema
        </a>
    </div>
</div>

<div class="details-header-card card">
    <div class="details-info-grid">
        <div class="info-group">
            <label class="details-info-label">Informações Gerais</label>
            <div class="details-info-list">
                <div class="details-info-item">
                    <i data-lucide="link" class="icon-sm text-primary"></i>
                    <code class="company-slug-badge" onclick="UI.copyToClipboard('<?php echo SITE_URL . '/' . $company['slug']; ?>', 'Slug copiada!')" title="Clique para copiar"><?php echo htmlspecialchars($company['slug']); ?> <i data-lucide="copy" class="icon-xs ml-1 opacity-50"></i></code>
                </div>
                <div class="details-info-item">
                    <i data-lucide="mail" class="icon-sm text-primary"></i>
                    <span><?php echo htmlspecialchars($company['email'] ?: 'Não informado'); ?></span>
                </div>
                <div class="details-info-item">
                    <i data-lucide="phone" class="icon-sm text-primary"></i>
                    <span><?php echo htmlspecialchars($company['phone'] ?: 'Não informado'); ?></span>
                </div>
            </div>
        </div>

        <div class="info-group">
            <label class="details-info-label">Assinatura & Plano</label>
            <div class="details-info-list">
                <div class="details-info-item">
                    <i data-lucide="package-open" class="icon-sm text-primary"></i>
                    <span class="fw-700"><?php echo htmlspecialchars($company['plan_name'] ?: 'Sem plano'); ?></span>
                </div>
                <div class="details-info-item">
                    <i data-lucide="calendar-check" class="icon-sm text-primary"></i>
                    <span>Vencimento: 
                        <strong id="company-expires-display" class="editable-value" onclick="editCompanyExpires()">
                            <?php echo $company['expires_at'] ? date('d/m/Y', strtotime($company['expires_at'])) : 'Indefinido'; ?>
                        </strong>
                        <div id="company-expires-edit" class="edit-inline-box hidden">
                            <input type="date" id="company-expires-input" value="<?php echo $company['expires_at'] ? date('Y-m-d', strtotime($company['expires_at'])) : ''; ?>" class="edit-inline-input">
                            <button onclick="saveCompanyExpires()" class="btn-inline-save"><i data-lucide="check" class="icon-xs"></i></button>
                            <button onclick="cancelEditCompanyExpires()" class="btn-inline-cancel"><i data-lucide="x" class="icon-xs"></i></button>
                        </div>
                    </span>
                </div>
                <div class="details-info-item">
                    <i data-lucide="circle" class="icon-xs <?php echo $company['active'] ? 'text-success' : 'text-danger'; ?>"></i>
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
                    <i data-lucide="user" class="icon-sm text-primary"></i>
                    <span class="fw-700"><?php echo htmlspecialchars($owner['name'] ?? 'Não vinculado'); ?></span>
                </div>
                <?php if ($owner): ?>
                <div class="details-info-item text-muted">
                    <i data-lucide="shield" class="icon-xs opacity-50"></i>
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
        <div class="card-header flex justify-between items-center mb-4">
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
                            <td class="text-muted small">#<?php echo $inv['id']; ?></td>
                            <td>
                                <?php if ($inv['status'] === 'pending'): ?>
                                    <span id="due-date-display-<?php echo $inv['id']; ?>" class="editable-value" onclick="editDueDate(<?php echo $inv['id']; ?>, '<?php echo $inv['due_date']; ?>')">
                                        <?php echo date('d/m/Y', strtotime($inv['due_date'])); ?>
                                    </span>
                                    <div id="due-date-edit-<?php echo $inv['id']; ?>" class="edit-inline-box hidden">
                                        <input type="date" id="due-date-input-<?php echo $inv['id']; ?>" value="<?php echo $inv['due_date']; ?>" class="edit-inline-input font-xs">
                                        <button onclick="saveDueDate(<?php echo $inv['id']; ?>)" class="btn-inline-save"><i data-lucide="check" class="icon-xs"></i></button>
                                        <button onclick="cancelUpdateDate(<?php echo $inv['id']; ?>)" class="btn-inline-cancel"><i data-lucide="x" class="icon-xs"></i></button>
                                    </div>
                                <?php else: ?>
                                    <?php echo date('d/m/Y', strtotime($inv['due_date'])); ?>
                                <?php endif; ?>
                            </td>
                            <td class="fw-800 text-primary">R$ <?php echo number_format($inv['amount'], 2, ',', '.'); ?></td>
                            <td>
                                <?php if ($inv['status'] === 'paid'): ?>
                                    <span class="status-badge status-success">Pago</span>
                                <?php else: ?>
                                    <span class="status-badge status-warning" title="Vencido em <?php echo date('d/m/Y', strtotime($inv['due_date'])); ?>">Pendente</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-right">
                                <div class="flex justify-end gap-2">
                                    <?php if ($inv['status'] === 'pending'): ?>
                                        <button class="btn-user-action text-success" onclick="markAsPaid(<?php echo $inv['id']; ?>)" title="Marcar como Pago"><i data-lucide="check-circle" class="icon-sm"></i></button>
                                        <button class="btn-user-action btn-user-delete" onclick="deleteInvoice(<?php echo $inv['id']; ?>)" title="Excluir"><i data-lucide="trash-2" class="icon-sm"></i></button>
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
        <h3 class="font-sm fw-800 text-main mb-4 px-4 pt-4">Colaboradores Associados</h3>
        <div class="details-info-list gap-3 p-4">
            <?php if (empty($users)): ?>
                <p class="text-muted text-center p-4">Nenhum usuário cadastrado.</p>
            <?php else: ?>
                <?php foreach($users as $u): ?>
                <div class="colaborator-item">
                    <div class="colaborator-icon-box">
                        <i data-lucide="<?php echo $u['role'] === 'proprietario' ? 'briefcase' : 'user'; ?>" class="icon-sm"></i>
                    </div>
                    <div class="flex-1">
                        <div class="fw-800 text-main font-sm"><?php echo htmlspecialchars($u['name']); ?></div>
                        <div class="font-xs text-muted"><?php echo htmlspecialchars($u['email']); ?></div>
                    </div>
                    <div class="text-right">
                        <span class="status-badge status-primary font-xs">
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
    window.COMPANIES_DATA = {
        companyId: <?php echo $company['id']; ?>
    };
</script>
<script src="<?php echo SITE_URL; ?>/assets/js/modules/companies.js"></script>
