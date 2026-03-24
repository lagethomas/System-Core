/**
 * Main Application Orchestrator
 * Handles layout, sidebar and global initializers
 */
document.addEventListener('DOMContentLoaded', () => {
    // 1. Sidebar persistence
    const sidebar = document.getElementById('sidebar');
    const isCollapsed = localStorage.getItem('sidebar-collapsed') === 'true';
    if (isCollapsed && sidebar) {
        sidebar.classList.add('collapsed');
        document.body.classList.add('sidebar-collapsed');
    }

    // 2. Initializers
    if (typeof UI !== 'undefined') {
        UI.handleUrlMessages();
        UI.initMasks();
        UI.initAutocomplete();
        UI.initPasswordToggles();
    }

    // 3. Table Responsiveness Wrapper
    document.querySelectorAll('.premium-table').forEach(table => {
        if (!table.parentElement.classList.contains('table-responsive')) {
            const wrapper = document.createElement('div');
            wrapper.className = 'table-responsive';
            table.parentNode.insertBefore(wrapper, table);
            wrapper.appendChild(table);
        }
    });

    // 4. Modal Overlay Closer
    document.addEventListener('click', (e) => {
        if (e.target.classList.contains('modal-overlay')) {
            if (typeof UI !== 'undefined') UI.closeModal();
        }
    });

    // 5. Session Pulse (Rule 5)
    if (typeof UI !== 'undefined' && !window.location.pathname.includes('/login')) {
        startPulse();
    }

    // 6. Unregister Service Workers
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.getRegistrations().then(registrations => {
            for (let registration of registrations) registration.unregister();
        });
    }
});

/**
 * Sidebar Toggle Functions
 */
function toggleSidebarCollapse() {
    const sidebar = document.getElementById('sidebar');
    if (!sidebar) return;
    sidebar.classList.toggle('collapsed');
    document.body.classList.toggle('sidebar-collapsed');
    localStorage.setItem('sidebar-collapsed', sidebar.classList.contains('collapsed'));
}

function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.querySelector('.sidebar-overlay');
    if (sidebar) sidebar.classList.toggle('active');
    if (overlay) overlay.classList.toggle('active');
}
/**
 * Session Management (Rule 5)
 */
function startPulse() {
    // Immediate check on focus for better DX
    window.addEventListener('focus', checkSession);

    // Periodic check
    setInterval(checkSession, 5000); // 5 second pulse
}

async function checkSession() {
    // Fire and forget, global fetch handler catches 401 auto-disconnects
    fetch('/api/auth/pulse', { method: 'POST', body: new FormData() }).catch(() => {});
}

window.showDisconnectOverlay = function(message) {
    const overlay = document.createElement('div');
    overlay.className = 'disconnect-overlay';
    overlay.innerHTML = `
        <div class="disconnect-card">
            <div class="disconnect-icon"><i class="fas fa-plug"></i></div>
            <h3>Sessão Encerrada</h3>
            <p>${message}</p>
            <div class="disconnect-loader"></div>
            <span>Encerrando sua sessão com segurança...</span>
        </div>
    `;
    document.body.appendChild(overlay);
    setTimeout(() => overlay.classList.add('active'), 10);
    
    setTimeout(() => {
        window.location.href = '/logout';
    }, 3500);
}
