<?php
/** @var bool $caixa_aberto */

if (!$caixa_aberto): ?>
    <div style="display: flex; align-items: center; justify-content: center; height: 70vh;">
        <div class="card" style="text-align: center; max-width: 500px; padding: 50px; border-top: 5px solid #ef4444;">
            <div style="width: 80px; height: 80px; background: rgba(239, 68, 68, 0.1); color: #ef4444; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 25px; font-size: 32px;">
                <i class="fas fa-lock"></i>
            </div>
            <h2 style="font-size: 24px; margin-bottom: 10px; color: var(--text-main);">Caixa Fechado</h2>
            <p style="color: var(--text-muted); margin-bottom: 30px;">O Mapa de Mesas requer um caixa aberto para registrar lançamentos financeiros. Abra seu caixa para continuar.</p>
            <a href="<?php echo SITE_URL; ?>/app/caixa" class="btn-primary btn-block" style="padding: 15px;">
                <i class="fas fa-unlock"></i> IR PARA CONTROLE DE CAIXA
            </a>
        </div>
    </div>
<?php else: ?>
<?php 
/** @var string $SITE_URL */
/** @var string $current_page */
?>

<!-- PAGE HEADER -->
<div class="page-header">
    <div>
        <h2 style="color: var(--primary); margin-bottom: 5px;">Mapa de Mesas</h2>
        <p style="color: var(--text-muted);">Clique em uma mesa para abrir a comanda.</p>
    </div>
    <div class="page-header-actions">
        <?php if ($is_admin): ?>
        <button class="btn-primary" id="btn-nova-mesa">
            <i class="fas fa-plus"></i> Nova Mesa
        </button>
        <?php endif; ?>
    </div>
</div>

<!-- MESAS GRID -->
<div id="mesas-grid" class="mesas-grid">
    <!-- Estado Inicial: Carregando -->
    <div class="loading-state" style="text-align:center; color: var(--text-muted); padding: 60px; grid-column: 1/-1;">
        <i class="fas fa-spinner fa-spin fa-2x" style="margin-bottom: 15px; color: var(--primary);"></i>
        <p>Carregando mesas...</p>
    </div>
</div>

<script>
// Configurações globais para o módulo
window.SITE_URL = '<?php echo rtrim(SITE_URL, '/'); ?>';
window.COM_NONCES = <?php echo json_encode($nonces); ?>;
window.IS_ADMIN = <?php echo $is_admin ? 'true' : 'false'; ?>;

// Tenta carregar o script do módulo manualmente se ele não foi carregado pelo footer
(function() {
    console.log('Comanda: Iniciando verificação de script...');
    
    // Vincula o botão de nova mesa via listener (não depende do Comanda estar definido no clique)
    const btn = document.getElementById('btn-nova-mesa');
    if (btn) {
        btn.addEventListener('click', function() {
            if (window.Comanda) {
                window.Comanda.addMesa();
            } else {
                console.error('Comanda: Script ainda não carregado ou falhou.');
                UI.showToast('O sistema ainda está carregando. Aguarde um instante...', 'warning');
            }
        });
    }

    // Se o Comanda não estiver definido em 2 segundos, tenta forçar o carregamento do script
    setTimeout(() => {
        if (!window.Comanda) {
            console.warn('Comanda: Script não detectado. Forçando carregamento...');
            const script = document.createElement('script');
            script.src = window.SITE_URL + '/assets/js/modules/comanda.js?v=' + Date.now();
            document.body.appendChild(script);
        }
    }, 1000);
})();
</script>
<?php endif; ?>
