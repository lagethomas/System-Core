/**
 * UI Core Component - v2.5.0
 * Standardized according to .agenterules.md
 */

// 1. SweetAlert2 Standardized Mixins (Rule 5)
/**
 * Global Notify Function (Custom implementation to replace SweetAlert)
 */
window.notify = function(msg, type = 'success') {
    let container = document.querySelector('.toast-container');
    if (!container) {
        container = document.createElement('div');
        container.className = 'toast-container';
        document.body.appendChild(container);
    }

    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    
    // Icon mapping (Lucide)
    let iconName = 'info';
    if (type === 'success') iconName = 'check-circle-2';
    if (type === 'error') iconName = 'alert-circle';
    if (type === 'warning') iconName = 'alert-triangle';

    toast.innerHTML = `
        <i data-lucide="${iconName}"></i>
        <div class="toast-message">${msg}</div>
        <button class="toast-close" onclick="UI.removeToast(this)">
            <i data-lucide="x"></i>
        </button>
        <div class="toast-progress">
            <div class="toast-progress-bar"></div>
        </div>
    `;

    container.appendChild(toast);
    if (window.lucide) lucide.createIcons();

    // Animate progress bar
    const bar = toast.querySelector('.toast-progress-bar');
    if (bar) {
        bar.style.transition = 'transform 3s linear';
        setTimeout(() => {
            bar.style.transform = 'scaleX(0)';
        }, 10);
    }

    // Auto-remove after 3 seconds
    setTimeout(() => {
        toast.style.animation = 'toastSlideOut 0.4s forwards';
        setTimeout(() => toast.remove(), 400);
    }, 3000);
};

/**
 * Global Confirm Action (Rule 5)
 */
window.confirmAction = async function(title, text, type = 'warning', confirmText = 'Sim, continuar') {
    const result = await Swal.fire({
        title: title,
        text: text,
        icon: type,
        showCancelButton: true,
        confirmButtonColor: '#d4af37',
        cancelButtonColor: '#1a1b26',
        confirmButtonText: confirmText,
        cancelButtonText: 'Cancelar',
        background: '#1a1b26',
        color: '#fff',
        iconColor: '#d4af37'
    });
    return result.isConfirmed;
};

// 2. Global Modal Helpers (Rule 32)
window.openModal = function(id) {
    const modal = document.getElementById(id);
    if (modal) {
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
};

window.closeModal = function(id) {
    const modal = id ? document.getElementById(id) : document.querySelector('.modal-overlay.active');
    if (modal) {
        modal.classList.remove('active');
        document.body.style.overflow = '';
    }
};

window.UI = {
    modal: document.getElementById('global-modal'),
    modalTitle: document.getElementById('modal-title'),
    modalBody: document.getElementById('modal-body'),
    modalFooter: document.getElementById('modal-footer'),

    // Legacy support redirecting to notify/confirmAction
    showToast(msg, type = 'success') {
        window.notify(msg, type);
    },

    confirm(msg, options = {}) {
        return window.confirmAction(options.title || 'Confirmar', msg, options.type || 'warning', options.confirmText);
    },
    
    removeToast(btn) {
        const toast = btn.closest('.toast');
        if (toast) {
            toast.style.animation = 'toastSlideOut 0.4s forwards';
            setTimeout(() => toast.remove(), 400);
        }
    },

    showModal(title, html, size = '') {
        if (!this.modal) return;
        this.modalTitle.textContent = title;

        const content = this.modal.querySelector('.modal-content');
        if (content) {
            content.classList.remove('modal-lg', 'modal-sm', 'modal-xl');
            if (size) content.classList.add('modal-' + size.startsWith('modal-') ? size : 'modal-' + size);
        }

        const temp = document.createElement('div');
        temp.innerHTML = html;

        const footer = temp.querySelector('.modal-footer');
        if (footer && this.modalFooter) {
            this.modalFooter.innerHTML = footer.innerHTML;
            this.modalFooter.style.display = 'flex';
            footer.remove();

            const form = temp.querySelector('form');
            if (form) {
                let formId = form.getAttribute('id');
                if (!formId) {
                    formId = 'modal-dynamic-form-' + Date.now();
                    form.setAttribute('id', formId);
                }
                this.modalFooter.querySelectorAll('button[type="submit"]').forEach(btn => {
                    btn.setAttribute('form', formId);
                    btn.onclick = (e) => {
                        e.preventDefault();
                        const realForm = document.getElementById(formId);
                        if (realForm) {
                            if (realForm.reportValidity && !realForm.reportValidity()) return;
                            if (realForm.requestSubmit) {
                                realForm.requestSubmit(btn);
                            } else {
                                realForm.dispatchEvent(new Event('submit', { cancelable: true, bubbles: true }));
                            }
                        }
                    };
                });
            }
        } else if (this.modalFooter) {
            this.modalFooter.innerHTML = '';
            this.modalFooter.style.display = 'none';
        }

        this.modalBody.innerHTML = temp.innerHTML;
        this.modal.classList.add('active');
        document.body.style.overflow = 'hidden';

        if (typeof this.initAutocomplete === 'function') this.initAutocomplete();
        if (typeof this.initMasks === 'function') this.initMasks();
        if (typeof this.initPasswordToggles === 'function') this.initPasswordToggles();
        if (typeof lucide !== 'undefined') lucide.createIcons();
    },

    closeModal() {
        window.closeModal('global-modal');
        if (this.modalBody) this.modalBody.innerHTML = '';
        if (this.modalFooter) {
            this.modalFooter.innerHTML = '';
            this.modalFooter.style.display = 'none';
        }
    },

    // Global Search Implementation (Rule 37)
    handleGlobalSearch(query) {
        const q = query.toLowerCase().trim();
        const selectors = '.premium-table tbody tr, .card, .stat-card, .list-item, .user-list-card table tr, [data-searchable]';
        const elements = document.querySelectorAll(selectors);
        
        elements.forEach(el => {
            const text = el.textContent.toLowerCase();
            if (!q || text.includes(q)) {
                el.style.display = '';
                el.style.opacity = '1';
                el.style.transform = 'scale(1)';
            } else {
                el.style.display = 'none';
                el.style.opacity = '0';
                el.style.transform = 'scale(0.95)';
            }
        });
    },

    handleUrlMessages() {
        const urlParams = new URLSearchParams(window.location.search);
        const msg = urlParams.get('msg');
        if (msg) {
            const messages = {
                'success': 'Operação realizada com sucesso!',
                'saved': 'Alterações salvas com sucesso!',
                'deleted': 'Item removido com sucesso!',
                'error': 'Ocorreu um erro ao processar a solicitação.',
                'updated': 'Configurações atualizadas!'
            };
            const toastMsg = messages[msg] || decodeURIComponent(msg.replace(/_/g, ' '));
            const type = msg.includes('error') ? 'error' : 'success';
            setTimeout(() => window.notify(toastMsg, type), 500);
            const newUrl = window.location.pathname + window.location.search.replace(/([&?]msg=[^&]*)/, '').replace(/^&/, '?');
            window.history.replaceState({}, document.title, newUrl);
        }
    },

    initAutocomplete() {
        document.querySelectorAll('select.tom-select').forEach(el => {
            if (el.tomselect) return;
            if (typeof TomSelect !== 'undefined') {
                new TomSelect(el, {
                    plugins: ['remove_button'],
                    persist: false,
                    create: false,
                    allowEmptyOption: true,
                    maxOptions: 50,
                    sortField: { field: "text", direction: "asc" }
                });
            }
        });
    },
    
    togglePassword(btn, targetId) {
        const input = document.getElementById(targetId);
        if (!input || !btn) return;
        input.type = input.type === 'password' ? 'text' : 'password';
        btn.innerHTML = `<i data-lucide="${input.type === 'password' ? 'lock' : 'unlock'}"></i>`;
        if (typeof lucide !== 'undefined') lucide.createIcons();
    },

    initPasswordToggles() {
        document.querySelectorAll('input[type="password"]').forEach(input => {
            if (input.dataset.toggleInit) return;
            
            let wrapper = input.parentElement;
            if (!wrapper.classList.contains('password-toggle-wrapper')) {
                wrapper = document.createElement('div');
                wrapper.className = 'password-toggle-wrapper relative';
                input.parentNode.insertBefore(wrapper, input);
                wrapper.appendChild(input);
            }
            
            // Avoid duplicates if a button already exists (Rule 35)
            if (wrapper.querySelector('.btn-password-toggle') || wrapper.querySelector('.password-toggle-btn')) {
                input.dataset.toggleInit = 'true';
                return;
            }

            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'btn-password-toggle'; // Adjusted class to match auth.css
            btn.innerHTML = '<i data-lucide="lock"></i>';
            if (!input.id) input.id = 'pwd-' + Math.random().toString(36).substr(2, 9);
            btn.onclick = (e) => { e.preventDefault(); this.togglePassword(btn, input.id); };
            wrapper.appendChild(btn);
            
            input.dataset.toggleInit = 'true';

            const genBtn = wrapper.parentElement ? wrapper.parentElement.querySelector('.btn-generate-password') : null;
            if (genBtn) {
                wrapper.appendChild(genBtn);
                btn.style.right = '40px'; 
                genBtn.style.right = '10px';
            }
        });
    },

    copyToClipboard(text, msg = 'Copiado para a área de transferência!') {
        if (!text) return;
        
        navigator.clipboard.writeText(text).then(() => {
            window.notify(msg, 'success');
        }).catch(err => {
            console.error('Erro ao copiar:', err);
            // Fallback for non-secure contexts
            const textArea = document.createElement("textarea");
            textArea.value = text;
            document.body.appendChild(textArea);
            textArea.select();
            try {
                document.execCommand('copy');
                window.notify(msg, 'success');
            } catch (err) {
                window.notify('Erro ao copiar texto.', 'error');
            }
            document.body.removeChild(textArea);
        });
    },

    generatePassword(targetId) {
        const charset = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_+";
        let password = "";
        for (let i = 0; i < 12; i++) {
            password += charset.charAt(Math.floor(Math.random() * charset.length));
        }
        const input = document.getElementById(targetId);
        if (input) {
            input.value = password;
            input.type = 'text'; // Show it so they can see it
            window.notify('Senha gerada com sucesso!', 'info');
            // Trigger floating label if hidden
            input.dispatchEvent(new Event('input'));
        }
    }
};

// Global Initialization
document.addEventListener('DOMContentLoaded', () => {
    UI.handleUrlMessages();
    UI.initAutocomplete();
    UI.initPasswordToggles();
    if (typeof lucide !== 'undefined') lucide.createIcons();
});

window.handleGlobalSearch = UI.handleGlobalSearch;
