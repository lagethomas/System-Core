/* -----------------------------------------------------------
   SIDEBAR
   ----------------------------------------------------------- */
window.toggleSidebarCollapse = function() {
    const sidebar = document.getElementById('sidebar');
    if (!sidebar) return;
    sidebar.classList.toggle('collapsed');
    const isCollapsed = sidebar.classList.contains('collapsed');
    document.body.classList.toggle('sidebar-collapsed', isCollapsed);
    localStorage.setItem('sidebar-collapsed', isCollapsed);

    // Update Icon (Lucide)
    const btn = document.getElementById('sidebar-toggle-btn');
    if (btn) {
        btn.innerHTML = `<i data-lucide="${isCollapsed ? 'chevrons-right' : 'chevrons-left'}"></i>`;
        if (typeof lucide !== 'undefined') lucide.createIcons();
    }
};

window.toggleSidebar = function() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.querySelector('.sidebar-overlay');
    if (sidebar) sidebar.classList.toggle('active');
    if (overlay) overlay.classList.toggle('active');
};

/* -----------------------------------------------------------
   SESSION MANAGER
   ----------------------------------------------------------- */
const SessionManager = (() => {
    // Will be set from PHP via window.SESSION_TIMEOUT_MINUTES
    const TIMEOUT_MIN    = (window.SESSION_TIMEOUT_MINUTES || 120);
    const TIMEOUT_MS     = TIMEOUT_MIN * 60 * 1000;
    const PULSE_INTERVAL = 30 * 1000;    // send pulse every 30s
    const WARN_AT        = 5 * 60 * 1000; // show warning color at 5 min left
    const DANGER_AT      = 1 * 60 * 1000; // show danger color at 1 min left

    let lastActivity  = Date.now();
    let sessionStart  = Date.now();
    let timerInterval = null;
    let pulseInterval = null;
    let expired       = false;

    // Activity events that reset inactivity countdown
    const ACTIVITY_EVENTS = ['mousemove', 'keydown', 'mousedown', 'touchstart', 'scroll', 'click'];

    function onActivity() {
        if (expired) return;
        lastActivity = Date.now();
    }

    async function sendPulse() {
        if (expired) return;

        // Only pulse if user was active in the last pulse window + buffer
        const idleMs = Date.now() - lastActivity;
        if (idleMs > TIMEOUT_MS) {
            markExpired();
            return;
        }

        try {
            const pulseUrl = (window.SITE_URL || '') + '/api/auth/pulse';
            const res = await fetch(pulseUrl, { method: 'POST', body: new FormData() });
            if (!res.ok) {
                const data = await res.json().catch(() => ({}));
                if (data.error === 'expired' || data.error === 'duplicate') {
                    if (window.showDisconnectOverlay && !window._disconnectShown) {
                        window._disconnectShown = true;
                        const msg = data.error === 'duplicate'
                            ? 'Sua conta foi acessada em outro local.'
                            : 'Sua sessão expirou por inatividade.';
                        window.showDisconnectOverlay(msg);
                    }
                }
            } else {
                // Reset inactivity window since server confirmed session is alive
                sessionStart = Date.now();
            }
        } catch (_) {}
    }

    function updateTimer() {
        if (expired) return;

        const idleMs    = Date.now() - lastActivity;
        const remaining = Math.max(0, TIMEOUT_MS - idleMs);

        if (remaining === 0) {
            markExpired();
            return;
        }

        const mins = Math.floor(remaining / 60000);
        const secs = Math.floor((remaining % 60000) / 1000);
        const display = `${String(mins).padStart(2,'0')}:${String(secs).padStart(2,'0')}`;

        const el    = document.getElementById('timer-count');
        const timer = document.getElementById('session-timer');
        if (el)    el.textContent = display;
        if (timer) {
            timer.classList.remove('warning','danger','expired');
            if      (remaining <= DANGER_AT) timer.classList.add('danger');
            else if (remaining <= WARN_AT)   timer.classList.add('warning');
        }
    }

    function markExpired() {
        expired = true;
        clearInterval(timerInterval);
        clearInterval(pulseInterval);

        const el    = document.getElementById('timer-count');
        const timer = document.getElementById('session-timer');

        if (el)    el.textContent = 'Sessão expirada';
        if (timer) { timer.classList.remove('warning','danger'); timer.classList.add('expired'); }

        // On next interaction → disconnect
        document.addEventListener('click',   expiredInteraction, { capture: true });
        document.addEventListener('keydown', expiredInteraction, { capture: true });
    }

    function expiredInteraction(e) {
        // Avoid double-trigger
        e.preventDefault();
        e.stopImmediatePropagation();
        if (window.showDisconnectOverlay && !window._disconnectShown) {
            window._disconnectShown = true;
            window.showDisconnectOverlay('Sua sessão expirou por inatividade.');
        }
    }

    function init() {
        // Listen for activity
        ACTIVITY_EVENTS.forEach(ev => document.addEventListener(ev, onActivity, { passive: true }));

        // Start ticker every second for the countdown
        timerInterval = setInterval(updateTimer, 1000);
        updateTimer(); // immediate render

        // Start pulse
        sendPulse(); // immediate
        pulseInterval = setInterval(sendPulse, PULSE_INTERVAL);

        // Also check on tab focus
        window.addEventListener('focus', () => { sendPulse(); });
    }

    return { init };
})();

/**
 * Main Application Orchestrator
 */
document.addEventListener('DOMContentLoaded', () => {
    // 1. Sidebar persistence
    const sidebar = document.getElementById('sidebar');
    const isCollapsed = localStorage.getItem('sidebar-collapsed') === 'true';
    if (isCollapsed && sidebar) {
        sidebar.classList.add('collapsed');
        document.body.classList.add('sidebar-collapsed');
        
        const btn = document.getElementById('sidebar-toggle-btn');
        if (btn) {
            btn.innerHTML = '<i data-lucide="chevrons-right"></i>';
            if (typeof lucide !== 'undefined') lucide.createIcons();
        }
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

    // 5. Session management
    if (!window.location.pathname.includes('/login')) {
        SessionManager.init();
    }

    // 6. Service Workers cleanup
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.getRegistrations().then(regs => {
            regs.forEach(r => r.unregister());
        });
    }
});

window.showDisconnectOverlay = function(message) {
    window.location.href = (window.SITE_URL || '') + '/logout';
};
