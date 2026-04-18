/**
 * Plans Module Logic
 */

window.PLANS_DATA = window.PLANS_DATA || {};

window.openPlanModal = function(data = null) {
    const nonces = window.PLANS_DATA.nonces || {};
    
    const html = `
        <form action="${window.SITE_URL}/api/admin/plans/save" method="POST" class="ajax-form" data-no-reload="true">
            <input type="hidden" name="nonce" value="${nonces.save || ''}">
            <input type="hidden" name="id" value="${data ? data.id : ''}">
            
            <div class="floating-group">
                <input type="text" name="name" class="form-control" value="${data ? data.name : ''}" placeholder=" " required>
                <label class="floating-label">Nome do Pacote (Ex: Bronze, Prata, Gold)</label>
            </div>

            <div class="form-grid-2 mt-4">
                <div class="floating-group">
                    <input type="number" step="0.01" name="base_price" class="form-control" value="${data ? data.base_price : '40.00'}" required placeholder=" ">
                    <label class="floating-label">Preço Base (R$)</label>
                </div>
                <div class="floating-group">
                    <input type="number" name="included_users" class="form-control" value="${data ? data.included_users : '4'}" required placeholder=" ">
                    <label class="floating-label">Usuários Incluídos</label>
                </div>
            </div>

            <div class="form-grid-2 mt-4">
                <div class="floating-group">
                    <input type="number" step="0.01" name="extra_user_price" class="form-control" value="${data ? data.extra_user_price : '30.00'}" required placeholder=" ">
                    <label class="floating-label">Extra / Usuário (R$)</label>
                </div>
                <div class="floating-group">
                    <input type="number" name="trial_days" class="form-control" value="${data ? data.trial_days : '7'}" required placeholder=" ">
                    <label class="floating-label">Trial (Dias de Experiência)</label>
                </div>
            </div>

            <div class="pt-4 border-top flex justify-end gap-3">
                <button type="button" class="btn-dark btn-premium" onclick="UI.closeModal()">Cancelar</button>
                <button type="submit" class="btn-primary btn-premium">
                    <i data-lucide="save" class="icon-sm mr-2"></i> ${data ? 'Salvar Alterações' : 'Criar Pacote Agora'}
                </button>
            </div>
        </form>
    `;
    
    UI.showModal(data ? 'Editar Pacote' : 'Novo Pacote', html);
}

window.deletePlan = async function(id) {
    if (await UI.confirm('Deseja realmente excluir este pacote? Empresas associadas ficarão sem pacote definido.')) {
        const result = await UI.request(`${window.SITE_URL}/api/admin/plans/delete`, { 
            id, 
            nonce: window.PLANS_DATA.nonces.delete 
        });
        
        if (result && result.success) {
            const row = document.querySelector(`button[onclick*="deletePlan(${id})"]`)?.closest('tr');
            if (row) {
                row.style.transition = 'all 0.4s ease';
                row.style.opacity = '0';
                row.style.transform = 'translateX(20px)';
                setTimeout(() => row.remove(), 400);
            }
        }
    }
}

document.addEventListener('ajaxSuccess', (e) => {
    if (e.detail && e.detail.success && e.target.action.includes('/api/admin/plans/save')) {
        setTimeout(() => window.location.reload(), 800);
    }
});
