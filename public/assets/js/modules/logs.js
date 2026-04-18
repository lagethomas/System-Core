/**
 * Logs Module Logic
 */

window.clearLogs = async function() {
    const confirm = await UI.confirm('Deseja realmente apagar todo o histórico de logs do sistema? Esta ação é irreversível.');
    if (confirm) {
        try {
            const formData = new FormData();
            formData.append('csrf_token', window.CSRF_TOKEN);
            
            const res = await UI.request(`${window.SITE_URL}/api/admin/logs/clear`, formData);
            if (res && res.success) {
                UI.showToast(res.message || 'Histórico limpo com sucesso!', 'success');
                setTimeout(() => window.location.reload(), 1000);
            }
        } catch (error) {
            UI.showToast('Erro ao processar solicitação', 'error');
        }
    }
};
