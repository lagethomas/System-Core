<?php
/** @var array $logs */
/** @var string $start_date */
/** @var string $end_date */
/** @var string $action_filter */
/** @var int $currentPage */
/** @var int $totalPages */
/** @var int $totalLogs */
?>

<div class="page-header" style="margin-bottom: 25px; display: flex; justify-content: space-between; align-items: center;">
    <div>
        <h2 style="color: var(--primary); margin-bottom: 5px;">Logs de Auditoria</h2>
        <p style="color: var(--text-muted);">Histórico detalhado de ações realizadas no sistema.</p>
    </div>
    <div class="page-header-actions">
        <button class="btn-secondary" onclick="clearLogs()" style="color: #ef4444; border-color: rgba(239, 68, 68, 0.2);">
            <i data-lucide="trash-2" class="icon-lucide"></i> Limpar Histórico
        </button>
    </div>
</div>

<div class="card" style="margin-bottom: 30px; padding: 25px;">
    <form method="GET" class="logs-filter-form" style="display: grid; grid-template-columns: 1fr 1fr 2fr auto auto; gap: 20px; align-items: baseline;">
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
        
        <button type="submit" class="btn-primary" style="height: 42px; display: flex; align-items: center; gap: 8px;">
            <i data-lucide="search" class="icon-lucide icon-sm"></i> Filtrar
        </button>
        
        <?php if (!empty($start_date) || !empty($end_date) || !empty($action_filter)): ?>
            <a href="?" class="btn-secondary" title="Limpar Filtros" style="height: 42px; display: flex; align-items: center; justify-content: center; width: 42px; padding: 0;">
                <i data-lucide="x" class="icon-lucide"></i>
            </a>
        <?php endif; ?>
    </form>
</div>

<div class="card user-list-card">
    <div class="table-responsive" style="min-height: 45vh;">
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
                    <tr><td colspan="5" style="text-align: center; padding: 60px; color: var(--text-muted);">Nenhum log encontrado para os critérios selecionados.</td></tr>
                <?php else: ?>
                    <?php foreach ($logs as $log): ?>
                    <tr>
                        <td style="white-space: nowrap; color: var(--text-main); font-size: 13px;">
                            <?php echo date('d/m/Y H:i:s', strtotime($log['created_at'])); ?>
                        </td>
                        <td style="font-weight: 600; color: var(--primary);">
                            <?php if ($log['user_name']): ?>
                                <i data-lucide="user" class="icon-lucide icon-xs mr-2"></i> <?php echo htmlspecialchars($log['user_name']); ?>
                            <?php else: ?>
                                <i data-lucide="cpu" class="icon-lucide icon-xs mr-2"></i> <span style="color: var(--text-muted);">Sistema</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge" style="background: rgba(var(--primary-rgb), 0.05); color: var(--primary); font-size: 10px; font-weight: 800; text-transform: uppercase; border-radius: 50px; padding: 4px 10px; border: 1px solid rgba(var(--primary-rgb), 0.1);">
                                <?php echo htmlspecialchars($log['action']); ?>
                            </span>
                        </td>
                        <td style="font-size: 13px; color: var(--text-muted); max-width: 400px;">
                            <?php echo htmlspecialchars($log['description']); ?>
                        </td>
                        <td style="font-family: monospace; font-size: 12px; color: var(--text-muted);">
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
            const res = await fetch('<?php echo SITE_URL; ?>/api/admin/logs/clear', {
                method: 'POST'
            });
            const result = await res.json();
            if (result.success) {
                UI.showToast('Histórico limpo com sucesso!');
                window.location.reload();
            } else {
                UI.showToast(result.message || 'Erro ao limpar logs', 'error');
            }
        } catch (error) {
            UI.showToast('Erro de conexão', 'error');
        }
    }
}
</script>
