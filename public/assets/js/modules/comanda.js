/**
 * Comanda Module - comanda.js
 * 
 * Padrão do sistema: utiliza UI.showModal(), UI.showToast(), UI.confirm()
 */

window.Comanda = {

    config: {
        produtos: [], 
        filtroProduto: '' 
    },

    mesas: [],
    mesaAtual: null,
    itensAtual: [],

    formatarMoeda(val) {
        return parseFloat(val || 0).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
    },

    async init() {
        console.log('Comanda Module: Iniciando módulo...');
        await this.carregarProdutos();
        await this.carregarMesas();
    },

    async carregarProdutos() {
        const res = await this.apiGet('/api/produtos/list');
        if (res && res.success) {
            this.config.produtos = res.data;
        }
    },

    async apiGet(path) {
        try {
            const url = `${window.SITE_URL}${path}`;
            const res = await fetch(url);
            if (!res.ok) throw new Error('Status ' + res.status);
            return await res.json();
        } catch (e) {
            console.error('Comanda API Error:', e);
            return null;
        }
    },

    async apiPost(path, body = null, nonce = null) {
        try {
            const url = `${window.SITE_URL}${path}`;
            const opts = {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' }
            };
            const payload = body || {};
            if (nonce) payload.nonce = nonce;
            opts.body = JSON.stringify(payload);
            
            const res = await fetch(url, opts);
            if (!res.ok) throw new Error('Status ' + res.status);
            return await res.json();
        } catch (e) {
            console.error('Comanda POST Error:', e);
            return null;
        }
    },

    async carregarMesas() {
        const data = await this.apiGet('/api/comanda/mesas');
        if (data && data.success) {
            this.mesas = data.data;
        }
        this.renderMesas();
    },

    renderMesas() {
        const grid = document.getElementById('mesas-grid');
        if (!grid) return;

        if (!this.mesas.length) {
            grid.innerHTML = `
                <div style="text-align:center; color: var(--text-muted); padding: 60px; grid-column: 1/-1;">
                    <i class="fas fa-chair fa-3x" style="margin-bottom:15px; opacity:0.1; display:block; color:var(--primary);"></i>
                    <p style="font-size:18px; font-weight:600; color:var(--text-main);">Nenhuma mesa criada</p>
                </div>
            `;
            return;
        }

        grid.innerHTML = this.mesas.map(m => `
            <div class="mesa-card ${m.status}" onclick="Comanda.abrirComanda(${m.id})">
                ${window.IS_ADMIN ? `<button class="btn-remove-mesa" onclick="event.stopPropagation(); Comanda.removerMesa(${m.id}, ${m.numero})" title="Remover Mesa">&times;</button>` : ''}
                <i class="fas ${m.status === 'ocupada' ? 'fa-users' : 'fa-chair'} mesa-icon"></i>
                <div class="mesa-numero">Mesa ${m.numero}</div>
                <span class="mesa-status-badge">${m.status === 'ocupada' ? 'Ocupada' : 'Livre'}</span>
                ${m.status === 'ocupada' ? `
                    <div class="mesa-total">${this.formatarMoeda(m.total)}</div>
                    <div class="mesa-itens">${m.itens_count || 0} itens</div>
                ` : ''}
            </div>
        `).join('');
    },

    async removerMesa(id, numero) {
        if (await UI.confirm(`Remover permanentemente a <strong>Mesa ${numero}</strong>?`)) {
            const nonce = window.COM_NONCES?.remover_mesa;
            const res = await fetch(`${window.SITE_URL}/api/comanda/mesa/${id}?nonce=${nonce}`, { method: 'DELETE' });
            const data = await res.json();
            if (data && data.success) {
                UI.showToast(`Mesa ${numero} removida.`);
                this.carregarMesas();
            } else {
                UI.showToast(data.message || 'Erro', 'error');
            }
        }
    },

    async addMesa() {
        const data = await this.apiPost('/api/comanda/mesas/add', {}, window.COM_NONCES?.add_mesa);
        if (data && data.success) {
            UI.showToast(`Mesa ${data.numero} criada!`);
            this.carregarMesas();
        }
    },

    async abrirComanda(id) {
        this.mesaAtual = this.mesas.find(m => m.id === id);
        if (!this.mesaAtual) return;

        const data = await this.apiGet(`/api/comanda/mesa/${id}`);
        this.itensAtual = (data && data.success) ? (data.itens || []) : [];

        this.config.filtroProduto = ''; // Reseta filtro ao abrir
        this.renderModal();
    },

    // Filtro de produtos dinâmico
    filtrarProdutos(termo) {
        this.config.filtroProduto = termo.toLowerCase();
        this.renderQuickAdd();
    },

    renderQuickAdd() {
        const container = document.getElementById('quick-add-container');
        if (!container) return;

        const termo = this.config.filtroProduto;
        const filtrados = this.config.produtos.filter(p => 
            p.nome.toLowerCase().includes(termo) || 
            (p.categoria && p.categoria.toLowerCase().includes(termo))
        );

        if (filtrados.length === 0) {
            container.innerHTML = `<p style="text-align:center; padding: 20px; color:var(--text-muted); font-size:12px;">Nenhum produto encontrado.</p>`;
            return;
        }

        container.innerHTML = filtrados.map(p => `
            <button class="btn-produto-rapido" onclick="Comanda.lancarItem('${p.nome.replace(/'/g, "\\'")}', ${p.preco})">
                <div style="display:flex; flex-direction:column;">
                    <span style="font-weight:600;">${p.nome}</span>
                    <small style="font-size:10px; color:var(--text-muted)">${p.categoria || 'Geral'}</small>
                </div>
                <span class="btn-produto-preco">${this.formatarMoeda(p.preco)}</span>
            </button>
        `).join('');
    },

    renderModal() {
        const m = this.mesaAtual;
        const itens = this.itensAtual;
        const total = itens.reduce((s, i) => s + (parseFloat(i.preco) * (i.quantidade || 1)), 0);

        let itensHtml = (itens.length === 0) 
            ? `<div class="empty-comanda"><i class="fas fa-receipt"></i><p>A comanda está vazia.</p></div>`
            : itens.map(it => `
                <div class="comanda-item-row">
                    <div class="comanda-item-info">
                        <span class="comanda-item-nome">${it.produto_nome}</span>
                        <span class="comanda-item-qtd">Quantidade: ${it.quantidade || 1}</span>
                    </div>
                    <span class="comanda-item-preco" style="font-size: 16px;">${this.formatarMoeda(parseFloat(it.preco) * (it.quantidade || 1))}</span>
                    <button class="btn-remove-item" onclick="Comanda.removerItem(${it.id})" title="Remover item">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </div>
            `).join('');

        const btnAcao = m.status === 'livre'
            ? `<button class="btn-primary" style="width:100%; padding: 15px; font-size: 16px; border-radius:12px;" onclick="Comanda.abrirMesa(${m.id})"><i class="fas fa-play"></i> INICIAR ATENDIMENTO</button>`
            : `<button class="btn-danger" style="width:100%; padding: 15px; font-size: 16px; margin-top:10px; border-radius:12px;" onclick="Comanda.fecharMesa(${m.id})"><i class="fas fa-check-circle"></i> FECHAR CONTA</button>`;

        const html = `
            <div class="modal-body-scroll" style="max-height: 70vh;">
                <div class="comanda-modal-layout">
                    <!-- COLUNA ESQUERDA: EXTRATO -->
                    <div style="display:flex; flex-direction:column; justify-content: space-between;">
                        <div class="comanda-itens-list">
                            <div style="margin-bottom: 20px; border-left: 3px solid var(--primary); padding-left: 15px;">
                                <h4 style="margin:0; font-size: 12px; text-transform: uppercase; color: var(--text-muted);">Extrato da Mesa</h4>
                                <span style="font-size: 20px; font-weight: 800; color: var(--text-main);">Mesa ${m.numero}</span>
                            </div>
                            <div class="comanda-items-container">
                                ${itensHtml}
                            </div>
                        </div>
                        <div>
                            <div class="comanda-total-bar" style="background: rgba(var(--primary-rgb), 0.1); padding: 15px; border-radius: 12px; margin: 15px 0; text-align:center;">
                                <span style="font-size: 13px; font-weight: 600; color: var(--text-muted); display:block; margin-bottom:5px;">VALOR TOTAL</span>
                                <span style="font-size: 32px; font-weight: 900; color: var(--primary);">${this.formatarMoeda(total)}</span>
                            </div>
                            ${btnAcao}
                        </div>
                    </div>

                    <!-- COLUNA DIREITA: LANÇAMENTO RÁPIDO -->
                    <div class="comanda-actions-panel" style="border-left: 1px solid var(--border); padding-left: 20px;">
                        <h4 class="comanda-actions-label" style="display:flex; align-items:center; gap: 10px; margin-bottom: 20px;">
                            <i class="fas fa-bolt" style="color:var(--primary)"></i> Lançamento Rápido
                        </h4>
                        
                        <div style="position:relative; margin-bottom: 15px;">
                            <i class="fas fa-search" style="position:absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-size: 12px;"></i>
                            <input type="text" 
                                id="search-produto" 
                                class="form-control" 
                                placeholder="Buscar produto..." 
                                style="padding-left: 35px; height: 45px; font-size: 14px; border-radius: 12px;"
                                onkeyup="Comanda.filtrarProdutos(this.value)">
                        </div>

                        <div id="quick-add-container" class="quick-add-scroll" style="max-height: 350px;">
                            <!-- Carregado via JS filtrarProdutos -->
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer" style="padding: 15px 25px; border-top: 1px solid var(--border);">
                <button class="btn-secondary" onclick="UI.closeModal()" style="border-radius:10px;"><i class="fas fa-times"></i> Fechar Janela</button>
            </div>
        `;

        UI.showModal(`Gerenciar Mesa ${m.numero}`, html);
        this.renderQuickAdd();
    },

    async abrirMesa(id) {
        const nonce = window.COM_NONCES?.abrir_mesa;
        await this.apiPost(`/api/comanda/mesa/abrir/${id}`, {}, nonce);
        const m = this.mesas.find(x => x.id === id);
        if (m) { m.status = 'ocupada'; m.total = 0; m.itens_count = 0; }
        this.renderModal();
        this.renderMesas();
    },

    async lancarItem(nome, preco) {
        if (this.mesaAtual.status === 'livre') {
            await this.apiPost(`/api/comanda/mesa/abrir/${this.mesaAtual.id}`, {}, window.COM_NONCES?.abrir_mesa);
            const m = this.mesas.find(x => x.id === this.mesaAtual.id);
            if (m) m.status = 'ocupada';
        }

        const nonce = window.COM_NONCES?.lancar_item;
        await this.apiPost(`/api/comanda/mesa/item/${this.mesaAtual.id}`, { nome, preco, quantidade: 1 }, nonce);
        const res = await this.apiGet(`/api/comanda/mesa/${this.mesaAtual.id}`);
        this.itensAtual = (res && res.success) ? (res.itens || []) : [...this.itensAtual, { id: Date.now(), produto_nome: nome, preco, quantidade: 1 }];

        const m = this.mesas.find(x => x.id === this.mesaAtual.id);
        if (m) {
            m.total = this.itensAtual.reduce((s, i) => s + (parseFloat(i.preco) * (i.quantidade || 1)), 0);
            m.itens_count = this.itensAtual.length;
        }

        this.renderModal();
        this.renderMesas();
    },

    async removerItem(itemId) {
        if (await UI.confirm('Remover este item?', { icon: 'fa-trash' })) {
            const nonce = window.COM_NONCES?.remover_item;
            await fetch(`${window.SITE_URL}/api/comanda/item/${itemId}?nonce=${nonce}`, { method: 'DELETE' });
            this.itensAtual = this.itensAtual.filter(i => i.id !== itemId);
            const m = this.mesas.find(x => x.id === this.mesaAtual.id);
            if (m) {
                m.total = this.itensAtual.reduce((s, i) => s + (parseFloat(i.preco) * (i.quantidade || 1)), 0);
                m.itens_count = this.itensAtual.length;
            }
            this.renderModal();
            this.renderMesas();
        }
    },

    togglePaymentFields(method) {
        const df = document.getElementById('dinheiro-fields-mesa');
        if (df) df.style.display = method === 'Dinheiro' ? 'block' : 'none';
        if (method !== 'Dinheiro') {
            const vr = document.getElementById('valor_recebido_mesa');
            if (vr) vr.value = '';
            const td = document.getElementById('troco-display-mesa');
            if (td) td.style.display = 'none';
        }
    },

    calcTroco(total) {
        const vr = document.getElementById('valor_recebido_mesa');
        const td = document.getElementById('troco-display-mesa');
        const tv = document.getElementById('troco-value-mesa');
        if (!vr || !td || !tv) return;

        const recebido = parseFloat(vr.value.replace(',', '.')) || 0;
        const troco = recebido - total;
        if (troco > 0) {
            td.style.display = 'block';
            tv.innerText = this.formatarMoeda(troco);
        } else {
            td.style.display = 'none';
        }
    },

    async fecharMesa(id) {
        const subtotal = this.itensAtual.reduce((s, i) => s + (parseFloat(i.preco) * (i.quantidade || 1)), 0);
        
        const html = `
            <div class="modal-body-scroll" style="padding: 20px;">
                <div class="payment-modal-content">
                    <div style="text-align:center; margin-bottom: 30px;">
                        <span style="font-size: 14px; color: var(--text-muted); text-transform:uppercase;">Total a Pagar</span>
                        <h2 id="mesa-final-total" style="font-size: 48px; font-weight: 900; color: var(--primary); margin:0;">${this.formatarMoeda(subtotal)}</h2>
                    </div>

                    <div class="form-group mb-4" style="background: rgba(255,255,255,0.03); padding: 20px; border-radius: 15px; border: 1px solid var(--border); margin-bottom: 25px;">
                        <label class="form-label" style="font-size:11px; font-weight:800; margin-bottom:15px; display:block; text-transform:uppercase; color:var(--primary); letter-spacing: 1px;">Taxas e Serviços</label>
                        <div class="form-grid-2">
                            <div class="form-group">
                                <label class="form-label">Cover (R$)</label>
                                <input type="text" id="mesa_cover" class="form-control mask-money" placeholder="0,00" onkeyup="Comanda.recalcFechamento(${subtotal})">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Taxa Serviço (Garçom)</label>
                                <div style="display:flex; gap: 5px;">
                                    <input type="number" id="mesa_taxa" class="form-control" style="flex:1" placeholder="0" onkeyup="Comanda.recalcFechamento(${subtotal})">
                                    <select id="mesa_taxa_tipo" class="form-control" style="width: 70px;" onchange="Comanda.recalcFechamento(${subtotal})">
                                        <option value="porcentagem">%</option>
                                        <option value="fixo">R$</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group mb-4">
                        <label class="form-label" style="font-size:13px; font-weight:600; margin-bottom:15px; display:block">Forma de Pagamento</label>
                        <div class="payment-methods-grid">
                            <label class="pay-method-opt">
                                <input type="radio" name="pay_method_mesa" value="Dinheiro" checked onchange="Comanda.togglePaymentFields('Dinheiro')">
                                <span><i class="fas fa-money-bill-wave"></i> Dinheiro</span>
                            </label>
                            <label class="pay-method-opt">
                                <input type="radio" name="pay_method_mesa" value="PIX" onchange="Comanda.togglePaymentFields('PIX')">
                                <span><i class="fas fa-qrcode"></i> PIX</span>
                            </label>
                            <label class="pay-method-opt">
                                <input type="radio" name="pay_method_mesa" value="Cartão" onchange="Comanda.togglePaymentFields('Cartao')">
                                <span><i class="fas fa-credit-card"></i> Cartão</span>
                            </label>
                        </div>
                    </div>

                    <div id="dinheiro-fields-mesa">
                        <div class="form-group">
                            <label class="form-label">Valor Recebido (Gaveta)</label>
                            <input type="text" id="valor_recebido_mesa" class="form-control mask-money" style="height: 65px; font-size: 28px; text-align:center; border-radius: 15px; font-weight:800;" placeholder="0,00" onkeyup="Comanda.calcTroco()">
                        </div>
                        <div id="troco-display-mesa" style="margin-top:20px; text-align:center; padding: 20px; background: rgba(var(--primary-rgb), 0.1); border-radius: 15px; display:none;">
                            <span style="display:block; font-size:12px; color:var(--text-muted); font-weight:600; text-transform:uppercase; margin-bottom:5px;">TROCO PARA O CLIENTE</span>
                            <span id="troco-value-mesa" style="font-size: 36px; font-weight: 900; color:var(--primary)">R$ 0,00</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer" style="padding: 25px; border-top: 1px solid var(--border);">
                <button class="btn-secondary" onclick="UI.closeModal()" style="padding: 15px 30px; border-radius:12px;">Cancelar</button>
                <button class="btn-primary" style="flex:1; height: 60px; font-size: 20px; border-radius: 15px; font-weight:800" onclick="Comanda.confirmarFechamento(${id}, ${subtotal})">
                    <i class="fas fa-check-circle"></i> FINALIZAR CONTA
                </button>
            </div>
        `;

        UI.showModal(`Finalizar Atendimento — Mesa ${this.mesaAtual.numero}`, html);
        this.recalcFechamento(subtotal);
    },

    recalcFechamento(subtotal) {
        let cover = parseFloat(document.getElementById('mesa_cover').value.replace(',', '.')) || 0;
        let taxaVal = parseFloat(document.getElementById('mesa_taxa').value) || 0;
        let taxaTipo = document.getElementById('mesa_taxa_tipo').value;
        
        let taxaFinal = (taxaTipo === 'porcentagem') ? (subtotal * (taxaVal / 100)) : taxaVal;
        let finalTotal = subtotal + cover + taxaFinal;
        
        document.getElementById('mesa-final-total').innerText = this.formatarMoeda(finalTotal);
        this._currentFinalTotal = finalTotal;
        this.calcTroco();
    },

    calcTroco() {
        const vr = document.getElementById('valor_recebido_mesa');
        const td = document.getElementById('troco-display-mesa');
        const tv = document.getElementById('troco-value-mesa');
        if (!vr || !td || !tv) return;

        const total = this._currentFinalTotal || 0;
        const recebido = parseFloat(vr.value.replace('.', '').replace(',', '.')) || 0;
        const troco = recebido - total;
        if (troco > 0) {
            td.style.display = 'block';
            tv.innerText = this.formatarMoeda(troco);
        } else {
            td.style.display = 'none';
        }
    },

    async confirmarFechamento(id, subtotal) {
        const metodo = document.querySelector('input[name="pay_method_mesa"]:checked').value;
        
        let cover = parseFloat(document.getElementById('mesa_cover').value.replace(',', '.')) || 0;
        let taxaVal = parseFloat(document.getElementById('mesa_taxa').value) || 0;
        let taxaTipo = document.getElementById('mesa_taxa_tipo').value;
        let taxaFinal = (taxaTipo === 'porcentagem') ? (subtotal * (taxaVal / 100)) : taxaVal;

        const nonce = window.COM_NONCES?.fechar_mesa;
        
        const res = await this.apiPost(`/api/comanda/mesa/fechar/${id}`, { 
            metodo, 
            taxa_servico: taxaFinal,
            cover: cover
        }, nonce);

        if (res && res.success) {
            UI.showToast('Mesa finalizada com sucesso!');
            const m = this.mesas.find(x => x.id === id);
            if (m) { m.status = 'livre'; m.total = 0; m.itens_count = 0; }
            this.itensAtual = [];
            UI.closeModal();
            this.renderMesas();
        } else {
            UI.showToast('Erro ao fechar mesa', 'error');
        }
    }
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => Comanda.init());
} else {
    Comanda.init();
}
