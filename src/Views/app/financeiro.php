<?php
/** @var array $movimentacoes */
/** @var array $resumo */
/** @var array $chartData */

$labels = array_column($chartData, 'label');
$entradas = array_column($chartData, 'entrada');
$saidas = array_column($chartData, 'saida');
?>

<!-- FINANCE HEADER -->
<div class="page-header">
    <div>
        <h2 style="color: var(--primary); margin-bottom: 5px;">Módulo Financeiro</h2>
        <p style="color: var(--text-muted);">Controle de entradas, saídas e movimentações do estabelecimento.</p>
    </div>
</div>

<div class="card mb-4" style="padding: 15px 20px; border-radius: 12px; border: 1px solid var(--border); background: var(--bg-surface);">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 20px;">
        <form action="" method="GET" style="display: flex; gap: 20px; align-items: center; flex-wrap: wrap;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <i class="fas fa-calendar-alt" style="color: var(--primary)"></i>
                <span style="font-size: 13px; font-weight: 600; color: var(--text-main)">Filtrar Período:</span>
            </div>
            <div style="display: flex; gap: 10px; align-items: center;">
                <input type="date" name="start_date" class="form-control" value="<?php echo $filters['start_date']; ?>" style="width: 150px; padding: 7px 12px; font-size: 13px;">
                <span class="text-muted">até</span>
                <input type="date" name="end_date" class="form-control" value="<?php echo $filters['end_date']; ?>" style="width: 150px; padding: 7px 12px; font-size: 13px;">
            </div>
            <button type="submit" class="btn-primary" style="padding: 8px 18px; font-size: 13px; border-radius: 8px;">
                <i class="fas fa-filter"></i> Aplicar
            </button>
        </form>

        <div style="display: flex; gap: 10px;">
            <button class="btn-finance-action btn-finance-saida" onclick="openFinanceModal('saida')">
                <i class="fas fa-minus-circle"></i> Nova Saída
            </button>
            <button class="btn-finance-action btn-finance-entrada" onclick="openFinanceModal('entrada')">
                <i class="fas fa-plus-circle"></i> Nova Entrada
            </button>
        </div>
    </div>
</div>

<!-- SUMMARY CARDS -->
<div class="finance-stats-grid">
    <div class="finance-card entrada">
        <div class="finance-icon"><i class="fas fa-arrow-up"></i></div>
        <div class="finance-info">
            <span class="finance-label">Entradas</span>
            <span class="finance-value">+ R$ <?php echo number_format($resumo['entradas'], 2, ',', '.'); ?></span>
        </div>
    </div>
    <div class="finance-card saida">
        <div class="finance-icon"><i class="fas fa-arrow-down"></i></div>
        <div class="finance-info">
            <span class="finance-label">Saídas</span>
            <span class="finance-value">- R$ <?php echo number_format($resumo['saidas'], 2, ',', '.'); ?></span>
        </div>
    </div>
    <div class="finance-card saldo">
        <div class="finance-icon"><i class="fas fa-hand-holding-usd"></i></div>
        <div class="finance-info">
            <span class="finance-label">Saldo Real</span>
            <span class="finance-value">R$ <?php echo number_format($resumo['saldo'], 2, ',', '.'); ?></span>
        </div>
    </div>
</div>

<div class="finance-main-grid">
    <!-- CHART SECTION -->
    <div class="card">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 25px;">
            <h3 style="font-size: 16px; margin:0;"><i class="fas fa-chart-line" style="color:var(--primary)"></i> Tendência de Fluxo (7 dias)</h3>
            <span class="badge badge-primary">Relatório em Tempo Real</span>
        </div>
        <div class="chart-container">
            <canvas id="financeTrendChart"></canvas>
        </div>
    </div>

    <!-- LIST SECTION -->
    <div class="card" style="padding:0; overflow:hidden;">
        <div style="padding: 20px; border-bottom: 1px solid var(--border);">
             <h3 style="font-size: 16px; margin:0;"><i class="fas fa-history"></i> Últimas Movimentações</h3>
        </div>
        <div class="table-responsive">
            <table class="premium-table">
                <thead>
                    <tr>
                        <th>Data</th>
                        <th>Descrição</th>
                        <th>Valor</th>
                        <th class="text-right">Remover</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($movimentacoes)): ?>
                        <tr><td colspan="4" class="text-center" style="padding: 30px; color: var(--text-muted);">Sem registros.</td></tr>
                    <?php else: ?>
                        <?php foreach ($movimentacoes as $m): ?>
                            <tr>
                                <td style="font-size: 12px;"><?php echo date('d/m H:i', strtotime($m['data_movimentacao'])); ?></td>
                                <td style="font-weight: 600; font-size: 13px;"><?php echo htmlspecialchars($m['descricao']); ?></td>
                                <td style="color: <?php echo $m['tipo'] === 'entrada' ? '#10b981' : '#ef4444'; ?>; font-weight: 800;">
                                    <?php echo $m['tipo'] === 'entrada' ? '+' : '-'; ?> R$<?php echo number_format($m['valor'], 2, ',', '.'); ?>
                                </td>
                                <td class="text-right">
                                    <?php if (is_null($m['referencia_id'])): ?>
                                        <button onclick="deleteFinanceiro(<?php echo $m['id']; ?>)" class="btn-user-action btn-user-delete"><i class="fas fa-times"></i></button>
                                    <?php else: ?>
                                        <span class="badge badge-secondary" style="font-size: 10px; opacity: 0.6;">Comanda</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Chart Implementation
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('financeTrendChart').getContext('2d');
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode($labels); ?>,
            datasets: [
                {
                    label: 'Entradas',
                    data: <?php echo json_encode($entradas); ?>,
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    borderWidth: 3,
                    pointRadius: 5,
                    pointBackgroundColor: '#10b981',
                    fill: true,
                    tension: 0.4
                },
                {
                    label: 'Saídas',
                    data: <?php echo json_encode($saidas); ?>,
                    borderColor: '#ef4444',
                    backgroundColor: 'rgba(239, 68, 68, 0.1)',
                    borderWidth: 3,
                    pointRadius: 5,
                    pointBackgroundColor: '#ef4444',
                    fill: true,
                    tension: 0.4
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                    labels: { color: '#94a3b8', font: { family: 'Outfit', size: 12 } }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(255, 255, 255, 0.05)' },
                    ticks: { color: '#94a3b8', font: { size: 11 } }
                },
                x: {
                    grid: { display: false },
                    ticks: { color: '#94a3b8', font: { size: 11 } }
                }
            }
        }
    });
});

function openFinanceModal(tipo = 'entrada') {
    const html = `
        <form class="ajax-form" id="form-finance" action="<?php echo SITE_URL; ?>/api/financeiro/add">
            <div class="modal-body-scroll" style="padding: 10px;">
                <input type="hidden" name="tipo" value="${tipo}">
                <input type="hidden" name="nonce" value="<?php echo $nonce_add; ?>">
                
                <div class="form-group mb-3">
                    <label class="form-label">Descrição da Movimentação</label>
                    <input type="text" name="descricao" class="form-control" required placeholder="Ex: Venda Mesa 5, Compra Fornecedor...">
                </div>

                <div class="form-group mb-3">
                    <label class="form-label">Valor do Lançamento (R$)</label>
                    <input type="text" name="valor" class="form-control mask-money" required placeholder="0,00">
                </div>

                <div class="form-group mb-4">
                    <label class="form-label">Método de Pagamento</label>
                    <select name="metodo_pagamento" class="form-control w-100" style="padding: 12px; border-radius: 10px; border: 1px solid var(--border); background: var(--bg-surface); color: var(--text-main);">
                        <option value="Dinheiro">Dinheiro</option>
                        <option value="PIX">PIX</option>
                        <option value="Cartão">Cartão</option>
                        <option value="Outros">Outros</option>
                    </select>
                </div>
            </div>

            <div class="modal-footer" style="padding-top:20px; border-top: 1px solid var(--border);">
                <button type="button" class="btn-secondary" onclick="UI.closeModal()">Cancelar</button>
                <button type="submit" class="btn-finance-action ${tipo === 'entrada' ? 'btn-finance-entrada' : 'btn-finance-saida'}">Salvar Lançamento</button>
            </div>
        </form>
    `;

    UI.showModal(tipo === 'entrada' ? 'Nova Entrada' : 'Nova Saída', html);

    const form = document.getElementById('form-finance');
    form.onsubmit = async (e) => {
        e.preventDefault();
        const res = await UI.request(form.action, Object.fromEntries(new FormData(form).entries()));
        if (res && res.success) {
            UI.showToast('Fluxo atualizado!');
            setTimeout(() => window.location.reload(), 1200);
        }
    };
}

async function deleteFinanceiro(id) {
    if (await UI.confirm('Remover esta movimentação?')) {
        const res = await UI.request('<?php echo SITE_URL; ?>/api/financeiro/delete', { 
            id: id,
            nonce: '<?php echo $nonce_delete; ?>'
        });
        if (res && res.success) window.location.reload();
    }
}
</script>
