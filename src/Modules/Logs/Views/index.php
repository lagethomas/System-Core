<?php declare(strict_types=1);
/** @var array $logs */
/** @var string $start_date */
/** @var string $end_date */
/** @var string $action_filter */
/** @var int $currentPage */
/** @var int $totalPages */
/** @var int $totalLogs */
?>

<div class="logs-header">
    <div class="flex items-center gap-4">
        <div class="header-icon-box md">
            <i data-lucide="terminal" class="icon-md"></i>
        </div>
        <div>
            <h2 class="m-0">Logs de Auditoria</h2>
            <p class="text-muted m-0">Histórico detalhado de ações realizadas no sistema.</p>
        </div>
    </div>
</div>

<div class="card user-list-card">
    <div class="card-header d-flex justify-content-between align-items-center mb-4">
        <h3 class="m-0">Histórico de Atividades</h3>
        <button class="btn-danger-glass icon-btn-square" onclick="clearLogs()" title="Limpar todo o histórico">
            <i data-lucide="trash-2" class="icon-sm"></i>
        </button>
    </div>

    <!-- Filtros integrados no card -->
    <div class="px-5 mb-5">
        <form method="GET" class="logs-filter-form flex items-center gap-3">
            <div class="floating-group">
                <input type="date" name="start_date" class="form-control" value="<?php echo $start_date; ?>" placeholder=" ">
                <label class="floating-label">Data Início</label>
            </div>
            <div class="floating-group">
                <input type="date" name="end_date" class="form-control" value="<?php echo $end_date; ?>" placeholder=" ">
                <label class="floating-label">Data Fim</label>
            </div>
            <div class="floating-group flex-2">
                <input type="text" name="action" class="form-control" placeholder=" " value="<?php echo htmlspecialchars($action_filter); ?>">
                <label class="floating-label">Filtrar por Ação</label>
            </div>
            
            <button type="submit" class="btn-primary btn-premium flex-shrink-0">
                <i data-lucide="search" class="icon-sm mr-2"></i> Filtrar
            </button>
            
            <?php if (!empty($start_date) || !empty($end_date) || !empty($action_filter)): ?>
                <a href="?" class="btn-dark btn-premium flex-shrink-0 icon-btn-square-lg" title="Limpar Filtros">
                    <i data-lucide="x" class="icon-sm"></i>
                </a>
            <?php endif; ?>
        </form>
    </div>

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
                                    <div class="header-icon-box sm">
                                        <i data-lucide="user" class="icon-sm"></i>
                                    </div>
                                    <?php echo htmlspecialchars($log['user_name']); ?>
                                </div>
                            <?php else: ?>
                                <div class="d-flex align-items-center gap-2 text-muted">
                                    <div class="header-icon-box sm bg-card-alt">
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
    <div class="mt-4 p-5 pt-0">
        <?php echo \App\Core\Pagination::render($currentPage, $totalPages, SITE_URL . '/logs', $totalLogs, 25); ?>
    </div>
</div>

<script src="<?php echo SITE_URL; ?>/assets/js/modules/logs.js"></script>
