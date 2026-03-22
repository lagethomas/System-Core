<?php
/** @var array $movimentacoes */
/** @var array $metodos */
/** @var array $topProdutos */
/** @var array $caixas */

$totalEntradas = array_sum(array_column($movimentacoes, 'entradas'));
$totalSaidas   = array_sum(array_column($movimentacoes, 'saidas'));
$lucroBruto    = $totalEntradas - $totalSaidas;
?>

<div class="page-header">
    <div>
        <h2 style="color: var(--primary); margin-bottom: 5px;">Relatórios e Análises</h2>
        <p style="color: var(--text-muted);">Visualize o desempenho financeiro e produtividade do estabelecimento.</p>
    </div>
</div>

<!-- TABS NAV — same pattern as settings-tabs -->
<div class="settings-tabs">
    <button class="tab-btn active" id="btn-tab-dashboard" onclick="switchTab('dashboard', this)">
        <i class="fas fa-chart-bar"></i> Painel Geral
    </button>
    <button class="tab-btn" id="btn-tab-caixas" onclick="switchTab('caixas', this)">
        <i class="fas fa-cash-register"></i> Histórico de Caixas
    </button>
</div>

<!-- FILTER BAR (always visible) -->
<div class="card mb-4" style="padding: 15px 20px; border-radius: 12px; border: 1px solid var(--border); background: var(--bg-surface);">
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
            <i class="fas fa-filter"></i> Aplicar Filtro
        </button>
    </form>
</div>

<!-- TAB: PAINEL GERAL -->
<div id="tab-dashboard" class="tab-content active">

    <!-- TOP STATS -->
    <div class="stats-cards-grid mb-4">
        <div class="stat-card" style="border-left: 4px solid #10b981;">
            <span>Faturamento Bruto</span>
            <strong style="color: #10b981;">R$ <?php echo number_format($totalEntradas, 2, ',', '.'); ?></strong>
            <small style="color:var(--text-muted)">No período selecionado</small>
        </div>
        <div class="stat-card" style="border-left: 4px solid #ef4444;">
            <span>Despesas Operacionais</span>
            <strong style="color: #ef4444;">R$ <?php echo number_format($totalSaidas, 2, ',', '.'); ?></strong>
            <small style="color:var(--text-muted)">No período selecionado</small>
        </div>
        <div class="stat-card" style="border-left: 4px solid var(--primary);">
            <span>Resultado Líquido</span>
            <strong style="color: var(--primary);">R$ <?php echo number_format($lucroBruto, 2, ',', '.'); ?></strong>
            <small style="color:var(--text-muted)">Entradas − Saídas</small>
        </div>
    </div>

    <!-- CHARTS -->
    <div class="dashboard-grid mb-4 dashboard-charts-container">
        <div class="card">
            <h3 style="font-size: 16px; margin-bottom: 25px;"><i class="fas fa-chart-line"></i> Fluxo de Tendência (Entradas vs Saídas)</h3>
            <div style="height: 350px; position: relative;">
                <canvas id="chart-movimentacao"></canvas>
            </div>
        </div>
        <div class="card">
            <h3 style="font-size: 16px; margin-bottom: 25px;"><i class="fas fa-chart-pie"></i> Canais de Pagamento</h3>
            <div style="height: 300px; display: flex; align-items: center; justify-content: center;">
                <canvas id="chart-metodos"></canvas>
            </div>
        </div>
    </div>

    <!-- TOP PRODUTOS -->
    <div class="dashboard-grid">
        <div class="card p-0 overflow-hidden" style="grid-column: span 2;">
            <div style="padding: 20px; border-bottom: 1px solid var(--border)">
                <h3 style="font-size: 15px; margin: 0;"><i class="fas fa-trophy"></i> Top 5 Produtos Mais Vendidos</h3>
            </div>
            <div class="table-responsive">
                <table class="premium-table" style="min-width: 0;">
                    <thead>
                        <tr>
                            <th style="padding-left: 20px">Produto</th>
                            <th>Qte Vendida</th>
                            <th class="text-right" style="padding-right: 20px">Total Faturado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($topProdutos)): ?>
                            <tr><td colspan="3" class="text-center py-5 text-muted">Aguardando dados de vendas para gerar o ranking.</td></tr>
                        <?php else: ?>
                            <?php foreach ($topProdutos as $tp): ?>
                                <tr>
                                    <td style="font-weight: 600; padding-left: 20px;"><?php echo htmlspecialchars($tp['produto_nome']); ?></td>
                                    <td style="color: var(--text-muted)"><?php echo $tp['qte']; ?> unidades</td>
                                    <td class="text-right" style="font-weight: 800; color:var(--primary); padding-right: 20px">R$ <?php echo number_format($tp['total'], 2, ',', '.'); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div><!-- /tab-dashboard -->


<!-- TAB: HISTÓRICO DE CAIXAS -->
<div id="tab-caixas" class="tab-content">

    <div class="card p-0 overflow-hidden">
        <div style="padding: 20px; border-bottom: 1px solid var(--border)">
            <h3 style="font-size: 15px; margin: 0;"><i class="fas fa-history"></i> Histórico de Sessões de Caixa</h3>
        </div>
        <div class="table-responsive">
            <table class="premium-table">
                <thead>
                    <tr>
                        <th style="padding-left: 20px">Operador</th>
                        <th>Abertura</th>
                        <th>Status</th>
                        <th>Saldo Inicial</th>
                        <th>Vendas Mesas</th>
                        <th style="color: #0ea5e9;">Vendas PDV</th>
                        <th style="color: #10b981;">Faturamento</th>
                        <th>Resultado</th>
                        <th style="padding-right: 20px; text-align: center;">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($caixas)): ?>
                        <tr><td colspan="8" class="text-center py-5 text-muted">Nenhuma sessão de caixa encontrada no período.</td></tr>
                    <?php else: ?>
                        <?php foreach ($caixas as $c): ?>
                            <tr>
                                <td style="padding-left: 20px; font-weight: 600;"><?php echo htmlspecialchars($c['usuario_nome']); ?></td>
                                <td style="font-size: 12px; color: var(--text-muted)"><?php echo date('d/m H:i', strtotime($c['data_abertura'])); ?></td>
                                <td>
                                    <span class="status-badge <?php echo $c['status'] === 'aberto' ? 'status-active' : 'status-inactive'; ?>">
                                        <?php echo strtoupper($c['status']); ?>
                                    </span>
                                </td>
                                <td style="font-weight: 600; opacity: 0.8">R$ <?php echo number_format((float)$c['saldo_inicial'], 2, ',', '.'); ?></td>
                                <td style="color: #6366f1; font-weight: 600;">R$ <?php echo number_format((float)$c['pagamentos_comanda'], 2, ',', '.'); ?></td>
                                <td style="color: #0ea5e9; font-weight: 600;">R$ <?php echo number_format((float)$c['pagamentos_pdv'], 2, ',', '.'); ?></td>
                                <td style="font-weight: 600; color: #10b981;">R$ <?php echo number_format((float)$c['entradas'], 2, ',', '.'); ?></td>
                                <td style="font-weight: 800; color: <?php echo $c['lucro'] >= 0 ? '#10b981' : '#ef4444'; ?>;">
                                    R$ <?php echo number_format((float)$c['lucro'], 2, ',', '.'); ?>
                                </td>
                                <td style="padding-right: 20px; text-align: center;">
                                    <div style="display:flex; gap:6px; justify-content:center;">
                                        <button class="btn-primary" 
                                                style="padding: 6px; border-radius: 6px; width: 34px; height: 34px; font-size: 14px; background: rgba(var(--primary-rgb), 0.1); color: var(--primary); border: 1px solid rgba(var(--primary-rgb), 0.2);"
                                                title="Ver Resumo" 
                                                onclick='showCaixaDetail(<?php echo json_encode($c); ?>)'>
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn-primary" 
                                                style="padding: 6px; border-radius: 6px; width: 34px; height: 34px; font-size: 14px; background: rgba(14,165,233,0.1); color: #0ea5e9; border: 1px solid rgba(14,165,233,0.2);"
                                                title="Fluxo do Turno" 
                                                onclick='showFluxoTurno(<?php echo (int)$c['id']; ?>, "<?php echo htmlspecialchars($c['usuario_nome']); ?>", "<?php echo date('d/m H:i', strtotime($c['data_abertura'])); ?>")'>
                                            <i class="fas fa-file-alt"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div><!-- /tab-caixas -->



<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
/* Tab switching — uses .active CSS class, same as settings module */
function switchTab(tab, btnEl) {
    document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.getElementById('tab-' + tab).classList.add('active');
    btnEl.classList.add('active');
}

function showCaixaDetail(c) {
    const fmoeda = (v) => parseFloat(v || 0).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
    const fdata = (d) => d ? new Date(d).toLocaleString('pt-BR') : '—';

    const html = `
        <div class="modal-body-scroll" style="padding: 10px;">
            <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; margin-bottom: 30px;">
                <div class="card p-3" style="background: rgba(255,255,255,0.02)">
                    <small style="color: var(--text-muted); text-transform:uppercase; font-size:10px;">Dados da Sessão</small>
                    <div style="margin-top:10px;">
                        <p style="margin:5px 0;"><strong>Operador:</strong> ${c.usuario_nome}</p>
                        <p style="margin:5px 0;"><strong>Abertura:</strong> ${fdata(c.data_abertura)}</p>
                        <p style="margin:5px 0;"><strong>Fechamento:</strong> ${fdata(c.data_fechamento)}</p>
                        <p style="margin:5px 0;"><strong>Status:</strong> ${c.status.toUpperCase()}</p>
                    </div>
                </div>
                <div class="card p-3" style="background: rgba(var(--primary-rgb), 0.05); border-color: rgba(var(--primary-rgb), 0.1)">
                    <small style="color: var(--primary); text-transform:uppercase; font-size:10px;">Resumo Financeiro</small>
                    <div style="margin-top:10px; font-size: 16px;">
                        <div style="display:flex; justify-content:space-between; margin-bottom:10px;">
                            <span>Faturamento:</span>
                            <span style="font-weight:800; color: #10b981;">${fmoeda(c.entradas)}</span>
                        </div>
                        <div style="display:flex; justify-content:space-between; margin-bottom:10px;">
                            <span>Saídas/Despesas:</span>
                            <span style="font-weight:800; color: #ef4444;">${fmoeda(c.saidas)}</span>
                        </div>
                        <hr style="border:0; border-top:1px solid var(--border); margin:10px 0;">
                        <div style="display:flex; justify-content:space-between;">
                            <strong>LUCRO LÍQUIDO:</strong>
                            <strong style="color: ${c.lucro >= 0 ? '#10b981' : '#ef4444'}">${fmoeda(c.lucro)}</strong>
                        </div>
                    </div>
                </div>
            </div>

            <h4 style="margin-bottom:15px; font-size: 14px; color: var(--text-muted); text-transform:uppercase;">Detalhamento de Entradas</h4>
            <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap:15px;">
                <div class="card p-3 text-center" style="border-top: 3px solid #6366f1;">
                    <i class="fas fa-chair mb-2" style="color:#6366f1"></i>
                    <span style="display:block; font-size:11px; color:var(--text-muted)">MAPA DE MESAS</span>
                    <strong style="font-size:18px;">${fmoeda(c.pagamentos_comanda)}</strong>
                </div>
                <div class="card p-3 text-center" style="border-top: 3px solid #0ea5e9;">
                    <i class="fas fa-cash-register mb-2" style="color:#0ea5e9"></i>
                    <span style="display:block; font-size:11px; color:var(--text-muted)">PDV / VENDA DIRETA</span>
                    <strong style="font-size:18px;">${fmoeda(c.pagamentos_pdv)}</strong>
                </div>
                <div class="card p-3 text-center" style="border-top: 3px solid #f59e0b;">
                    <i class="fas fa-hand-holding-usd mb-2" style="color:#f59e0b"></i>
                    <span style="display:block; font-size:11px; color:var(--text-muted)">ENTRADAS MANUAIS</span>
                    <strong style="font-size:18px;">${fmoeda(c.entradas_manuais)}</strong>
                </div>
            </div>
        </div>

        <div class="modal-footer" style="padding-top:20px; border-top: 1px solid var(--border);">
            <button class="btn-secondary" onclick="UI.closeModal()"><i class="fas fa-times"></i> Fechar Detalhes</button>
        </div>
    `;\n\n    UI.showModal(`Detalhes do Caixa — #${c.id}`, html);
}

async function showFluxoTurno(caixaId, operador, abertura) {
    UI.showModal(`📋 Fluxo do Turno — ${operador}`, `<div style="text-align:center;padding:30px;color:var(--text-muted);"><i class="fas fa-spinner fa-spin fa-2x"></i><p style="margin-top:10px">Carregando movimentações...</p></div>`);
    
    try {
        const res = await fetch(`<?php echo SITE_URL; ?>/api/relatorios/fluxo/${caixaId}`);
        const data = await res.json();
        
        if (!data.success) throw new Error(data.message || 'Erro ao carregar');
        
        const fmoeda = (v, tipo) => {
            const val = parseFloat(v || 0).toLocaleString('pt-BR', { minimumFractionDigits: 2 });
            return tipo === 'entrada' ? `<span style="color:#10b981;font-weight:800">+ R$ ${val}</span>` : `<span style="color:#ef4444;font-weight:800">- R$ ${val}</span>`;
        };
        
        const rows = data.itens.length === 0 
            ? `<tr><td colspan="3" style="text-align:center;padding:30px;color:var(--text-muted);">Nenhuma movimentação encontrada neste turno.</td></tr>`
            : data.itens.map(i => `
                <tr>
                    <td style="font-size:12px;color:var(--text-muted);white-space:nowrap;padding-left:20px">${new Date(i.data_movimentacao).toLocaleTimeString('pt-BR',{hour:'2-digit',minute:'2-digit'})}</td>
                    <td style="font-size:13px;padding:12px 15px">${i.descricao || '—'}</td>
                    <td style="text-align:right;padding-right:20px">${fmoeda(i.valor, i.tipo)}</td>
                </tr>
            `).join('');
        
        const total = data.itens.filter(i=>i.tipo==='entrada').reduce((s,i)=>s+parseFloat(i.valor||0),0);
        
        const html = `
            <div class="modal-body-scroll" style="padding:0">
                <div style="padding:20px;background:rgba(var(--primary-rgb),0.05);border-bottom:1px solid var(--border);">
                    <small style="color:var(--text-muted);display:block;margin-bottom:5px">FLUXO DO TURNO</small>
                    <strong style="font-size:16px;">${operador}</strong>
                    <span style="color:var(--text-muted);font-size:13px;display:block">Abertura: ${abertura}</span>
                </div>
                <div class="table-responsive">
                    <table class="premium-table" style="min-width:400px">
                        <thead>
                            <tr>
                                <th style="padding-left:20px">Hora</th>
                                <th>Descrição</th>
                                <th class="text-right" style="padding-right:20px">Valor</th>
                            </tr>
                        </thead>
                        <tbody>${rows}</tbody>
                        <tfoot>
                            <tr style="background:rgba(var(--primary-rgb),0.05)">
                                <td colspan="2" style="padding:15px 20px;font-weight:800;text-align:right">TOTAL ENTRADAS:</td>
                                <td style="padding:15px 20px;font-weight:900;color:#10b981;text-align:right">R$ ${total.toLocaleString('pt-BR',{minimumFractionDigits:2})}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            <div class="modal-footer" style="padding:20px;border-top:1px solid var(--border)">
                <button class="btn-secondary" onclick="UI.closeModal()"><i class="fas fa-times"></i> Fechar</button>
            </div>
        `;
        UI.showModal(`📋 Fluxo do Turno — ${operador}`, html);
    } catch(e) {
        UI.showToast('Erro ao carregar fluxo: ' + e.message, 'error');
    }
}

document.addEventListener('DOMContentLoaded', () => {
    Chart.defaults.color = '#94a3b8';
    Chart.defaults.font.family = "'Outfit', sans-serif";

    // Chart Movimentação
    const ctxMov = document.getElementById('chart-movimentacao').getContext('2d');
    const movData = <?php echo json_encode(array_values($movimentacoes)); ?>;

    if (movData && movData.length > 0) {
        new Chart(ctxMov, {
            type: 'line',
            data: {
                labels: movData.map(d => d.data ? d.data.split('-').reverse().slice(0,2).join('/') : ''),
                datasets: [
                    {
                        label: 'Entradas',
                        data: movData.map(d => parseFloat(d.entradas) || 0),
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        borderWidth: 3, fill: true, tension: 0.4,
                        pointRadius: 4, pointBackgroundColor: '#10b981'
                    },
                    {
                        label: 'Saídas',
                        data: movData.map(d => parseFloat(d.saidas) || 0),
                        borderColor: '#ef4444',
                        backgroundColor: 'rgba(239, 68, 68, 0.1)',
                        borderWidth: 3, fill: true, tension: 0.4,
                        pointRadius: 4, pointBackgroundColor: '#ef4444'
                    }
                ]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { position: 'top', align: 'end' } },
                scales: {
                    y: {
                        grid: { color: 'rgba(255,255,255,0.05)' }, border: { display: false },
                        ticks: { callback: v => 'R$ ' + v.toLocaleString('pt-BR') }
                    },
                    x: { grid: { display: false } }
                }
            }
        });
    } else {
        const w = ctxMov.canvas.width, h = ctxMov.canvas.height;
        ctxMov.font = "14px Outfit";
        ctxMov.fillStyle = "#64748b";
        ctxMov.textAlign = "center";
        ctxMov.fillText("Sem movimentações no período selecionado", w/2, h/2);
    }

    // Chart Métodos
    const ctxMet = document.getElementById('chart-metodos').getContext('2d');
    const metData = <?php echo json_encode($metodos); ?>;

    new Chart(ctxMet, {
        type: 'doughnut',
        data: {
            labels: metData.map(d => d.metodo_pagamento || 'Outros'),
            datasets: [{
                data: metData.map(d => parseFloat(d.total) || 0),
                backgroundColor: ['#6366f1','#10b981','#f59e0b','#0ea5e9','#ec4899','#8b5cf6'],
                borderWidth: 0, hoverOffset: 10
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom' } },
            cutout: '70%'
        }
    });
});
</script>
