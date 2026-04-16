<?php
declare(strict_types=1);
global $pdo;
require_once __DIR__ . '/helpers/Auth.php';
require_once __DIR__ . '/helpers/CSRF.php';
require_once __DIR__ . '/repositories/NotificationRepository.php';

$user_id = (int)($_SESSION['user_id'] ?? 0);
$user_name = $_SESSION['user_name'] ?? 'Usuário';
$user_role = $_SESSION['user_role'] ?? 'usuario';

// Page title detector (MVC Aware)
global $current_page;
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
// Remove o base path do SITE_URL se houver (ex: /folder/dashboard -> dashboard)
$site_path = parse_url(SITE_URL, PHP_URL_PATH) ?: '';
$route = str_replace($site_path, '', $uri);
$route = trim($route, '/');

// Se for vazio ou index.php (legado), padrão é dashboard
if (empty($route) || $route === 'index.php') {
    $route = 'dashboard';
}

// Para CSS de módulos, pegamos a última parte da rota
$route_parts = explode('/', $route);
$current_page = end($route_parts);
$page_titles = [
    'dashboard' => 'Painel de Controle',
    'users' => 'Usuários',
    'logs' => 'Logs Globais',
    'settings' => 'Configurações',
    'profile' => 'Meu Perfil',
    'integrations' => 'Integrações',
    'companies' => 'Empresas Clientes',
    'details' => 'Detalhes da Empresa',
    'plans' => 'Pacotes de Assinatura',
    'company-settings' => 'Configurações da Empresa'
];

$unread_count = 0;
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo CSRF::generateToken(); ?>">
    <?php 
    // Use Pre-loaded Platform Settings from db.php
    global $platform_settings;
    $theme_slug = $platform_settings['system_theme'] ?? 'gold-black';
    $system_name = $platform_settings['system_name'] ?? 'SaaSFlow Core';
    $system_logo = $platform_settings['system_logo'] ?? '';

    // --- COMPANY BRANDING OVERRIDE: only for non-admin (company) users ---
    if (Auth::isLoggedIn() && !Auth::isAdmin()) {
        $company_id = Auth::companyId();
        if ($company_id) {
            try {
                $comp = \App\Core\Database::fetch("SELECT name, logo, theme FROM cp_companies WHERE id = ?", [$company_id]);
                if ($comp) {
                    $system_name = $comp['name'];
                    if (!empty($comp['logo'])) {
                        if (strpos($comp['logo'], '/') === 0 || strpos($comp['logo'], 'http') === 0) {
                            $system_logo = $comp['logo'];
                        } else {
                            $system_logo = '/uploads/logos/' . $comp['logo'];
                        }
                    }
                    if (!empty($comp['theme'])) {
                        $theme_slug = $comp['theme'];
                    }
                }
            } catch (\Exception $e) {}
        }
    }
    ?>
    <title><?php echo htmlspecialchars(($page_titles[$current_page] ?? 'Início') . ' | ' . ($system_name ?? 'SaaSFlow')); ?></title>
    
    <link rel="stylesheet" href="<?php echo \App\Core\Controller::asset('/assets/css/style.css'); ?>">
    <link rel="stylesheet" href="<?php echo \App\Core\Controller::asset('/assets/css/app-premium.css'); ?>">
    <link rel="stylesheet" href="<?php echo \App\Core\Controller::asset('/assets/css/theme/' . $theme_slug . '.css'); ?>">
    
    <?php 
    // Core CSS Components Registry (Modular loading)
    $core_components = [
        'page-content', 'sidebar', 'top-bar', 'buttons', 'tables', 'cards', 
        'forms', 'alerts', 'uploads', 'scroll', 'tom-select-custom', 
        'notifications', 'main-footer', 'popups', 'toasts', 
        'global-search', 'switches', 'utilities', 'pagination', 'tabs', 'accordion'
    ];

    foreach ($core_components as $component) {
        $asset_path = "/assets/css/components/{$component}.css";
        echo '    <link rel="stylesheet" href="' . \App\Core\Controller::asset($asset_path) . '">' . PHP_EOL;
    }
    ?>

    
    <?php 
    // Auto-load page specific CSS from modules
    $page_name = str_replace('.php', '', $current_page);
    $css_path = dirname(__FILE__) . "/../public/assets/css/modules/{$page_name}.css";
    if (file_exists($css_path)) {
        echo '<link rel="stylesheet" href="' . \App\Core\Controller::asset('/assets/css/modules/' . $page_name . '.css') . '">';
    }
    ?>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="<?php echo SITE_URL; ?>/assets/img/icon-192.png">
    <script>
        // Expose server-side session timeout so JS timer stays in sync
        window.SESSION_TIMEOUT_MINUTES = <?php echo (string)(int)($platform_settings['security_session_timeout'] ?? 120); ?>;
    </script>
</head>
<body>
    <div class="app-container">
        <div class="sidebar-overlay" onclick="toggleSidebar()"></div>
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <a href="<?php echo SITE_URL; ?>/dashboard" class="logo">
                    <div class="sidebar-logo-icon">
                        <?php 
                        $logo_to_show = '';
                        if (!empty($system_logo)) {
                            if (strpos($system_logo, '/') === 0) {
                                $logo_to_show = SITE_URL . $system_logo;
                            } elseif (strpos($system_logo, 'http') === 0) {
                                $logo_to_show = $system_logo;
                            } else {
                                $logo_to_show = SITE_URL . '/uploads/logos/' . $system_logo;
                            }
                        }
                        
                        if (!empty($logo_to_show)): ?>
                            <img src="<?php echo $logo_to_show; ?>" alt="Logo" style="max-width: 100%; max-height: 100%; object-fit: contain;">
                        <?php else: ?>
                            <i data-lucide="image"></i>
                        <?php endif; ?>
                    </div>
                    <span><?php echo htmlspecialchars($system_name); ?></span>
                </a>
            </div>
            <nav class="sidebar-nav">
                <ul>
                    <li class="<?php echo ($current_page == 'dashboard') ? 'active' : ''; ?>">
                        <a href="<?php echo SITE_URL; ?>/dashboard">
                            <i data-lucide="layout-dashboard"></i> <span>Dashboard</span>
                        </a>
                    </li>
                    
                    <?php if (Auth::isAdmin()): ?>
                    <li class="<?php echo ($current_page == 'companies' || $current_page == 'details') ? 'active' : ''; ?>">
                        <a href="<?php echo SITE_URL; ?>/admin/companies">
                            <i data-lucide="building-2"></i> <span>Empresas</span>
                        </a>
                    </li>
                    <li class="<?php echo ($current_page == 'plans') ? 'active' : ''; ?>">
                        <a href="<?php echo SITE_URL; ?>/admin/plans">
                            <i data-lucide="package"></i> <span>Planos</span>
                        </a>
                    </li>
                    <li class="<?php echo ($current_page == 'users') ? 'active' : ''; ?>">
                        <a href="<?php echo SITE_URL; ?>/users">
                            <i data-lucide="users"></i> <span>Usuários</span>
                        </a>
                    </li>
                    <li class="<?php echo ($current_page == 'integrations') ? 'active' : ''; ?>">
                        <a href="<?php echo SITE_URL; ?>/integrations">
                            <i data-lucide="plug"></i> <span>Integrações</span>
                        </a>
                    </li>
                    <li class="<?php echo ($current_page == 'logs') ? 'active' : ''; ?>">
                        <a href="<?php echo SITE_URL; ?>/logs">
                            <i data-lucide="terminal"></i> <span>Logs Globais</span>
                        </a>
                    </li>
                    <li class="<?php echo ($current_page == 'settings') ? 'active' : ''; ?>">
                        <a href="<?php echo SITE_URL; ?>/settings">
                            <i data-lucide="settings"></i> <span>Configurações</span>
                        </a>
                    </li>
                    <?php endif; ?>

                    <?php if (Auth::isOwner()): ?>
                    <li class="<?php echo ($current_page == 'company-settings') ? 'active' : ''; ?>">
                        <a href="<?php echo SITE_URL; ?>/app/company-settings">
                            <i data-lucide="building"></i> <span>Minha Empresa</span>
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
            </nav>
            <div class="sidebar-footer">
                <div class="user-profile" id="user-profile-trigger">
                    <div class="user-avatar">
                        <?php echo strtoupper(substr($user_name, 0, 1)); ?>
                    </div>
                    <div class="user-info">
                        <span class="user-name"><?php echo htmlspecialchars($user_name); ?> <i data-lucide="chevron-up" class="user-chevron"></i></span>
                        <span class="user-role"><?php echo ucfirst($user_role); ?></span>
                    </div>
                </div>
                <!-- Popup de Perfil/Sair -->
                <div class="sidebar-user-dropdown" id="user-dropdown">
                    <a href="<?php echo SITE_URL; ?>/profile" class="user-dropdown-link">
                        <i data-lucide="user-circle"></i> Meu Perfil
                    </a>
                    <a href="<?php echo SITE_URL; ?>/logout" onclick="handleLogout(event)" class="user-dropdown-item danger">
                        <i data-lucide="log-out"></i> Sair
                    </a>
                </div>
            </div>
        </aside>

        <main class="main-content">
            <header class="top-bar">
                <div class="top-bar-left">
                    <button class="menu-toggle" onclick="toggleSidebar()">
                        <i data-lucide="menu"></i>
                    </button>
                    <button class="sidebar-collapse-toggle" id="sidebar-toggle-btn" onclick="toggleSidebarCollapse()" title="Encolher Menu">
                        <i data-lucide="chevrons-left"></i>
                    </button>
                    <h2 class="page-title" id="page-title"><?php echo $page_titles[$current_page] ?? 'Início'; ?></h2>
                </div>

                <div class="top-nav-right">
                    <!-- Global Search -->
                    <div class="modern-search-container" id="global-search">
                        <div class="search-input-wrapper">
                            <i data-lucide="search" class="search-icon"></i>
                            <input type="text" id="global-search-input" placeholder="Buscar no sistema..." autocomplete="off">
                            <kbd class="search-shortcut">/</kbd>
                        </div>
                        <div class="global-search-results" id="global-search-results">
                            <!-- Results inject via JS -->
                        </div>
                    </div>


                    <!-- Session Timer (compact, in header) -->
                    <div class="session-timer" id="session-timer" title="Tempo restante de sessão">
                        <i data-lucide="clock"></i>
                        <span id="timer-count">--:--</span>
                    </div>
                    <!-- Notificações -->
                    <div class="notif-trigger">
                        <i data-lucide="bell"></i>
                    </div>

                    <div class="notification-dropdown" id="notif-dropdown">
                        <div class="notif-header">
                            <span>Notificações</span>
                        </div>
                        <div class="notif-list">
                            <div class="notif-empty">
                                <i data-lucide="bell-off"></i>
                                <span>Nenhuma nova notificação</span>
                            </div>
                        </div>
                    </div>

                </div>
            </header>
            <div class="page-content">

<script>
// ── GLOBAL SEARCH ──────────────────────────────────────────────
(function() {
    const searchInput = document.getElementById('global-search-input');
    const searchResults = document.getElementById('global-search-results');
    let searchDebounce = null;

    if (searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(searchDebounce);
            const query = this.value.trim();
            
            if (query.length < 2) {
                searchResults.classList.remove('active');
                return;
            }

            searchDebounce = setTimeout(async () => {
                try {
                    const res = await fetch(`<?php echo SITE_URL; ?>/api/search?q=${encodeURIComponent(query)}`);
                    const data = await res.json();
                    
                    if (data.results && data.results.length > 0) {
                        renderSearchResults(data.results);
                    } else {
                        searchResults.innerHTML = '<div style="padding: 15px; text-align: center; color: var(--text-muted); font-size: 0.85rem;">Nenhum resultado encontrado.</div>';
                        searchResults.classList.add('active');
                    }
                } catch (err) {
                    console.error("Erro na busca global:", err);
                }
            }, 300);
        });

        // Keyboard Shortcut (/)
        document.addEventListener('keydown', (e) => {
            if (e.key === '/' && !['INPUT', 'TEXTAREA'].includes(document.activeElement.tagName)) {
                e.preventDefault();
                searchInput.focus();
            }
            if (e.key === 'Escape') {
                searchResults.classList.remove('active');
                if (document.activeElement === searchInput) searchInput.blur();
            }
        });

        // Close on click outside
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.modern-search-container')) {
                searchResults.classList.remove('active');
            }
        });

        // Handle focus to show results if query exists
        searchInput.addEventListener('focus', function() {
            if (this.value.trim().length >= 2) {
                searchResults.classList.add('active');
            }
        });
    }

    function renderSearchResults(results) {
        let html = '';
        const groups = {};

        results.forEach(item => {
            if (!groups[item.type]) groups[item.type] = [];
            groups[item.type].push(item);
        });

        const typeIcons = {
            'user': 'user',
            'log': 'terminal',
            'setting': 'settings'
        };

        const typeLabels = {
            'user': 'Usuários',
            'log': 'Logs do Sistema',
            'setting': 'Configurações'
        };

        for (const type in groups) {
            html += `<div class="search-result-group">
                        <div class="search-group-title">${typeLabels[type] || type}</div>
                        ${groups[type].map(res => `
                            <a href="${res.url}" class="search-result-item">
                                <div class="search-result-icon">
                                    <i data-lucide="${res.icon || typeIcons[type] || 'info'}" class="icon-lucide"></i>
                                </div>
                                <div class="search-result-info">
                                    <span class="result-name">${res.name}</span>
                                    <span class="result-sub">${res.sub || ''}</span>
                                </div>
                            </a>
                        `).join('')}
                    </div>`;
        }

        searchResults.innerHTML = html;
        searchResults.classList.add('active');
        if (typeof lucide !== 'undefined') lucide.createIcons();
    }
})();
</script>

<script>

// Dropdown de perfil
document.getElementById('user-profile-trigger').addEventListener('click', function(e) {
    e.stopPropagation();
    const dropdown = document.getElementById('user-dropdown');
    dropdown.classList.toggle('active');
    this.classList.toggle('active');
});

// Dropdown de notificações
const notifTrigger = document.querySelector('.notif-trigger');
if (notifTrigger) {
    notifTrigger.addEventListener('click', function(e) {
        e.stopPropagation();
        document.getElementById('notif-dropdown').classList.toggle('active');
    });
}

// Fechar dropdowns ao clicar fora
document.addEventListener('click', function() {
    const dropdown = document.getElementById('user-dropdown');
    const trigger = document.getElementById('user-profile-trigger');
    if (dropdown) dropdown.classList.remove('active');
    if (trigger) trigger.classList.remove('active');

    const notif = document.getElementById('notif-dropdown');
    if (notif) notif.classList.remove('active');
});

function handleLogout(e) {
    // Redirect immediately as requested by user
    window.location.href = e.currentTarget.href;
}
</script>
