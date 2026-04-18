/**
 * Companies Module Logic
 */

window.COMPANIES_DATA = window.COMPANIES_DATA || {};

window.openCompanyModal = function(data = null) {
    const plans = window.COMPANIES_DATA.plans || [];
    const owners = window.COMPANIES_DATA.owners || [];
    const nonces = window.COMPANIES_DATA.nonces || {};
    
    const planOptions = plans.map(p => `<option value="${p.id}" ${data && data.plan_id == p.id ? 'selected' : ''}>${p.name}</option>`).join('');

    const html = `
        <form action="${window.SITE_URL}/api/admin/companies/save" method="POST" class="ajax-form">
            <input type="hidden" name="nonce" value="${nonces.save || ''}">
            <input type="hidden" name="id" value="${data ? data.id : ''}">
            
            <div class="floating-group">
                <input type="text" name="name" class="form-control" value="${data ? data.name : ''}" required placeholder=" " onkeyup="${!data ? 'suggestCompanySlug(this.value)' : ''}">
                <label class="floating-label">Nome da Empresa</label>
            </div>

            <div class="floating-group mt-4">
                <input type="text" name="slug" id="company_slug" class="form-control" value="${data ? data.slug : ''}" required placeholder=" ">
                <label class="floating-label">Slug (URL do Portal)</label>
            </div>

            <div class="floating-group mt-4">
                <input type="text" name="custom_domain" class="form-control" value="${data ? data.custom_domain || '' : ''}" placeholder=" ">
                <label class="floating-label">Domínio Customizado (Opcional)</label>
            </div>

            <div class="form-grid-2 mt-4">
                <div class="floating-group">
                    <input type="text" name="phone" class="form-control mask-phone" value="${data ? data.phone || '' : ''}" placeholder=" ">
                    <label class="floating-label">WhatsApp</label>
                </div>
                <div class="floating-group">
                    <input type="email" name="email" class="form-control" value="${data ? data.email || '' : ''}" placeholder=" ">
                    <label class="floating-label">E-mail</label>
                </div>
            </div>

            <div class="floating-group mt-4">
                <select name="owner_id" class="form-control" placeholder=" ">
                    <option value="">-- Sem Proprietário (Opcional) --</option>
                    ${owners.map(u => `<option value="${u.id}" ${u.company_id == data?.id || (data && data.master_manager_id == u.id) ? 'selected' : ''}>${u.name}</option>`).join('')}
                </select>
                <label class="floating-label">Proprietário (Responsável)</label>
            </div>

            <div class="form-grid-2 mt-4">
                <div class="floating-group">
                    <select name="plan_id" class="form-control" required placeholder=" ">
                        <option value="">Selecione um plano...</option>
                        ${planOptions}
                    </select>
                    <label class="floating-label">Plano</label>
                </div>
                <div class="floating-group">
                    <input type="date" name="expires_at" class="form-control" value="${data ? (data.expires_at ? data.expires_at.split(' ')[0] : '') : ''}" placeholder=" ">
                    <label class="floating-label">Expiração</label>
                </div>
            </div>
            
            <div class="floating-group mt-4">
                <select name="active" class="form-control" placeholder=" ">
                    <option value="1" ${!data || data.active == 1 ? 'selected' : ''}>Ativo</option>
                    <option value="0" ${data && data.active == 0 ? 'selected' : ''}>Suspenso</option>
                </select>
                <label class="floating-label">Status</label>
            </div>

            <div class="alert-premium mb-4">
                <i data-lucide="info" class="icon-sm"></i>
                <span>O primeiro vencimento e as faturas (Trial + Mensalidade) serão geradas automaticamente.</span>
            </div>

            <div class="pt-4 border-top flex justify-end gap-3">
                <button type="button" class="btn-dark btn-premium" onclick="UI.closeModal()">Cancelar</button>
                <button type="submit" class="btn-primary btn-premium">
                    <i data-lucide="save" class="icon-sm mr-2"></i> ${data ? 'Salvar Alterações' : 'Cadastrar Empresa'}
                </button>
            </div>
        </form>
    `;
    
    UI.showModal(data ? 'Editar Empresa' : 'Nova Empresa', html, 'lg');
    UI.initMasks();
}

window.suggestCompanySlug = function(name) {
    const input = document.getElementById('company_slug');
    if (!input) return;
    input.value = name.toLowerCase()
        .normalize("NFD")
        .replace(/[\u0300-\u036f]/g, "")
        .replace(/[^a-z0-9]/g, '-')
        .replace(/-+/g, '-')
        .replace(/^-|-$/g, '');
}

window.deleteCompany = async function(id) {
    if (await UI.confirm('Deseja realmente excluir esta empresa?')) {
        const result = await UI.request(`${window.SITE_URL}/api/admin/companies/delete`, { id });
        if (result && result.success) {
            const row = document.querySelector(`button[onclick*="deleteCompany(${id})"]`)?.closest('tr');
            if (row) {
                row.style.transition = 'all 0.4s ease';
                row.style.opacity = '0';
                row.style.transform = 'translateX(20px)';
                setTimeout(() => row.remove(), 400);
            }
        }
    }
}

// Details Page Functions
window.editCompanyExpires = function() {
    document.getElementById('company-expires-display').classList.add('hidden');
    document.getElementById('company-expires-edit').classList.replace('hidden', 'd-flex');
}

window.cancelEditCompanyExpires = function() {
    document.getElementById('company-expires-display').classList.remove('hidden');
    document.getElementById('company-expires-edit').classList.replace('d-flex', 'hidden');
}

window.saveCompanyExpires = async function() {
    const newDate = document.getElementById('company-expires-input').value;
    if (!newDate) return;

    const result = await UI.request(`${window.SITE_URL}/api/admin/companies/update-expiration`, {
        company_id: window.COMPANIES_DATA.companyId,
        expires_at: newDate
    });
    
    if (result && result.success) {
        window.location.reload();
    }
}

window.markAsPaid = async function(id) {
    if (await UI.confirm('Deseja marcar esta fatura como paga manualmente?', {
        title: 'Baixa de Fatura',
        confirmText: 'Confirmar Pagamento',
        type: 'success',
        icon: 'check-circle'
    })) {
        const result = await UI.request(`${window.SITE_URL}/api/admin/invoices/pay`, { id });
        if (result && result.success) {
            window.location.reload();
        }
    }
}

window.deleteInvoice = async function(id) {
    if (await UI.confirm('Remover fatura pendente?', {
        title: 'Remover Fatura',
        confirmText: 'Sim, Remover',
        type: 'danger'
    })) {
        const result = await UI.request(`${window.SITE_URL}/api/admin/invoices/delete`, { id });
        if (result && result.success) {
            window.location.reload();
        }
    }
}

window.editDueDate = function(id) {
    document.getElementById(`due-date-display-${id}`).classList.add('hidden');
    document.getElementById(`due-date-edit-${id}`).classList.replace('hidden', 'd-flex');
}

window.cancelUpdateDate = function(id) {
    document.getElementById(`due-date-display-${id}`).classList.remove('hidden');
    document.getElementById(`due-date-edit-${id}`).classList.replace('d-flex', 'hidden');
}

window.saveDueDate = async function(id) {
    const newDate = document.getElementById(`due-date-input-${id}`).value;
    if (!newDate) return;

    const result = await UI.request(`${window.SITE_URL}/api/admin/invoices/update-date`, {
        invoice_id: id,
        due_date: newDate
    });
    
    if (result && result.success) {
        window.location.reload();
    }
}

window.openInvoiceModal = function() {
    const html = `
        <form action="${window.SITE_URL}/api/admin/invoices/generate" method="POST" class="ajax-form">
            <input type="hidden" name="company_id" value="${window.COMPANIES_DATA.companyId}">
            
            <div class="floating-group">
                <input type="text" name="description" class="form-control" placeholder=" " required>
                <label class="floating-label">Descrição (Ex: Mensalidade)</label>
            </div>

            <div class="form-grid-2 mt-4">
                <div class="floating-group">
                    <input type="number" step="0.01" name="amount" class="form-control" placeholder=" " required>
                    <label class="floating-label">Valor (R$)</label>
                </div>
                <div class="floating-group">
                    <input type="date" name="due_date" class="form-control" value="${new Date(Date.now() + 3*86400000).toISOString().split('T')[0]}" required placeholder=" ">
                    <label class="floating-label">Vencimento</label>
                </div>
            </div>

            <div class="floating-group mt-4">
                <select name="type" class="form-control" placeholder=" ">
                    <option value="recurring">Mensalidade (Recorrente)</option>
                    <option value="single">Pagamento Único / Avulso</option>
                </select>
                <label class="floating-label">Tipo de Lançamento</label>
            </div>

            <div class="pt-4 border-top flex justify-end gap-3">
                <button type="button" class="btn-dark btn-premium" onclick="UI.closeModal()">Cancelar</button>
                <button type="submit" class="btn-primary btn-premium">Gerar Fatura Agora</button>
            </div>
        </form>
    `;
    UI.showModal('Gerar Fatura Manual', html);
}

document.addEventListener('ajaxSuccess', (e) => {
    if (e.detail && e.detail.success) {
        // window.location.reload(); 
    }
});
