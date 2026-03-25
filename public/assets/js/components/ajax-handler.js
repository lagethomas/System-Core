/**
 * AJAX Handler Component
 * Handles CSRF auto-injection and UI.request helper
 */
(function () {
    const metaToken = document.querySelector('meta[name="csrf-token"]');
    if (!metaToken) return;
    const csrfToken = metaToken.getAttribute('content');
    if (!csrfToken) return;

    const origFetch = window.fetch;
    window.fetch = async function (input, init) {
        init = init || {};
        const method = (init.method || 'GET').toUpperCase();
        
        // Only inject for relative URLs or internal domain
        const url = typeof input === 'string' ? input : (input && input.url ? input.url : '');
        const isInternal = !url.startsWith('http') || url.startsWith(window.location.origin);
        
        if (isInternal) {
            init.headers = init.headers || {};
            if (init.headers instanceof Headers) {
                init.headers.set('X-CSRF-Token', csrfToken);
                init.headers.set('X-Requested-With', 'XMLHttpRequest');
            } else {
                init.headers['X-CSRF-Token'] = csrfToken;
                init.headers['X-Requested-With'] = 'XMLHttpRequest';
            }
            
            // For POST/PUT with FormData, append token field
            if (['POST', 'PUT'].includes(method)) {
                if (init.body instanceof FormData && !init.body.has('csrf_token')) {
                    init.body.append('csrf_token', csrfToken);
                }
            }
        }
        const response = await origFetch.call(this, input, init);
        
        // Intercept 401 triggers across the app mapping to session overlay
        if (response.status === 401 && isInternal) {
            try {
                const clone = response.clone();
                const data = await clone.json();
                if (data && (data.error === 'duplicate' || data.error === 'expired')) {
                    if (window.showDisconnectOverlay && !window._disconnectShown) {
                        window._disconnectShown = true;
                        const msg = data.error === 'duplicate' 
                            ? 'Sua conta foi acessada em outro local.' 
                            : 'Sua sessão expirou por inatividade.';
                        window.showDisconnectOverlay(msg);
                    }
                }
            } catch(e) {}
        }
        
        return response;
    };
})();

if (typeof UI !== 'undefined') {
    UI.request = async function (url, data = null) {
        try {
            const options = {
                method: data ? 'POST' : 'GET',
                body: data ? (data instanceof FormData ? data : JSON.stringify(data)) : null,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    ...(data && !(data instanceof FormData) ? { 'Content-Type': 'application/json' } : {})
                }
            };

            const response = await fetch(url, options);
            const contentType = response.headers.get("content-type");

            if (contentType && contentType.indexOf("application/json") !== -1) {
                const result = await response.json();
                if (!result.success) {
                    // Don't show toast for auth failures, they are handled by overlays
                    if (result.error !== 'expired' && result.error !== 'duplicate') {
                        this.showToast(result.message || 'Erro ao processar requisição', result.type || 'error');
                    }
                }
                return result;
            } else {
                const text = await response.text();
                console.error('Non-JSON Response:', text);
                if (text.includes('Fatal error') || text.includes('Parse error')) {
                    this.showToast('❌ Erro crítico no motor do sistema.', 'error');
                } else {
                    this.showToast('📡 Falha técnica na comunicação com o servidor.', 'error');
                }
                return null;
            }
        } catch (error) {
            console.error('Request Error:', error);
            this.showToast('Erro de conexão com o servidor', 'error');
            return null;
        }
    };
}

// Global Form Interceptor for .ajax-form
document.addEventListener('submit', async (e) => {
    if (e.target.classList.contains('ajax-form')) {
        e.preventDefault();
        const form = e.target;
        const action = form.getAttribute('action') || window.location.href;
        const formData = new FormData(form);

        const btn = e.submitter || form.querySelector('button[type="submit"]');
        const originalText = btn ? btn.innerHTML : '';

        if (btn && btn.name) {
            formData.append(btn.name, btn.value || '1');
        }

        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<i data-lucide="loader-2" class="animate-spin mr-2"></i> Processando...';
            if (typeof lucide !== 'undefined') lucide.createIcons();
        }

        const result = await UI.request(action, formData);

        if (btn) {
            btn.disabled = false;
            btn.innerHTML = originalText;
        }

        if (result && result.success) {
            UI.showToast(result.message || 'Operação realizada com sucesso', result.type || 'success');
            form.dispatchEvent(new CustomEvent('ajaxSuccess', { detail: result }));
            if (result.noClose || (result.data && result.data.noClose)) return;
            UI.closeModal();

            if (result.redirect) {
                setTimeout(() => window.location.href = result.redirect, 1000);
            } else if (!result.noReload) {
                setTimeout(() => window.location.reload(), 1200);
            }
        }
    }
});
