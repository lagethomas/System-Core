<?php
/** @var App\Core\Router $router */

// Middleware Definitions
$auth  = \App\Middleware\AuthMiddleware::class;
$admin = \App\Middleware\AdminMiddleware::class;

// ── Public Routes (no auth required) ────────────────────────────
$router->add('GET',  '/login',  ['controller' => 'App\\Modules\\Auth\\Controllers\\AuthController', 'method' => 'index']);
$router->add('POST', '/login',  ['controller' => 'App\\Modules\\Auth\\Controllers\\AuthController', 'method' => 'attempt']);
$router->add('GET',  '/logout', ['controller' => 'App\\Modules\\Auth\\Controllers\\AuthController', 'method' => 'logout']);
$router->add('POST', '/api/auth/pulse', ['controller' => 'App\\Modules\\Auth\\Controllers\\AuthController', 'method' => 'pulse', 'middlewares' => [$auth]]);
$router->add('GET',  '/confirm-email', ['controller' => 'App\\Modules\\Auth\\Controllers\\AuthController', 'method' => 'confirmEmail']);

// ── Authenticated Routes ─────────────────────────────────────────
$router->add('GET', '/',          ['controller' => 'App\\Modules\\Dashboard\\Controllers\\DashboardController', 'method' => 'index', 'middlewares' => [$auth]]);
$router->add('GET', '/dashboard', ['controller' => 'App\\Modules\\Dashboard\\Controllers\\DashboardController', 'method' => 'index', 'middlewares' => [$auth]]);

// User Profile Management
$router->add('GET', '/profile', ['controller' => 'App\\Modules\\Profile\\Controllers\\ProfileController', 'method' => 'index', 'middlewares' => [$auth]]);
$router->add('POST', '/api/profile/save', ['controller' => 'App\\Modules\\Profile\\Controllers\\ProfileController', 'method' => 'save', 'middlewares' => [$auth]]);

// Admin Routes
$router->add('GET', '/admin/users', ['controller' => 'App\\Modules\\Users\\Controllers\\UsersController', 'method' => 'index', 'middlewares' => [$auth, $admin]]);
$router->add('GET', '/users', ['controller' => 'App\\Modules\\Users\\Controllers\\UsersController', 'method' => 'index', 'middlewares' => [$auth, $admin]]);
$router->add('POST', '/api/admin/users/save', ['controller' => 'App\\Modules\\Users\\Controllers\\UsersController', 'method' => 'save', 'middlewares' => [$auth, $admin]]);
$router->add('POST', '/api/admin/users/delete', ['controller' => 'App\\Modules\\Users\\Controllers\\UsersController', 'method' => 'delete', 'middlewares' => [$auth, $admin]]);
$router->add('POST', '/api/admin/users/send-access', ['controller' => 'App\\Modules\\Users\\Controllers\\UsersController', 'method' => 'sendAccess', 'middlewares' => [$auth, $admin]]);

// SaaS Plans Management
$router->add('GET', '/admin/plans', ['controller' => 'App\\Modules\\Plans\\Controllers\\PlansController', 'method' => 'index', 'middlewares' => [$auth, $admin]]);
$router->add('POST', '/api/admin/plans/save', ['controller' => 'App\\Modules\\Plans\\Controllers\\PlansController', 'method' => 'save', 'middlewares' => [$auth, $admin]]);
$router->add('POST', '/api/admin/plans/delete', ['controller' => 'App\\Modules\\Plans\\Controllers\\PlansController', 'method' => 'delete', 'middlewares' => [$auth, $admin]]);

// SaaS Companies Management
$router->add('GET', '/admin/companies', ['controller' => 'App\\Modules\\Companies\\Controllers\\CompaniesController', 'method' => 'index', 'middlewares' => [$auth, $admin]]);
$router->add('POST', '/api/admin/companies/save', ['controller' => 'App\\Modules\\Companies\\Controllers\\CompaniesController', 'method' => 'save', 'middlewares' => [$auth, $admin]]);
$router->add('POST', '/api/admin/companies/delete', ['controller' => 'App\\Modules\\Companies\\Controllers\\CompaniesController', 'method' => 'delete', 'middlewares' => [$auth, $admin]]);

$router->add('GET', '/admin/companies/details', ['controller' => 'App\\Modules\\Companies\\Controllers\\DetailsController', 'method' => 'index', 'middlewares' => [$auth, $admin]]);
$router->add('POST', '/api/admin/invoices/generate', ['controller' => 'App\\Modules\\Companies\\Controllers\\DetailsController', 'method' => 'generateInvoice', 'middlewares' => [$auth, $admin]]);
$router->add('POST', '/api/admin/invoices/delete', ['controller' => 'App\\Modules\\Companies\\Controllers\\DetailsController', 'method' => 'deleteInvoice', 'middlewares' => [$auth, $admin]]);
$router->add('POST', '/api/admin/invoices/pay', ['controller' => 'App\\Modules\\Companies\\Controllers\\DetailsController', 'method' => 'payInvoice', 'middlewares' => [$auth, $admin]]);
$router->add('POST', '/api/admin/invoices/update-date', ['controller' => 'App\\Modules\\Companies\\Controllers\\DetailsController', 'method' => 'updateDate', 'middlewares' => [$auth, $admin]]);
$router->add('POST', '/api/admin/companies/update-expiration', ['controller' => 'App\\Modules\\Companies\\Controllers\\DetailsController', 'method' => 'updateExpiration', 'middlewares' => [$auth, $admin]]);

// SaaS Logs Management
$router->add('GET', '/admin/logs', ['controller' => 'App\\Modules\\Logs\\Controllers\\LogsController', 'method' => 'index', 'middlewares' => [$auth, $admin]]);
$router->add('GET', '/logs', ['controller' => 'App\\Modules\\Logs\\Controllers\\LogsController', 'method' => 'index', 'middlewares' => [$auth, $admin]]);
$router->add('POST', '/api/admin/logs/clear', ['controller' => 'App\\Modules\\Logs\\Controllers\\LogsController', 'method' => 'clear', 'middlewares' => [$auth, $admin]]);

// Global Settings
$router->add('GET', '/admin/settings', ['controller' => 'App\\Modules\\Settings\\Controllers\\SettingsController', 'method' => 'index', 'middlewares' => [$auth, $admin]]);
$router->add('POST', '/api/admin/settings/save', ['controller' => 'App\\Modules\\Settings\\Controllers\\SettingsController', 'method' => 'save', 'middlewares' => [$auth, $admin]]);
$router->add('GET', '/settings', ['controller' => 'App\\Modules\\Settings\\Controllers\\SettingsController', 'method' => 'index', 'middlewares' => [$auth, $admin]]);

// Company Settings
$router->add('GET', '/app/company-settings', ['controller' => 'App\\Modules\\Companies\\Controllers\\SettingsController', 'method' => 'index', 'middlewares' => [$auth]]);
$router->add('POST', '/api/company-settings/save', ['controller' => 'App\\Modules\\Companies\\Controllers\\SettingsController', 'method' => 'save', 'middlewares' => [$auth]]);

// Notifications
$router->add('POST', '/api/notifications/read/{id}', ['controller' => 'App\\Modules\\Notifications\\Controllers\\NotificationsController', 'method' => 'read', 'middlewares' => [$auth]]);
$router->add('POST', '/api/notifications/read-all',   ['controller' => 'App\\Modules\\Notifications\\Controllers\\NotificationsController', 'method' => 'readAll', 'middlewares' => [$auth]]);
$router->add('GET',  '/api/notifications/unread',    ['controller' => 'App\\Modules\\Notifications\\Controllers\\NotificationsController', 'method' => 'unread', 'middlewares' => [$auth]]);

// Global Search
$router->add('GET', '/api/search', ['controller' => 'App\\Modules\\Search\\Controllers\\SearchController', 'method' => 'globalSearch', 'middlewares' => [$auth]]);

$router->add('GET', '/admin/integrations', ['controller' => 'App\\Modules\\Integrations\\Controllers\\IntegrationsController', 'method' => 'index', 'middlewares' => [$auth, $admin]]);
$router->add('GET', '/integrations', ['controller' => 'App\\Modules\\Integrations\\Controllers\\IntegrationsController', 'method' => 'index', 'middlewares' => [$auth, $admin]]);
$router->add('POST', '/api/admin/integrations/save', ['controller' => 'App\\Modules\\Integrations\\Controllers\\IntegrationsController', 'method' => 'save', 'middlewares' => [$auth, $admin]]);
$router->add('POST', '/api/admin/integrations/test-email', ['controller' => 'App\\Modules\\Integrations\\Controllers\\IntegrationsController', 'method' => 'testEmail', 'middlewares' => [$auth, $admin]]);

// ── SaaS Slug Route (Keep this at the very end!) ────────────────
$router->add('GET',  '/{slug}',       ['controller' => 'App\\Modules\\Auth\\Controllers\\AuthController', 'method' => 'companyLogin']);
$router->add('GET',  '/{slug}/login', ['controller' => 'App\\Modules\\Auth\\Controllers\\AuthController', 'method' => 'companyLogin']);
$router->add('POST', '/{slug}/login', ['controller' => 'App\\Modules\\Auth\\Controllers\\AuthController', 'method' => 'attempt']);
