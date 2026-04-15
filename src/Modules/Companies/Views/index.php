<?php
/** @var array $companies */
/** @var array $plans */
/** @var array $owners */
/** @var array $nonces */
$v = (string)time();
?>
<link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/modules/companies.css?v=<?php echo $v; ?>">

<div class="page-header">
    <div>
        <h2 class="companies-title">Empresas Clientes</h2>
        <p class="companies-subtitle">Administre as unidades e negócios cadastrados no SaaS.</p>
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
                    <tr><td colspan="6" class="table-empty-msg">Nenhuma unidade cadastrada.</td></tr>
                <?php else: ?>
                    <?php foreach ($companies as $c): ?>
                    <tr>
                        <td>
                            <div class="company-name-box"><?php echo htmlspecialchars($c['name']); ?></div>
                            <div class="company-email-sub"><?php echo htmlspecialchars($c['email'] ?: 'Sem e-mail'); ?></div>
                        </td>
                        <td>
                            <div class="company-slug-row">
                                <div class="company-slug-item">
                                    <code class="company-slug-badge" onclick="UI.copyToClipboard('<?php echo SITE_URL . '/' . $c['slug']; ?>')"><?php echo htmlspecialchars($c['slug']); ?></code>
                                    <button class="btn-user-action" onclick="UI.copyToClipboard('<?php echo SITE_URL . '/' . $c['slug']; ?>')" title="Copiar Link"><i data-lucide="copy" class="icon-lucide"></i></button>
                                </div>
                                <?php if (!empty($c['custom_domain'])): ?>
                                    <div class="company-custom-domain">
                                        <i data-lucide="globe"></i>
                                        <span title="Domínio Personalizado"><?php echo htmlspecialchars($c['custom_domain']); ?></span>
                                        <button class="btn-user-action" onclick="UI.copyToClipboard('https://<?php echo $c['custom_domain']; ?>')" title="Copiar Domínio"><i data-lucide="copy"></i></button>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td>
                            <span class="badge badge-primary-soft">
                                <i data-lucide="package" class="icon-lucide"></i> <?php echo htmlspecialchars($c['plan_name'] ?: 'Sem Plano'); ?>
                            </span>
                        </td>
                        <td>
                            <?php 
                            if (!empty($c['expires_at'])): 
                                $expires = strtotime($c['expires_at']);
                                $now = time();
                                $daysTo = ceil(($expires - $now) / 86400);
                                $statusClass = 'text-success';
                                if ($daysTo < 0) $statusClass = 'text-danger';
                                elseif ($daysTo <= 5) $statusClass = 'text-warning';
                            ?>
                                <span class="expiry-date <?php echo $statusClass; ?>">
                                    <i data-lucide="calendar" class="icon-lucide"></i> <?php echo date('d/m/Y', $expires); ?>
                                </span>
                            <?php else: ?>
                                <span class="text-muted small">Não definida</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php 
                            global $platform_settings;
                            $isActuallyActive = ($c['status'] === 'active');
                            if ($isActuallyActive && !empty($c['expires_at'])) {
                                $grace = (int)($platform_settings['grace_period'] ?? 2);
                                if (time() > (strtotime($c['expires_at']) + ($grace * 86400))) {
                                    $isActuallyActive = false;
                                }
                            }

                            if ($isActuallyActive): ?>
                                <span class="badge status-active"><i data-lucide="check" class="icon-lucide"></i> Ativa</span>
                            <?php else: ?>
                                <span class="badge status-danger"><i data-lucide="alert-triangle" class="icon-lucide"></i> Suspensa</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-right">
                            <div class="company-actions">
                                <a href="<?php echo SITE_URL; ?>/admin/companies/details?id=<?php echo $c['id']; ?>" class="btn-user-action text-info" title="Ver Detalhes"><i data-lucide="eye" class="icon-lucide"></i></a>
                                <button onclick='openCompanyModal(<?php echo htmlspecialchars(json_encode($c), JSON_HEX_APOS | JSON_HEX_QUOT); ?>)' class="btn-user-action" title="Editar"><i data-lucide="edit" class="icon-lucide"></i></button>
                                <button onclick="deleteCompany(<?php echo $c['id']; ?>)" class="btn-user-action btn-user-delete" title="Remover"><i data-lucide="trash" class="icon-lucide"></i></button>
                            </div>
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
        <form action="<?php echo SITE_URL; ?>/api/admin/companies/save" method="POST" class="ajax-form">
            <input type="hidden" name="nonce" value="<?php echo $nonces['save']; ?>">
            <input type="hidden" name="id" value="${data ? data.id : ''}">
            
            <div class="floating-group">
                <input type="text" name="name" class="form-control" value="${data ? data.name : ''}" required placeholder=" " onkeyup="${!data ? 'suggestCompanySlug(this.value)' : ''}">
                <label class="floating-label">Nome da Empresa</label>
            </div>

            <div class="floating-group mt-4">
                <input type="text" name="slug" id="company_slug" class="form-control" value="${data ? data.slug : ''}" required placeholder=" ">
                <label class="floating-label">Slug (URL do Portal)</label>
                <small class="text-muted" style="font-size: 10px; margin-top: 4px; display: block;">Acessível em: <?php echo SITE_URL; ?>/sua-slug</small>
            </div>

            <div class="floating-group mt-4">
                <input type="text" name="custom_domain" class="form-control" value="${data ? data.custom_domain || '' : ''}" placeholder=" ">
                <label class="floating-label">Domínio Customizado (Opcional)</label>
            </div>

            <div class="form-grid-2 mt-4">
                <div class="floating-group">
                    <input type="text" name="phone" class="form-control mask-phone" value="${data ? data.phone || '' : ''}" placeholder=" ">
                    <label class="floating-label">WhatsApp</label>
                </div>
                <div class="floating-group">
                    <input type="email" name="email" class="form-control" value="${data ? data.email || '' : ''}" placeholder=" ">
                    <label class="floating-label">E-mail</label>
                </div>
            </div>

            <div class="floating-group mt-4">
                <select name="owner_id" class="form-control" placeholder=" ">
                    <option value="">-- Sem Proprietário (Opcional) --</option>
                    ${(<?php echo json_encode($owners); ?>).map(u => `<option value="${u.id}" ${u.company_id == data?.id || (data && data.master_manager_id == u.id) ? 'selected' : ''}>${u.name}</option>`).join('')}
                </select>
                <label class="floating-label">Proprietário (Responsável)</label>
            </div>

            <div class="form-grid-2 mt-4">
                <div class="floating-group">
                    <select name="plan_id" class="form-control" required placeholder=" ">
                        <option value="">Selecione um plano...</option>
                        ${planOptions}
                    </select>
                    <label class="floating-label">Plano</label>
                </div>
                <div class="floating-group">
                    <input type="date" name="expires_at" class="form-control" value="${data ? (data.expires_at ? data.expires_at.split(' ')[0] : '') : ''}" placeholder=" ">
                    <label class="floating-label">Expiração</label>
                </div>
                <div class="floating-group">
                    <select name="active" class="form-control" placeholder=" ">
                        <option value="1" ${!data || data.active == 1 ? 'selected' : ''}>Ativo</option>
                        <option value="0" ${data && data.active == 0 ? 'selected' : ''}>Suspenso</option>
                    </select>
                    <label class="floating-label">Status</label>
                </div>
            </div>

            <div class="alert-premium">
                <i data-lucide="info" class="icon-lucide"></i>
                <span>O primeiro vencimento e as faturas (Trial + Mensalidade) serão geradas automaticamente ao criar uma nova empresa.</span>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-dark" onclick="UI.closeModal()">Cancelar</button>
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

document.addEventListener('ajaxSuccess', (e) => {
    if (e.target.action.includes('/api/admin/companies/save')) {
        window.location.reload(); 
    }
});

async function deleteCompany(id) {
    if (await UI.confirm('Deseja realmente excluir esta empresa?')) {
        const result = await UI.request('<?php echo SITE_URL; ?>/api/admin/companies/delete', { 
            id, 
            nonce: '<?php echo $nonces["delete"]; ?>' 
        });
        if (result && result.success) {
            const row = document.querySelector(`button[onclick*="deleteCompany(${id})"]`)?.closest('tr');
            if (row) {
                row.style.transition = 'all 0.4s ease';
                row.style.opacity = '0';
                row.style.transform = 'translateX(20px)';
                setTimeout(() => row.remove(), 400);
            }
        }
    }
}
</script>
