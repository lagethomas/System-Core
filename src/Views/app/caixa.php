<?php
/** @var array|null $caixa */
/** @var array $historico */
/** @var array $movimentacoes */
?>

<div class="page-header">
    <div>
        <h2 style="color: var(--primary); margin-bottom: 5px;">Controle de Caixa</h2>
        <p style="color: var(--text-muted);">Administre as aberturas, fechamentos e movimentações de turno.</p>
    </div>
</div>

<div class="caixa-dashboard">
    <!-- TOP SECTION: MAIN ACTION -->
    <div class="caixa-status-main">
        <?php if ($caixa): ?>
            <div class="status-card open">
                <div class="status-header">
                    <div class="status-badge"><i class="fas fa-circle"></i> CAIXA ABERTO</div>
                    <div class="status-time">Desde <?php echo date('d/m H:i', strtotime($caixa['data_abertura'])); ?></div>
                </div>
                <div class="status-body">
                    <div class="balance-item">
                        <span>Saldo Inicial</span>
                        <h3>R$ <?php echo number_format($caixa['saldo_inicial'], 2, ',', '.'); ?></h3>
                    </div>
                    <div class="balance-divider"></div>
                    <div class="balance-item">
                        <span>Movimentação (Entradas - Saídas)</span>
                        <?php 
                            $e = array_sum(array_map(fn($m) => $m['tipo'] == 'entrada' ? $m['valor'] : 0, $movimentacoes));
                            $s = array_sum(array_map(fn($m) => $m['tipo'] == 'saida' ? $m['valor'] : 0, $movimentacoes));
                            $mov = $e - $s;
                        ?>
                        <h3 style="color: <?php echo $mov >= 0 ? '#10b981' : '#ef4444'; ?>">
                            <?php echo $mov >= 0 ? '+' : ''; ?> R$ <?php echo number_format($mov, 2, ',', '.'); ?>
                        </h3>
                    </div>
                </div>
                <button class="btn-fechar-caixa" onclick="fecharCaixa()">
                    <i class="fas fa-lock-open"></i> ENCERRAR TURNO AGORA
                </button>
            </div>
        <?php else: ?>
            <div class="status-card closed">
                <div class="status-icon-big"><i class="fas fa-cash-register"></i></div>
                <h2>Caixa está Fechado</h2>
                <p>Nenhuma sessão ativa encontrada. Inicie um novo turno para operar o sistema.</p>
                <button class="btn-abrir-caixa" onclick="abrirCaixa()">
                    <i class="fas fa-plus-circle"></i> ABRIR NOVO CAIXA
                </button>
            </div>
        <?php endif; ?>
    </div>

    <!-- MIDDLE SECTION: DETAILS -->
    <div class="caixa-details-grid">
        <!-- MOVIMENTAÇÕES -->
        <div class="card p-0 overflow-hidden">
            <div class="card-p-header">
                <h3><i class="fas fa-exchange-alt"></i> Fluxo do Turno Atual</h3>
            </div>
            <div class="table-responsive" style="max-height: 400px;">
                <table class="premium-table">
                    <thead>
                        <tr>
                            <th>Hora</th>
                            <th>Descrição</th>
                            <th class="text-right">Valor</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($movimentacoes)): ?>
                            <tr><td colspan="3" class="text-center py-5">Nenhum lançamento nesta sessão.</td></tr>
                        <?php else: ?>
                            <?php foreach ($movimentacoes as $m): ?>
                                <tr>
                                    <td class="text-muted" style="font-size:11px"><?php echo date('H:i', strtotime($m['data_movimentacao'])); ?></td>
                                    <td style="font-weight:600"><?php echo htmlspecialchars($m['descricao']); ?></td>
                                    <td class="text-right" style="color: <?php echo $m['tipo'] == 'entrada' ? '#10b981' : '#ef4444'; ?>; font-weight:800">
                                        <?php echo $m['tipo'] == 'entrada' ? '+' : '-'; ?> R$ <?php echo number_format($m['valor'], 2, ',', '.'); ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- HISTORICO -->
        <div class="card p-0 overflow-hidden">
             <div class="card-p-header">
                <h3><i class="fas fa-history"></i> Histórico de Fechamentos</h3>
            </div>
            <div class="table-responsive" style="max-height: 400px;">
                <table class="premium-table">
                    <thead>
                        <tr>
                            <th>Operador</th>
                            <th>Abertura</th>
                            <th>Saldo Final</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($historico as $h): ?>
                            <tr>
                                <td>
                                    <div style="font-weight:700"><?php echo $h['usuario_nome']; ?></div>
                                    <span class="badge" style="font-size:9px"><?php echo strtoupper($h['status']); ?></span>
                                </td>
                                <td class="text-muted" style="font-size:12px"><?php echo date('d/m H:i', strtotime($h['data_abertura'])); ?></td>
                                <td style="font-weight:800">R$ <?php echo number_format($h['saldo_final'] ?? $h['saldo_inicial'], 2, ',', '.'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
function abrirCaixa() {
    const html = `
        <form id="form-abrir-caixa">
            <div class="modal-body-scroll" style="padding: 10px;">
                <input type="hidden" name="nonce" value="<?php echo $nonce_abrir; ?>">
                
                <div class="form-group mb-4">
                    <label class="form-label">Saldo Inicial em Dinheiro (Fundo de Caixa)</label>
                    <input type="text" name="saldo_inicial" class="form-control mask-money" style="height: 60px; font-size: 24px; text-align:center;" required placeholder="0,00">
                    <small style="color: var(--text-muted); display:block; margin-top:10px;">Informe quanto de dinheiro há na gaveta no momento da abertura.</small>
                </div>
            </div>
            <div class="modal-footer" style="padding-top:20px; border-top: 1px solid var(--border);">
                <button type="button" class="btn-secondary" onclick="UI.closeModal()">Cancelar</button>
                <button type="submit" class="btn-primary">CONFIRMAR ABERTURA</button>
            </div>
        </form>
    `;
    UI.showModal('Abrir Nova Sessão de Caixa', html);

    document.getElementById('form-abrir-caixa').onsubmit = async (e) => {
        e.preventDefault();
        const formData = new FormData(e.target);
        const res = await UI.request('<?php echo SITE_URL; ?>/api/caixa/abrir', Object.fromEntries(formData.entries()));
        if (res && res.success) {
            UI.showToast('Caixa aberto com sucesso!');
            setTimeout(() => window.location.reload(), 1200);
        }
    };
}

async function fecharCaixa() {
    if (await UI.confirm('Deseja realmente FECHAR o caixa agora? Todas as movimentações da sessão serão consolidadas.', { 
        title: 'Encerrar Turno',
        confirmText: 'Sim, Fechar Caixa', 
        type: 'danger', 
        icon: 'fa-lock' 
    })) {
        const res = await UI.request('<?php echo SITE_URL; ?>/api/caixa/fechar', {
            nonce: '<?php echo $nonce_fechar; ?>'
        });
        if (res && res.success) {
            UI.showToast('Caixa fechado com sucesso!');
            setTimeout(() => window.location.reload(), 1200);
        }
    }
}
</script>
