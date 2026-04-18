<?php declare(strict_types=1);
/** @var array $logs */
/** @var string $start_date */
/** @var string $end_date */
/** @var string $action_filter */
/** @var int $currentPage */
/** @var int $totalPages */
/** @var int $totalLogs */
?>

<div class="logs-header mb-5">
        <div class="flex items-center gap-4">
            <div class="header-icon-box">
                <i data-lucide="terminal"></i>
            </div>
            <div>
                <h2 class="m-0">Logs de Auditoria</h2>
                <p class="text-muted m-0">Histórico detalhado de ações realizadas no sistema.</p>
            </div>
        </div>
    </div>
</div>

<div class="card logs-filter-card mb-4 p-4">
    <form method="GET" class="logs-filter-form flex items-end gap-3">
        <div class="floating-group">
            <input type="date" name="start_date" class="form-control" value="<?php echo $start_date; ?>" placeholder=" ">
            <label class="floating-label">Data Início</label>
        </div>
        <div class="floating-group">
            <input type="date" name="end_date" class="form-control" value="<?php echo $end_date; ?>" placeholder=" ">
            <label class="floating-label">Data Fim</label>
        </div>
        <div class="floating-group" style="flex: 2;">
            <input type="text" name="action" class="form-control" placeholder=" " value="<?php echo htmlspecialchars($action_filter); ?>">
            <label class="floating-label">Filtrar por Ação</label>
        </div>
        
        <button type="submit" class="btn-primary" style="height: 48px; padding: 0 25px; border-radius: 12px; flex-shrink: 0;">
            <i data-lucide="search" class="icon-sm mr-2"></i> Filtrar
        </button>

        <button type="button" class="btn-danger" onclick="clearLogs()" style="height: 48px; padding: 0 20px; border-radius: 12px; flex-shrink: 0;" title="Limpar todo o histórico">
            <i data-lucide="trash-2" class="icon-sm mr-2"></i> Limpar Histórico
        </button>
        
        <?php if (!empty($start_date) || !empty($end_date) || !empty($action_filter)): ?>
            <a href="?" class="btn-dark" title="Limpar Filtros" style="height: 48px; width: 48px; display: flex; align-items: center; justify-content: center; border-radius: 12px; padding: 0; flex-shrink: 0;">
                <i data-lucide="x" class="icon-sm"></i>
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
                    <th class="text-right">IP</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($logs)): ?>
                    <tr><td colspan="5" class="no-results">Nenhum log encontrado para os critérios selecionados.</td></tr>
                <?php else: ?>
                    <?php foreach ($logs as $log): ?>
                    <tr>
                        <td class="log-date-col small fw-700">
                            <?php echo date('d/m/Y H:i:s', strtotime($log['created_at'])); ?>
                        </td>
                        <td class="log-user-col small">
                            <?php if ($log['user_name']): ?>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="header-icon-box" style="width: 32px; height: 32px; border-radius: 8px;">
                                        <i data-lucide="user" class="icon-sm"></i>
                                    </div>
                                    <?php echo htmlspecialchars($log['user_name']); ?>
                                </div>
                            <?php else: ?>
                                <div class="d-flex align-items-center gap-2 text-muted">
                                    <div class="header-icon-box" style="width: 32px; height: 32px; border-radius: 8px; background: rgba(var(--text-muted-rgb), 0.1);">
                                        <i data-lucide="cpu" class="icon-sm"></i>
                                    </div>
                                    <span class="log-user-system">Sistema</span>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="status-badge status-primary">
                                <?php echo htmlspecialchars($log['action']); ?>
                            </span>
                        </td>
                        <td class="log-description-col small text-muted">
                            <?php echo htmlspecialchars($log['description']); ?>
                        </td>
                        <td class="log-ip-col text-right small font-mono opacity-50">
                            <?php echo $log['ip_address']; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-4">
        <?php echo \App\Core\Pagination::render($currentPage, $totalPages, SITE_URL . '/logs', $totalLogs, 25); ?>
    </div>
</div>

<script src="<?php echo SITE_URL; ?>/assets/js/modules/logs.js"></script>
