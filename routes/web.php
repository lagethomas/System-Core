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

// SaaS Plans Management
$router->add('GET', '/admin/plans', ['controller' => 'Admin\\PlansController', 'method' => 'index', 'middlewares' => [$auth, $admin]]);
$router->add('POST', '/api/admin/plans/save', ['controller' => 'Admin\\PlansController', 'method' => 'save', 'middlewares' => [$auth, $admin]]);
$router->add('POST', '/api/admin/plans/delete', ['controller' => 'Admin\\PlansController', 'method' => 'delete', 'middlewares' => [$auth, $admin]]);

// SaaS Companies Management
$router->add('GET', '/admin/companies', ['controller' => 'Admin\\CompaniesController', 'method' => 'index', 'middlewares' => [$auth, $admin]]);
$router->add('POST', '/api/admin/companies/save', ['controller' => 'Admin\\CompaniesController', 'method' => 'save', 'middlewares' => [$auth, $admin]]);
$router->add('POST', '/api/admin/companies/delete', ['controller' => 'Admin\\CompaniesController', 'method' => 'delete', 'middlewares' => [$auth, $admin]]);

$router->add('GET', '/admin/companies/details', ['controller' => 'Admin\\CompanyDetailsController', 'method' => 'index', 'middlewares' => [$auth, $admin]]);
$router->add('POST', '/api/admin/invoices/generate', ['controller' => 'Admin\\CompanyDetailsController', 'method' => 'generateInvoice', 'middlewares' => [$auth, $admin]]);
$router->add('POST', '/api/admin/invoices/delete', ['controller' => 'Admin\\CompanyDetailsController', 'method' => 'deleteInvoice', 'middlewares' => [$auth, $admin]]);
$router->add('POST', '/api/admin/invoices/pay', ['controller' => 'Admin\\CompanyDetailsController', 'method' => 'payInvoice', 'middlewares' => [$auth, $admin]]);
$router->add('POST', '/api/admin/invoices/update-date', ['controller' => 'Admin\\CompanyDetailsController', 'method' => 'updateDate', 'middlewares' => [$auth, $admin]]);
$router->add('POST', '/api/admin/companies/update-expiration', ['controller' => 'Admin\\CompanyDetailsController', 'method' => 'updateExpiration', 'middlewares' => [$auth, $admin]]);

$router->add('GET', '/admin/logs', ['controller' => 'Admin\\LogsController', 'method' => 'index', 'middlewares' => [$auth, $admin]]);
$router->add('GET', '/logs', ['controller' => 'Admin\\LogsController', 'method' => 'index', 'middlewares' => [$auth, $admin]]);

$router->add('GET', '/admin/settings', ['controller' => 'Admin\\SettingsController', 'method' => 'index', 'middlewares' => [$auth, $admin]]);
$router->add('POST', '/api/admin/settings/save', ['controller' => 'Admin\\SettingsController', 'method' => 'save', 'middlewares' => [$auth, $admin]]);
$router->add('GET', '/settings', ['controller' => 'Admin\\SettingsController', 'method' => 'index', 'middlewares' => [$auth, $admin]]);

// Company Settings
$router->add('GET', '/app/company-settings', ['controller' => 'CompanySettingsController', 'method' => 'index', 'middlewares' => [$auth]]);
$router->add('POST', '/api/company-settings/save', ['controller' => 'CompanySettingsController', 'method' => 'save', 'middlewares' => [$auth]]);

$router->add('GET', '/api/search', ['controller' => 'SearchController', 'method' => 'globalSearch', 'middlewares' => [$auth]]);
$router->add('POST', '/api/notifications/read/{id}', ['controller' => 'NotificationController', 'method' => 'read', 'middlewares' => [$auth]]);

$router->add('POST', '/api/notifications/read_all', ['controller' => 'NotificationController', 'method' => 'readAll', 'middlewares' => [$auth]]);
$router->add('GET', '/api/notifications/unread', ['controller' => 'NotificationController', 'method' => 'unread', 'middlewares' => [$auth]]);
$router->add('GET', '/api/notifications/clear_all', ['controller' => 'NotificationController', 'method' => 'clearAll', 'middlewares' => [$auth]]);

$router->add('GET', '/admin/integrations', ['controller' => 'Admin\\IntegrationsController', 'method' => 'index', 'middlewares' => [$auth, $admin]]);
$router->add('POST', '/admin/integrations', ['controller' => 'Admin\\IntegrationsController', 'method' => 'index', 'middlewares' => [$auth, $admin]]);
$router->add('GET', '/integrations', ['controller' => 'Admin\\IntegrationsController', 'method' => 'index', 'middlewares' => [$auth, $admin]]);
$router->add('POST', '/integrations', ['controller' => 'Admin\\IntegrationsController', 'method' => 'index', 'middlewares' => [$auth, $admin]]);
$router->add('POST', '/api/admin/test_email', ['controller' => 'Admin\\IntegrationsController', 'method' => 'testEmail', 'middlewares' => [$auth, $admin]]);

// ── SaaS Slug Route (Keep this at the very end!) ────────────────
$router->add('GET',  '/{slug}',       ['controller' => 'LoginController', 'method' => 'companyLogin']);
$router->add('GET',  '/{slug}/login', ['controller' => 'LoginController', 'method' => 'companyLogin']);
$router->add('POST', '/{slug}/login', ['controller' => 'LoginController', 'method' => 'attempt']);
