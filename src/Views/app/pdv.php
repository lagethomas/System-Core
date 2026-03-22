<?php
/** @var string $title */
/** @var bool $caixa_aberto */

if (!$caixa_aberto): ?>
    <div style="display: flex; align-items: center; justify-content: center; height: 70vh;">
        <div class="card" style="text-align: center; max-width: 500px; padding: 50px; border-top: 5px solid #ef4444;">
            <div style="width: 80px; height: 80px; background: rgba(239, 68, 68, 0.1); color: #ef4444; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 25px; font-size: 32px;">
                <i class="fas fa-lock"></i>
            </div>
            <h2 style="font-size: 24px; margin-bottom: 10px; color: var(--text-main);">Caixa Fechado</h2>
            <p style="color: var(--text-muted); margin-bottom: 30px;">Identificamos que não há uma sessão de caixa aberta para o seu usuário. Abra o caixa para começar a vender.</p>
            <a href="<?php echo SITE_URL; ?>/app/caixa" class="btn-primary btn-block" style="padding: 15px;">
                <i class="fas fa-unlock"></i> IR PARA CONTROLE DE CAIXA
            </a>
        </div>
    </div>
<?php else: ?>

<div class="page-header">
    <div>
        <h2 style="color: var(--primary); margin-bottom: 5px;">Frente de Caixa (PDV)</h2>
        <p style="color: var(--text-muted);">Venda rápida de itens do balcão e conferência de troco.</p>
    </div>
    <div class="page-header-actions" style="display:flex; align-items:center; gap:20px">
        <div id="pdv-clock" style="font-weight: 700; font-size: 18px; color: var(--text-main);">00:00:00</div>
        <button class="btn-primary" onclick="PDV.openCaixaManager()"><i class="fas fa-cash-register"></i> Controle de Caixa</button>
    </div>
</div>

<div class="pdv-container">
    <!-- LEFT: PRODUCT SELECTION -->
    <div class="pdv-products-panel">
        <div class="card" style="margin-bottom: 20px;">
            <div style="position:relative;">
                <i class="fas fa-search" style="position:absolute; left: 15px; top: 50%; transform: translateY(-50%); color: var(--text-muted);"></i>
                <input type="text" id="pdv-search" 
                       class="form-control" 
                       placeholder="Pesquisar por NOME, CÓDIGO ou CATEGORIA..." 
                       style="padding-left: 45px; height: 50px; font-size: 16px; border-radius: 12px"
                       onkeyup="PDV.filter(this.value)">
            </div>
        </div>

        <div id="pdv-products-grid" class="pdv-grid">
            <!-- Loaded by JS -->
        </div>
    </div>

    <!-- RIGHT: CART & CHECKOUT -->
    <div class="pdv-cart-panel">
        <div class="card pdv-cart-card">
            <h3 style="font-size: 18px; margin-bottom: 20px; display:flex; align-items:center; gap: 10px;">
                <i class="fas fa-shopping-cart" style="color:var(--primary)"></i> Itens da Venda
            </h3>

            <div id="pdv-cart-items" class="pdv-cart-list">
                <div class="cart-empty-msg">Selecione produtos ao lado</div>
            </div>

            <div class="pdv-cart-totals">
                <div class="pdv-total-row main">
                    <span>TOTAL GERAL</span>
                    <span id="pdv-total">R$ 0,00</span>
                </div>
            </div>

            <button class="btn-primary btn-block btn-lg" style="height: 70px; font-size: 22px; border-radius: 15px;" onclick="PDV.openCheckoutModal()">
                <i class="fas fa-check-circle"></i> PAGAMENTO (F9)
            </button>
            
            <button class="btn-secondary btn-block" style="margin-top: 15px;" onclick="PDV.clearCart()">
                <i class="fas fa-trash-alt"></i> Cancelar Venda
            </button>
        </div>
    </div>
</div>

<script>
window.PDV = {
    products: [],
    cart: [],
    
    async init() {
        this.updateClock();
        setInterval(() => this.updateClock(), 1000);
        await this.loadProducts();
        document.onkeydown = (e) => { if (e.key === 'F9') { e.preventDefault(); this.openCheckoutModal(); } };
    },

    updateClock() {
        const el = document.getElementById('pdv-clock');
        if (el) el.innerText = new Date().toLocaleTimeString();
    },

    async loadProducts() {
        const res = await fetch('<?php echo SITE_URL; ?>/api/produtos/list');
        const data = await res.json();
        if (data && data.success) {
            this.products = data.data;
            this.renderProducts();
        }
    },

    filter(term) {
        const t = term.toLowerCase();
        this.renderProducts(this.products.filter(p => 
            p.nome.toLowerCase().includes(t) || 
            (p.codigo && p.codigo.toLowerCase().includes(t)) ||
            (p.categoria && p.categoria.toLowerCase().includes(t))
        ));
    },

    renderProducts(list = this.products) {
        const grid = document.getElementById('pdv-products-grid');
        if (!grid) return;
        grid.innerHTML = list.map(p => `
            <div class="pdv-item-card" onclick="PDV.addToCart(${p.id})">
                <div class="pdv-card-img">
                    ${p.imagem ? `<img src="<?php echo SITE_URL; ?>${p.imagem}">` : `<i class="fas fa-image"></i>`}
                </div>
                <div class="pdv-item-info">
                    <span class="pdv-item-code">#${p.codigo}</span>
                    <span class="pdv-item-name">${p.nome}</span>
                    <span class="pdv-item-price">R$ ${parseFloat(p.preco).toLocaleString('pt-BR', { minimumFractionDigits: 2 })}</span>
                </div>
            </div>
        `).join('');
    },

    addToCart(id) {
        const p = this.products.find(x => x.id === id);
        if (!p) return;
        const exists = this.cart.find(item => item.id === id);
        if (exists) exists.qty++; else this.cart.push({ ...p, qty: 1 });
        this.renderCart();
    },

    removeItem(id) { this.cart = this.cart.filter(i => i.id !== id); this.renderCart(); },

    updateQty(id, delta) {
        const item = this.cart.find(i => i.id === id);
        if (item) { item.qty += delta; if (item.qty <= 0) return this.removeItem(id); this.renderCart(); }
    },

    renderCart() {
        const container = document.getElementById('pdv-cart-items');
        if (!container) return;
        if (this.cart.length === 0) {
            container.innerHTML = `<div class="cart-empty-msg">Carrinho vazio</div>`;
            this.updateTotals(0); return;
        }
        container.innerHTML = this.cart.map(item => `
            <div class="pdv-cart-row">
                <div class="cart-item-info">
                    <div class="cart-item-name">${item.nome}</div>
                    <div class="cart-item-meta">#${item.codigo} • R$ ${parseFloat(item.preco).toLocaleString('pt-BR', { minimumFractionDigits: 2 })}</div>
                </div>
                <div class="cart-item-controls">
                    <button onclick="PDV.updateQty(${item.id}, -1)">-</button>
                    <span>${item.qty}</span>
                    <button onclick="PDV.updateQty(${item.id}, 1)">+</button>
                </div>
                <div class="cart-item-subtotal">R$ ${(item.qty * item.preco).toLocaleString('pt-BR', { minimumFractionDigits: 2 })}</div>
                <button class="cart-item-del" onclick="PDV.removeItem(${item.id})">&times;</button>
            </div>
        `).join('');
        const total = this.cart.reduce((sum, item) => sum + (item.qty * item.preco), 0);
        this.updateTotals(total);
    },

    updateTotals(total) {
        document.getElementById('pdv-total').innerText = total.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
    },

    clearCart() { this.cart = []; this.renderCart(); },

    openCheckoutModal() {
        if (this.cart.length === 0) return UI.showToast('Carrinho vazio!', 'error');
        const total = this.cart.reduce((s, i) => s + (i.qty * i.preco), 0);
        
        const html = `
            <div class="payment-modal-content">
                <div style="text-align:center; margin-bottom: 30px;">
                    <span style="font-size: 14px; color: var(--text-muted); text-transform:uppercase;">Total da Venda</span>
                    <h2 style="font-size: 48px; font-weight: 900; color: var(--primary); margin:0;">${total.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' })}</h2>
                </div>

                <div class="form-group mb-4">
                    <label class="form-label">Forma de Pagamento</label>
                    <div class="payment-methods-grid">
                        <label class="pay-method-opt">
                            <input type="radio" name="pay_method" value="Dinheiro" checked onchange="PDV.togglePaymentFields('Dinheiro')">
                            <span><i class="fas fa-money-bill-wave"></i> Dinheiro</span>
                        </label>
                        <label class="pay-method-opt">
                            <input type="radio" name="pay_method" value="PIX" onchange="PDV.togglePaymentFields('PIX')">
                            <span><i class="fas fa-qrcode"></i> PIX</span>
                        </label>
                        <label class="pay-method-opt">
                            <input type="radio" name="pay_method" value="Cartão" onchange="PDV.togglePaymentFields('Cartao')">
                            <span><i class="fas fa-credit-card"></i> Cartão</span>
                        </label>
                    </div>
                </div>

                <div id="dinheiro-fields">
                    <div class="form-group">
                        <label class="form-label">Valor Recebido (R$)</label>
                        <input type="text" id="valor_recebido" class="form-control mask-money" style="height: 60px; font-size: 24px; text-align:center;" onkeyup="PDV.calcTroco(${total})">
                    </div>
                    <div id="troco-display" style="margin-top:20px; text-align:center; padding: 15px; background: rgba(var(--primary-rgb), 0.1); border-radius: 12px; display:none;">
                        <span style="display:block; font-size:12px; color:var(--text-muted)">TROCO</span>
                        <span id="troco-value" style="font-size: 32px; font-weight: 800; color:var(--primary)">R$ 0,00</span>
                    </div>
                </div>

                <div class="modal-footer" style="padding-top:30px">
                    <button class="btn-secondary" onclick="UI.closeModal()">Cancelar</button>
                    <button class="btn-primary" style="flex:1; height: 60px; font-size: 18px" onclick="PDV.executeCheckout(${total})">CONFIRMAR E FINALIZAR</button>
                </div>
            </div>
        `;

        UI.showModal('Finalizar Venda', html);
    },

    togglePaymentFields(method) {
        document.getElementById('dinheiro-fields').style.display = method === 'Dinheiro' ? 'block' : 'none';
        if (method !== 'Dinheiro') document.getElementById('valor_recebido').value = '';
    },

    calcTroco(total) {
        const recebido = parseFloat(document.getElementById('valor_recebido').value.replace(',', '.')) || 0;
        const troco = recebido - total;
        const display = document.getElementById('troco-display');
        if (troco > 0) {
            display.style.display = 'block';
            document.getElementById('troco-value').innerText = troco.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
        } else {
            display.style.display = 'none';
        }
    },

    async executeCheckout(total) {
        const metodo = document.querySelector('input[name="pay_method"]:checked').value;
        const res = await fetch('<?php echo SITE_URL; ?>/api/pdv/checkout', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ 
                total, 
                metodo, 
                itens: this.cart,
                nonce: '<?php echo $nonce; ?>'
            })
        });
        const data = await res.json();
        if (data.success) {
            UI.showToast('Venda Finalizada!');
            UI.closeModal();
            this.clearCart();
        }
    },

    openCaixaManager() {
        window.location.href = '<?php echo SITE_URL; ?>/app/caixa';
    }
};

PDV.init();
</script>
<?php endif; ?>
