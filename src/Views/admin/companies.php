<?php
/** @var array $companies */
/** @var array $plans */
/** @var array $partners */
/** @var array $nonces */
?>

<div class="page-header">
    <div>
        <h2 style="color: var(--primary); margin-bottom: 5px;">Empresas Clientes</h2>
        <p style="color: var(--text-muted);">Administre as unidades e negócios cadastrados no SaaS.</p>
    </div>
    <div class="page-header-actions">
        <button class="btn-primary" onclick="openCompanyModal()">
            <i data-lucide="plus" class="icon-lucide"></i> Nova Empresa
        </button>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="premium-table">
            <thead>
                <tr>
                    <th>Empresa</th>
                    <th>Slug (URL)</th>
                    <th>Plano</th>
                    <th>Vencimento</th>
                    <th>Status</th>
                    <th class="text-right">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($companies)): ?>
                    <tr><td colspan="6" class="text-center" style="padding: 40px; color: var(--text-muted);">Nenhuma unidade cadastrada.</td></tr>
                <?php else: ?>
                    <?php foreach ($companies as $c): ?>
                    <tr>
                        <td>
                            <div style="font-weight: 700; color: var(--text-main);"><?php echo htmlspecialchars($c['name']); ?></div>
                            <div style="font-size: 11px; color: var(--text-muted);"><?php echo htmlspecialchars($c['email'] ?: 'Sem e-mail'); ?></div>
                        </td>
                        <td>
                            <div style="display: flex; flex-direction: column; gap: 4px;">
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <code id="slug-<?php echo $c['id']; ?>" style="background: rgba(255,255,255,0.05); padding: 2px 6px; border-radius: 4px; font-size: 11px; cursor: pointer;" onclick="UI.copyToClipboard('<?php echo SITE_URL . '/' . $c['slug']; ?>')"><?php echo htmlspecialchars($c['slug']); ?></code>
                                    <button class="btn-user-action" style="padding: 2px 5px; font-size: 10px;" onclick="UI.copyToClipboard('<?php echo SITE_URL . '/' . $c['slug']; ?>')" title="Copiar Link"><i data-lucide="copy" class="icon-lucide"></i></button>
                                </div>
                                <?php if (!empty($c['custom_domain'])): ?>
                                    <div style="display: flex; align-items: center; gap: 5px; font-size: 10px; color: var(--primary);">
                                        <i data-lucide="globe" style="width: 10px; height: 10px;"></i>
                                        <span title="Domínio Personalizado"><?php echo htmlspecialchars($c['custom_domain']); ?></span>
                                        <button class="btn-user-action" style="padding: 0; background: none; border: none; font-size: 9px; opacity: 0.7;" onclick="UI.copyToClipboard('https://<?php echo $c['custom_domain']; ?>')" title="Copiar Domínio"><i data-lucide="copy" style="width: 9px; height: 9px;"></i></button>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td>
                            <span class="badge" style="background: rgba(var(--primary-rgb), 0.1); color: var(--primary);">
                                <i data-lucide="package" class="icon-lucide"></i> <?php echo htmlspecialchars($c['plan_name'] ?: 'Sem Plano'); ?>
                            </span>
                        </td>
                        <td>
                            <?php 
                            if (!empty($c['expires_at'])): 
                                $expires = strtotime($c['expires_at']);
                                $now = time();
                                $daysTo = ceil(($expires - $now) / 86400);
                                $color = 'var(--success)';
                                if ($daysTo < 0) $color = 'var(--danger)';
                                elseif ($daysTo <= 5) $color = 'var(--warning)';
                            ?>
                                <span style="font-size: 13px; color: <?php echo $color; ?>; font-weight: 700;">
                                    <i data-lucide="calendar" class="icon-lucide"></i> <?php echo date('d/m/Y', $expires); ?>
                                </span>
                            <?php else: ?>
                                <span class="text-muted small">Não definida</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php 
                            $isActuallyActive = ($c['status'] === 'active');
                            if ($isActuallyActive && !empty($c['expires_at'])) {
                                $grace = (int)($platform_settings['grace_period'] ?? 2);
                                if (time() > (strtotime($c['expires_at']) + ($grace * 86400))) {
                                    $isActuallyActive = false;
                                }
                            }

                            if ($isActuallyActive): ?>
                                <span class="badge status-active" style="padding: 4px 10px; font-size: 11px;"><i data-lucide="check" class="icon-lucide"></i> Ativa</span>
                            <?php else: ?>
                                <span class="badge status-danger" style="padding: 4px 10px; font-size: 11px;"><i data-lucide="alert-triangle" class="icon-lucide"></i> Suspensa</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-right">
                            <a href="<?php echo SITE_URL; ?>/admin/companies/details?id=<?php echo $c['id']; ?>" class="btn-user-action" style="color: #60a5fa; text-decoration: none;" title="Ver Detalhes"><i data-lucide="eye" class="icon-lucide"></i></a>
                            <button onclick='openCompanyModal(<?php echo htmlspecialchars(json_encode($c), JSON_HEX_APOS | JSON_HEX_QUOT); ?>)' class="btn-user-action" title="Editar"><i data-lucide="edit" class="icon-lucide"></i></button>
                            <button onclick="deleteCompany(<?php echo $c['id']; ?>)" class="btn-user-action btn-user-delete" title="Remover"><i data-lucide="trash" class="icon-lucide"></i></button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php echo \App\Core\Pagination::render($pagination['page'], $pagination['totalPages'], SITE_URL . '/admin/companies', $pagination['totalItems'], $pagination['limit']); ?>
</div>

<script>
function openCompanyModal(data = null) {
    const plans = <?php echo json_encode($plans); ?>;
    const planOptions = plans.map(p => `<option value="${p.id}" ${data && data.plan_id == p.id ? 'selected' : ''}>${p.name}</option>`).join('');

    const html = `
        <form onsubmit="saveCompany(event, this)">
            <input type="hidden" name="nonce" value="<?php echo $nonces['save']; ?>">
            <input type="hidden" name="id" value="${data ? data.id : ''}">
            
            <div class="form-group mb-3">
                <label class="form-label">Nome da Empresa</label>
                <input type="text" name="name" class="form-control" value="${data ? data.name : ''}" required placeholder="Ex: Restaurante do Thomas" onkeyup="${!data ? 'suggestCompanySlug(this.value)' : ''}">
            </div>

            <div class="form-group mb-3">
                <label class="form-label">Slug (URL do Cardápio)</label>
                <input type="text" name="slug" id="company_slug" class="form-control" value="${data ? data.slug : ''}" required placeholder="ex: restaurante-thomas">
                <small class="text-muted">Acessível em: <?php echo SITE_URL; ?>/sua-slug</small>
            </div>

            <div class="form-group mb-3">
                <label class="form-label">Domínio Customizado (Opcional)</label>
                <input type="text" name="custom_domain" class="form-control" value="${data ? data.custom_domain || '' : ''}" placeholder="www.spivet.app">
                <small class="text-muted">O cliente deve apontar o CNAME para o domínio principal deste sistema.</small>
            </div>

            <div class="form-grid-2 mb-3">
                <div class="form-group">
                    <label class="form-label">WhatsApp</label>
                    <input type="text" name="phone" class="form-control mask-phone" value="${data ? data.phone || '' : ''}" placeholder="(00) 00000-0000">
                </div>
                <div class="form-group">
                    <label class="form-label">E-mail</label>
                    <input type="email" name="email" class="form-control" value="${data ? data.email || '' : ''}" placeholder="contato@empresa.com">
                </div>
            </div>

            <div class="form-group mb-3">
                <label class="form-label">Proprietário (Responsável)</label>
                <select name="owner_id" class="form-control">
                    <option value="">-- Sem Proprietário (Opcional) --</option>
                    ${(<?php echo json_encode($owners); ?>).map(u => `<option value="${u.id}" ${u.company_id == data?.id || (data && data.master_manager_id == u.id) ? 'selected' : ''}>${u.name}</option>`).join('')}
                </select>
            </div>

            <div class="form-grid-3 mb-3">
                <div class="form-group">
                    <label class="form-label">Plano Associado</label>
                    <select name="plan_id" class="form-control" required>
                        <option value="">Selecione um plano...</option>
                        ${planOptions}
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Vencimento do Acesso</label>
                    <input type="date" name="expires_at" class="form-control" value="${data ? (data.expires_at ? data.expires_at.split(' ')[0] : '') : ''}">
                </div>
                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select name="active" class="form-control">
                        <option value="1" ${!data || data.active == 1 ? 'selected' : ''}>Ativo</option>
                        <option value="0" ${data && data.active == 0 ? 'selected' : ''}>Suspenso</option>
                    </select>
                </div>
            </div>

            <div class="alert alert-info" style="font-size: 11px; padding: 10px; margin-bottom: 20px;">
                <i data-lucide="info" class="icon-lucide"></i> O primeiro vencimento e as faturas (Trial + Mensalidade) serão geradas automaticamente ao criar uma nova empresa. Para empresas existentes, a renovação ocorre ao dar baixa na fatura recorrente.
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="UI.closeModal()">Cancelar</button>
                <button type="submit" class="btn-primary">
                    <i data-lucide="save" class="icon-lucide icon-sm mr-2"></i> ${data ? 'Salvar Alterações' : 'Cadastrar Empresa'}
                </button>
            </div>
        </form>
    `;
    
    UI.showModal(data ? 'Editar Empresa' : 'Nova Empresa', html, 'lg');
    UI.initMasks();
}

function suggestCompanySlug(name) {
    const input = document.getElementById('company_slug');
    if (!input) return;
    input.value = name.toLowerCase()
        .normalize("NFD")
        .replace(/[\u0300-\u036f]/g, "")
        .replace(/[^a-z0-9]/g, '-')
        .replace(/-+/g, '-')
        .replace(/^-|-$/g, '');
}

async function saveCompany(e, form) {
    e.preventDefault();
    const submitBtn = form.querySelector('button[type="submit"]') || form.closest('.modal-content')?.querySelector('.modal-footer button[type="submit"]');
    let originalText = '';
    
    if (submitBtn) {
        originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i data-lucide="loader" class="icon-lucide icon-spin mr-2"></i> Salvando...';
        submitBtn.disabled = true;
        if(window.lucide) lucide.createIcons();
    }

    try {
        const formData = new FormData(form);
        const response = await fetch('<?php echo SITE_URL; ?>/api/admin/companies/save', {
            method: 'POST',
            body: formData
        });
        
        const text = await response.text();
        let result;
        try {
            result = JSON.parse(text);
        } catch (e) {
            console.error('Resposta inválida do servidor:', text);
            UI.showToast('Erro interno no servidor.', 'error');
            return;
        }

        if (result.success) {
            UI.showToast('Empresa salva com sucesso!');
            window.location.reload();
        } else {
            UI.showToast(result.message || 'Erro ao salvar', 'error');
        }
    } catch (error) {
        console.error('Erro de conexão:', error);
        UI.showToast('Erro de conexão', 'error');
    } finally {
        if (submitBtn) {
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
            if(window.lucide) lucide.createIcons();
        }
    }
}



async function deleteCompany(id) {
    if (await UI.confirm('Deseja realmente excluir esta empresa?')) {
        const result = await UI.request('<?php echo SITE_URL; ?>/api/admin/companies/delete', { 
            id, 
            nonce: '<?php echo $nonces["delete"]; ?>' 
        });
        if (result && result.success) {
            UI.showToast('Empresa removida');
            window.location.reload();
        }
    }
}
</script>
