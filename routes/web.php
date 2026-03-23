<?php
/** @var App\Core\Router $router */

// Middleware Definitions
$auth  = \App\Middleware\AuthMiddleware::class;
$admin = \App\Middleware\AdminMiddleware::class;

// ── Public Routes (no auth required) ────────────────────────────
$router->add('GET',  '/login',  ['controller' => 'LoginController', 'method' => 'index']);
$router->add('POST', '/login',  ['controller' => 'LoginController', 'method' => 'attempt']);
$router->add('GET',  '/logout', ['controller' => 'LoginController', 'method' => 'logout']);
$router->add('POST', '/api/auth/pulse', ['controller' => 'LoginController', 'method' => 'pulse', 'middlewares' => [$auth]]);
$router->add('GET',  '/confirm-email', ['controller' => 'Auth\\EmailConfirmationController', 'method' => 'confirm']);

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
$router->add('POST', '/api/admin/users/send-access', ['controller' => 'Admin\\UsersController', 'method' => 'sendAccess', 'middlewares' => [$auth, $admin]]);

$router->add('GET', '/admin/logs', ['controller' => 'Admin\\LogsController', 'method' => 'index', 'middlewares' => [$auth, $admin]]);
$router->add('GET', '/logs', ['controller' => 'Admin\\LogsController', 'method' => 'index', 'middlewares' => [$auth, $admin]]);

$router->add('GET', '/admin/settings', ['controller' => 'Admin\\SettingsController', 'method' => 'index', 'middlewares' => [$auth, $admin]]);
$router->add('POST', '/admin/settings', ['controller' => 'Admin\\SettingsController', 'method' => 'index', 'middlewares' => [$auth, $admin]]);
$router->add('GET', '/settings', ['controller' => 'Admin\\SettingsController', 'method' => 'index', 'middlewares' => [$auth, $admin]]);
$router->add('POST', '/settings', ['controller' => 'Admin\\SettingsController', 'method' => 'index', 'middlewares' => [$auth, $admin]]);

$router->add('POST', '/api/notifications/read/{id}', ['controller' => 'NotificationController', 'method' => 'read', 'middlewares' => [$auth]]);
$router->add('POST', '/api/notifications/read_all', ['controller' => 'NotificationController', 'method' => 'readAll', 'middlewares' => [$auth]]);
$router->add('GET', '/api/notifications/unread', ['controller' => 'NotificationController', 'method' => 'unread', 'middlewares' => [$auth]]);
$router->add('GET', '/api/notifications/clear_all', ['controller' => 'NotificationController', 'method' => 'clearAll', 'middlewares' => [$auth]]);

$router->add('GET', '/admin/integrations', ['controller' => 'Admin\\IntegrationsController', 'method' => 'index', 'middlewares' => [$auth, $admin]]);
$router->add('POST', '/admin/integrations', ['controller' => 'Admin\\IntegrationsController', 'method' => 'index', 'middlewares' => [$auth, $admin]]);
$router->add('GET', '/integrations', ['controller' => 'Admin\\IntegrationsController', 'method' => 'index', 'middlewares' => [$auth, $admin]]);
$router->add('POST', '/integrations', ['controller' => 'Admin\\IntegrationsController', 'method' => 'index', 'middlewares' => [$auth, $admin]]);
$router->add('POST', '/api/admin/test_email', ['controller' => 'Admin\\IntegrationsController', 'method' => 'testEmail', 'middlewares' => [$auth, $admin]]);
