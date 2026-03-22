<?php
/** @var App\Core\Router $router */

// Middleware Definitions
$auth  = \App\Middleware\AuthMiddleware::class;
$admin = \App\Middleware\AdminMiddleware::class;

// ── Public Routes (no auth required) ────────────────────────────
$router->add('GET',  '/login',  ['controller' => 'LoginController', 'method' => 'index']);
$router->add('POST', '/login',  ['controller' => 'LoginController', 'method' => 'attempt']);
$router->add('GET',  '/logout', ['controller' => 'LoginController', 'method' => 'logout']);
$router->add('GET',  '/cardapio', ['controller' => 'CardapioController', 'method' => 'index']);

// ── Authenticated Routes ─────────────────────────────────────────
$router->add('GET', '/',          ['controller' => 'DashboardController', 'method' => 'index', 'middlewares' => [$auth]]);
$router->add('GET', '/dashboard', ['controller' => 'DashboardController', 'method' => 'index', 'middlewares' => [$auth]]);

$router->add('GET', '/profile', ['controller' => 'ProfileController', 'method' => 'index', 'middlewares' => [$auth]]);
$router->add('POST', '/api/profile/save', ['controller' => 'ProfileController', 'method' => 'save', 'middlewares' => [$auth]]);

// Admin Routes
$router->add('GET', '/admin/users', ['controller' => 'Admin\\UsersController', 'method' => 'index', 'middlewares' => [$auth, $admin]]);
$router->add('GET', '/users', ['controller' => 'Admin\\UsersController', 'method' => 'index', 'middlewares' => [$auth, $admin]]);
$router->add('POST', '/api/admin/users/save', ['controller' => 'Admin\\UsersController', 'method' => 'save', 'middlewares' => [$auth, $admin]]);
$router->add('POST', '/api/admin/users/delete', ['controller' => 'Admin\\UsersController', 'method' => 'delete', 'middlewares' => [$auth, $admin]]);

$router->add('GET', '/admin/logs', ['controller' => 'Admin\\LogsController', 'method' => 'index', 'middlewares' => [$auth, $admin]]);
$router->add('GET', '/logs', ['controller' => 'Admin\\LogsController', 'method' => 'index', 'middlewares' => [$auth, $admin]]);

$router->add('GET', '/admin/settings', ['controller' => 'Admin\\SettingsController', 'method' => 'index', 'middlewares' => [$auth, $admin]]);
$router->add('POST', '/admin/settings', ['controller' => 'Admin\\SettingsController', 'method' => 'index', 'middlewares' => [$auth, $admin]]);
$router->add('GET', '/settings', ['controller' => 'Admin\\SettingsController', 'method' => 'index', 'middlewares' => [$auth, $admin]]);
$router->add('POST', '/settings', ['controller' => 'Admin\\SettingsController', 'method' => 'index', 'middlewares' => [$auth, $admin]]);

$router->add('GET', '/admin/categorias', ['controller' => 'Admin\\CategoriasController', 'method' => 'index', 'middlewares' => [$auth, $admin]]);
$router->add('POST', '/api/admin/categorias/save', ['controller' => 'Admin\\CategoriasController', 'method' => 'save', 'middlewares' => [$auth, $admin]]);
$router->add('POST', '/api/admin/categorias/delete', ['controller' => 'Admin\\CategoriasController', 'method' => 'delete', 'middlewares' => [$auth, $admin]]);

$router->add('GET', '/api/notifications/read/{id}', ['controller' => 'NotificationController', 'method' => 'read', 'middlewares' => [$auth]]);
$router->add('GET', '/api/notifications/read_all', ['controller' => 'NotificationController', 'method' => 'readAll', 'middlewares' => [$auth]]);
$router->add('GET', '/api/notifications/clear_all', ['controller' => 'NotificationController', 'method' => 'clearAll', 'middlewares' => [$auth]]);

$router->add('GET', '/admin/integrations', ['controller' => 'Admin\\IntegrationsController', 'method' => 'index', 'middlewares' => [$auth, $admin]]);
$router->add('POST', '/admin/integrations', ['controller' => 'Admin\\IntegrationsController', 'method' => 'index', 'middlewares' => [$auth, $admin]]);
$router->add('GET', '/integrations', ['controller' => 'Admin\\IntegrationsController', 'method' => 'index', 'middlewares' => [$auth, $admin]]);
$router->add('POST', '/integrations', ['controller' => 'Admin\\IntegrationsController', 'method' => 'index', 'middlewares' => [$auth, $admin]]);
$router->add('POST', '/api/admin/test_email', ['controller' => 'Admin\\IntegrationsController', 'method' => 'testEmail', 'middlewares' => [$auth, $admin]]);

// ── Comanda / Mapa de Mesas ─────────────────────────────────────
$router->add('GET',  '/app/comanda',               ['controller' => 'ComandaController', 'method' => 'index',      'middlewares' => [$auth]]);
$router->add('GET',  '/api/comanda/mesas',        ['controller' => 'ComandaController', 'method' => 'getMesas',    'middlewares' => [$auth]]);
$router->add('POST', '/api/comanda/mesas/add',    ['controller' => 'ComandaController', 'method' => 'addMesa',     'middlewares' => [$auth]]);
$router->add('GET',  '/api/comanda/mesa/{id}',    ['controller' => 'ComandaController', 'method' => 'getComanda',  'middlewares' => [$auth]]);
$router->add('POST', '/api/comanda/mesa/abrir/{id}', ['controller' => 'ComandaController', 'method' => 'abrirMesa', 'middlewares' => [$auth]]);
$router->add('POST', '/api/comanda/mesa/item/{id}', ['controller' => 'ComandaController', 'method' => 'lançarItem', 'middlewares' => [$auth]]);
$router->add('DELETE','/api/comanda/item/{id}',   ['controller' => 'ComandaController', 'method' => 'removerItem','middlewares' => [$auth]]);
$router->add('POST', '/api/comanda/mesa/fechar/{id}', ['controller' => 'ComandaController', 'method' => 'fecharMesa', 'middlewares' => [$auth]]);
$router->add('DELETE', '/api/comanda/mesa/{id}', ['controller' => 'ComandaController', 'method' => 'removerMesa', 'middlewares' => [$auth]]);

// --- Produtos (Gestão) ---
$router->add('GET', '/app/produtos', ['controller' => 'ProdutoController', 'method' => 'index', 'middlewares' => [$auth]]);
$router->add('POST', '/api/produtos/save', ['controller' => 'ProdutoController', 'method' => 'save', 'middlewares' => [$auth]]);
$router->add('POST', '/api/produtos/delete', ['controller' => 'ProdutoController', 'method' => 'delete', 'middlewares' => [$auth]]);
$router->add('GET', '/api/produtos/list', ['controller' => 'ProdutoController', 'method' => 'listApi', 'middlewares' => [$auth]]);

// --- Financeiro ---
$router->add('GET', '/app/financeiro', ['controller' => 'FinanceiroController', 'method' => 'index', 'middlewares' => [$auth]]);
$router->add('POST', '/api/financeiro/add', ['controller' => 'FinanceiroController', 'method' => 'addMovimentacao', 'middlewares' => [$auth]]);
$router->add('POST', '/api/financeiro/delete', ['controller' => 'FinanceiroController', 'method' => 'delete', 'middlewares' => [$auth]]);

// --- PDV (Frente de Caixa) ---
$router->add('GET', '/app/pdv', ['controller' => 'PdvController', 'method' => 'index', 'middlewares' => [$auth]]);
$router->add('POST', '/api/pdv/checkout', ['controller' => 'PdvController', 'method' => 'checkout', 'middlewares' => [$auth]]);

// --- Caixa ---
$router->add('GET', '/app/caixa', ['controller' => 'CaixaController', 'method' => 'index', 'middlewares' => [$auth]]);
$router->add('POST', '/api/caixa/abrir', ['controller' => 'CaixaController', 'method' => 'abrir', 'middlewares' => [$auth]]);
$router->add('POST', '/api/caixa/fechar', ['controller' => 'CaixaController', 'method' => 'fechar', 'middlewares' => [$auth]]);
$router->add('GET', '/api/caixa/status', ['controller' => 'CaixaController', 'method' => 'status', 'middlewares' => [$auth]]);

// --- Relatórios ---
$router->add('GET', '/app/relatorios', ['controller' => 'RelatorioController', 'method' => 'index', 'middlewares' => [$auth]]);
$router->add('GET', '/api/relatorios/fluxo/{caixaId}', ['controller' => 'RelatorioController', 'method' => 'fluxo', 'middlewares' => [$auth]]);
