<?php
/** @var App\Core\Router $router */

// Middleware Definitions
$auth = \App\Middleware\AuthMiddleware::class;
$admin = \App\Middleware\AdminMiddleware::class;

$router->add('GET', '/', ['controller' => 'DashboardController', 'method' => 'index', 'middlewares' => [$auth]]);
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

$router->add('GET', '/api/notifications/read/{id}', ['controller' => 'NotificationController', 'method' => 'read', 'middlewares' => [$auth]]);
$router->add('GET', '/api/notifications/read_all', ['controller' => 'NotificationController', 'method' => 'readAll', 'middlewares' => [$auth]]);

$router->add('GET', '/admin/integrations', ['controller' => 'Admin\\IntegrationsController', 'method' => 'index', 'middlewares' => [$auth, $admin]]);
$router->add('POST', '/admin/integrations', ['controller' => 'Admin\\IntegrationsController', 'method' => 'index', 'middlewares' => [$auth, $admin]]);
$router->add('GET', '/integrations', ['controller' => 'Admin\\IntegrationsController', 'method' => 'index', 'middlewares' => [$auth, $admin]]);
$router->add('POST', '/integrations', ['controller' => 'Admin\\IntegrationsController', 'method' => 'index', 'middlewares' => [$auth, $admin]]);
$router->add('POST', '/api/admin/test_email', ['controller' => 'Admin\\IntegrationsController', 'method' => 'testEmail', 'middlewares' => [$auth, $admin]]);
