/**
 * Integrations Module Logic
 */



window.testSmtp = async function() {
    const email = await UI.prompt('Para qual e-mail deseja enviar o teste?', {
        title: '📧 Testar Conexão SMTP',
        placeholder: 'nome@exemplo.com'
    });
    
    if (!email) return;

    UI.showToast('Enviando e-mail de teste...', 'info');

    const formData = new FormData();
    formData.append('email', email);
    formData.append('csrf_token', window.CSRF_TOKEN);

    try {
        const data = await UI.request(`${window.SITE_URL}/api/admin/integrations/test-email`, formData);
        if (data && data.success) {
            UI.showToast(data.message, 'success');
        } else {
            UI.showToast(data.message || 'Falha no teste.', 'error');
        }
    } catch (e) {
        UI.showToast('Erro ao enviar teste.', 'error');
    }
}
