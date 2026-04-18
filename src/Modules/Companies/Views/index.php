<?php declare(strict_types=1);
/** @var array $companies */
/** @var array $plans */
/** @var array $owners */
/** @var array $nonces */
/** @var array $pagination */
?>

<div class="page-header">
    <div class="flex items-center gap-4">
        <div class="header-icon-box md">
            <i data-lucide="briefcase" class="icon-md"></i>
        </div>
        <div>
            <h2 class="m-0">Empresas Clientes</h2>
            <p class="text-muted m-0">Administre as unidades e negócios cadastrados no SaaS.</p>
        </div>
    </div>
    <div class="page-header-actions">
        <button class="btn-primary btn-premium" onclick="openCompanyModal()">
            <i data-lucide="plus" class="icon-sm"></i> Nova Empresa
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
                    <tr><td colspan="6" class="no-results">Nenhuma unidade cadastrada.</td></tr>
                <?php else: ?>
                    <?php foreach ($companies as $c): ?>
                    <tr>
                        <td>
                            <div class="fw-700 text-main"><?php echo htmlspecialchars($c['name']); ?></div>
                            <div class="text-muted small"><?php echo htmlspecialchars($c['email'] ?: 'Sem e-mail'); ?></div>
                        </td>
                        <td>
                            <div class="flex flex-column gap-1">
                                <div class="flex items-center gap-2">
                                    <code class="company-slug-badge" onclick="UI.copyToClipboard('<?php echo SITE_URL . '/' . $c['slug']; ?>')"><?php echo htmlspecialchars($c['slug']); ?></code>
                                    <button class="btn-user-action" onclick="UI.copyToClipboard('<?php echo SITE_URL . '/' . $c['slug']; ?>')" title="Copiar Link"><i data-lucide="copy" class="icon-xs"></i></button>
                                </div>
                                <?php if (!empty($c['custom_domain'])): ?>
                                    <div class="flex items-center gap-2 text-primary small">
                                        <i data-lucide="globe" class="icon-xs"></i>
                                        <span title="Domínio Personalizado"><?php echo htmlspecialchars($c['custom_domain']); ?></span>
                                        <button class="btn-user-action" onclick="UI.copyToClipboard('https://<?php echo $c['custom_domain']; ?>')" title="Copiar Domínio"><i data-lucide="copy" class="icon-xs"></i></button>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td>
                            <span class="status-badge status-primary">
                                <i data-lucide="package" class="icon-xs mr-1"></i> <?php echo htmlspecialchars($c['plan_name'] ?: 'Sem Plano'); ?>
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
                                <span class="<?php echo $statusClass; ?> small fw-700">
                                    <i data-lucide="calendar" class="icon-xs mr-1"></i> <?php echo date('d/m/Y', $expires); ?>
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
                                <span class="status-badge status-success">Ativa</span>
                            <?php else: ?>
                                <span class="status-badge status-danger">Suspensa</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-right">
                            <div class="flex justify-end gap-2">
                                <a href="<?php echo SITE_URL; ?>/admin/companies/details?id=<?php echo $c['id']; ?>" class="btn-user-action" title="Ver Detalhes"><i data-lucide="eye" class="icon-sm"></i></a>
                                <button onclick='openCompanyModal(<?php echo htmlspecialchars(json_encode($c), JSON_HEX_APOS | JSON_HEX_QUOT); ?>)' class="btn-user-action" title="Editar"><i data-lucide="edit" class="icon-sm"></i></button>
                                <button onclick="deleteCompany(<?php echo $c['id']; ?>)" class="btn-user-action btn-user-delete" title="Remover"><i data-lucide="trash-2" class="icon-sm"></i></button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <div class="mt-4 p-5 pt-0">
        <?php echo \App\Core\Pagination::render($pagination['page'], $pagination['totalPages'], SITE_URL . '/admin/companies', $pagination['totalItems'], $pagination['limit']); ?>
    </div>
</div>

<script>
    window.COMPANIES_DATA = {
        plans: <?php echo json_encode($plans); ?>,
        owners: <?php echo json_encode($owners); ?>,
        nonces: <?php echo json_encode($nonces); ?>
    };
</script>
<script src="<?php echo SITE_URL; ?>/assets/js/modules/companies.js"></script>
