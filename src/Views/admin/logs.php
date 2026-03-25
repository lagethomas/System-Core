<?php
/** @var array $logs */
/** @var string $start_date */
/** @var string $end_date */
/** @var string $action_filter */
?>


<div class="log-filter-card">
    <form method="GET" class="logs-filter-form">
        <div class="form-group">
            <label class="form-label small">Início</label>
            <input type="date" name="start_date" class="form-control" value="<?php echo $start_date; ?>">
        </div>
        <div class="form-group">
            <label class="form-label small">Fim</label>
            <input type="date" name="end_date" class="form-control" value="<?php echo $end_date; ?>">
        </div>
        <div class="form-group flex-1">
            <label class="form-label small">Ação</label>
            <input type="text" name="action" class="form-control w-100" placeholder="Filtrar por ação..." value="<?php echo htmlspecialchars($action_filter); ?>">
        </div>
        <button type="submit" class="btn-primary logs-filter-btn">
            <i data-lucide="search"></i> Filtrar
        </button>
        <a href="?" class="btn-secondary logs-reset-btn">
            <i data-lucide="rotate-ccw"></i>
        </a>
    </form>
</div>

<div class="log-list-card">
    <h3 class="mb-4">Logs Globais</h3>
    
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
                    <tr><td colspan="5" class="table-empty-row">Nenhum log encontrado.</td></tr>
                <?php else: ?>
                    <?php foreach ($logs as $log): ?>
                    <tr>
                        <td class="log-date-cell">
                            <?php echo date('d/m/Y H:i:s', strtotime($log['created_at'])); ?>
                        </td>
                        <td class="log-user-name">
                            <?php echo htmlspecialchars($log['user_name'] ?: 'Sistema'); ?>
                        </td>
                        <td>
                            <span class="log-action-tag">
                                <?php echo htmlspecialchars($log['action']); ?>
                            </span>
                        </td>
                        <td class="log-details-cell">
                            <?php echo htmlspecialchars($log['description']); ?>
                        </td>
                        <td class="log-ip-cell">
                            <?php echo $log['ip_address']; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>


