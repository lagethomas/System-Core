<?php
/** @var array $logs */
/** @var string $start_date */
/** @var string $end_date */
/** @var string $action_filter */
/** @var int $currentPage */
/** @var int $totalPages */
/** @var int $totalLogs */
$v = time();
?>
<link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/modules/logs.css?v=<?php echo $v; ?>">

<div class="page-header">
    <div class="page-header-info">
        <h2>Logs de Auditoria</h2>
        <p>Histórico detalhado de ações realizadas no sistema.</p>
    </div>
    <div class="page-header-actions">
        <button class="btn-secondary text-danger" onclick="clearLogs()">
            <i data-lucide="trash-2" class="icon-lucide"></i> Limpar Histórico
        </button>
    </div>
</div>

<div class="card logs-filter-card">
    <form method="GET" class="logs-filter-form">
        <div class="floating-group">
            <input type="date" name="start_date" class="form-control" value="<?php echo $start_date; ?>" placeholder=" ">
            <label class="floating-label">Data Início</label>
        </div>
        <div class="floating-group">
            <input type="date" name="end_date" class="form-control" value="<?php echo $end_date; ?>" placeholder=" ">
            <label class="floating-label">Data Fim</label>
        </div>
        <div class="floating-group">
            <input type="text" name="action" class="form-control" placeholder=" " value="<?php echo htmlspecialchars($action_filter); ?>">
            <label class="floating-label">Filtrar por Ação</label>
        </div>
        
        <button type="submit" class="btn-primary" style="height: 42px;">
            <i data-lucide="search" class="icon-lucide icon-sm"></i> Filtrar
        </button>
        
        <?php if (!empty($start_date) || !empty($end_date) || !empty($action_filter)): ?>
            <a href="?" class="btn-secondary" title="Limpar Filtros" style="height: 42px; width: 42px; padding: 0;">
                <i data-lucide="x" class="icon-lucide"></i>
            </a>
        <?php endif; ?>
    </form>
</div>

<div class="card user-list-card">
    <div class="table-responsive">
        <table class="premium-table">
            <thead>
                <tr>
                    <th>Data/Hora</th>
                    <th>Usuário</th>
                    <th>Ação</th>
                    <th>Descrição</th>
                    <th>IP</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($logs)): ?>
                    <tr><td colspan="5" class="no-results">Nenhum log encontrado para os critérios selecionados.</td></tr>
                <?php else: ?>
                    <?php foreach ($logs as $log): ?>
                    <tr>
                        <td class="log-date-col">
                            <?php echo date('d/m/Y H:i:s', strtotime($log['created_at'])); ?>
                        </td>
                        <td class="log-user-col">
                            <?php if ($log['user_name']): ?>
                                <i data-lucide="user" class="icon-lucide icon-xs mr-2"></i> <?php echo htmlspecialchars($log['user_name']); ?>
                            <?php else: ?>
                                <i data-lucide="cpu" class="icon-lucide icon-xs mr-2"></i> <span class="log-user-system">Sistema</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="log-action-badge">
                                <?php echo htmlspecialchars($log['action']); ?>
                            </span>
                        </td>
                        <td class="log-description-col">
                            <?php echo htmlspecialchars($log['description']); ?>
                        </td>
                        <td class="log-ip-col">
                            <?php echo $log['ip_address']; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php echo \App\Core\Pagination::render($currentPage, $totalPages, SITE_URL . '/admin/logs', $totalLogs, 25); ?>
</div>

<script>
async function clearLogs() {
    if (await UI.confirm('Deseja realmente apagar todo o histórico de logs do sistema? Esta ação é irreversível.', {
        title: '⚠️ Confirmar Limpeza',
        confirmText: 'Sim, Apagar Tudo',
        type: 'danger'
    })) {
        try {
            const formData = new FormData();
            const res = await UI.request('<?php echo SITE_URL; ?>/api/admin/logs/clear', formData);
            if (res && res.success) {
                window.location.reload();
            }
        } catch (error) {
            UI.showToast('Erro de conexão', 'error');
        }
    }
}
</script>
