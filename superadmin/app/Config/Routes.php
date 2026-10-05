<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

$routes->get('/', 'AuthController::showLogin');
$routes->get('login', 'AuthController::showLogin');
$routes->post('login', 'AuthController::login');
$routes->post('logout', 'AuthController::logout');

$routes->group('', ['filter' => 'auth'], static function (RouteCollection $routes) {
    $routes->get('dashboard', 'DashboardController::index');

    // Companies
    $routes->get('companies', 'CompaniesController::index', ['filter' => 'permission:company.view']);
    $routes->get('companies/create', 'CompaniesController::create', ['filter' => 'permission:company.create']);
    $routes->post('companies', 'CompaniesController::store', ['filter' => 'permission:company.create']);
    $routes->get('companies/(:num)', 'CompaniesController::view/$1', ['filter' => 'permission:company.view']);
    $routes->get('companies/(:num)/edit', 'CompaniesController::edit/$1', ['filter' => 'permission:company.edit']);
    $routes->post('companies/(:num)', 'CompaniesController::update/$1', ['filter' => 'permission:company.edit']);
    $routes->post('companies/(:num)/activate', 'CompaniesController::activate/$1', ['filter' => 'permission:company.activate']);
    $routes->post('companies/(:num)/suspend', 'CompaniesController::suspend/$1', ['filter' => 'permission:company.suspend']);
    $routes->post('companies/(:num)/archive', 'CompaniesController::archive/$1', ['filter' => 'permission:company.delete']);
    $routes->get('companies/(:num)/modules', 'CompaniesController::modules/$1', ['filter' => 'permission:module.manage']);
    $routes->post('companies/(:num)/modules', 'CompaniesController::updateModules/$1', ['filter' => 'permission:module.manage']);
    $routes->get('companies/(:num)/settings', 'CompaniesController::settings/$1', ['filter' => 'permission:company.settings']);
    $routes->post('companies/(:num)/settings', 'CompaniesController::updateSettings/$1', ['filter' => 'permission:company.settings']);
    $routes->get('companies/(:num)/provisioning', 'CompaniesController::provisioning/$1', ['filter' => 'permission:company.create']);
    $routes->post('companies/(:num)/database/test', 'CompaniesController::testDatabaseConnection/$1', ['filter' => 'permission:company.create']);
    $routes->post('companies/(:num)/database', 'CompaniesController::saveDatabaseConnection/$1', ['filter' => 'permission:company.create']);
    $routes->get('companies/(:num)/provisioning-status', 'CompaniesController::provisioningStatus/$1', ['filter' => 'permission:company.create']);
    $routes->get('companies/(:num)/database', 'CompaniesController::database/$1', ['filter' => 'permission:company.view']);
    $routes->post('companies/(:num)/license/renew', 'CompaniesController::renewLicense/$1', ['filter' => 'permission:company.edit']);
    $routes->post('companies/(:num)/license/revoke', 'CompaniesController::revokeLicense/$1', ['filter' => 'permission:company.suspend']);

    // Plans
    $routes->get('plans', 'PlansController::index', ['filter' => 'permission:plan.view']);
    $routes->get('plans/create', 'PlansController::create', ['filter' => 'permission:plan.create']);
    $routes->post('plans', 'PlansController::store', ['filter' => 'permission:plan.create']);
    $routes->get('plans/(:num)/edit', 'PlansController::edit/$1', ['filter' => 'permission:plan.edit']);
    $routes->post('plans/(:num)', 'PlansController::update/$1', ['filter' => 'permission:plan.edit']);
    $routes->post('plans/(:num)/toggle', 'PlansController::toggle/$1', ['filter' => 'permission:plan.edit']);

    // Modules
    $routes->get('modules', 'ModulesController::index', ['filter' => 'permission:module.view']);
    $routes->get('modules/create', 'ModulesController::create', ['filter' => 'permission:module.manage']);
    $routes->post('modules', 'ModulesController::store', ['filter' => 'permission:module.manage']);
    $routes->get('modules/(:num)/edit', 'ModulesController::edit/$1', ['filter' => 'permission:module.manage']);
    $routes->post('modules/(:num)', 'ModulesController::update/$1', ['filter' => 'permission:module.manage']);

    // Subscriptions
    $routes->get('subscriptions', 'SubscriptionsController::index', ['filter' => 'permission:subscription.view']);
    $routes->get('subscriptions/create', 'SubscriptionsController::create', ['filter' => 'permission:subscription.create']);
    $routes->post('subscriptions', 'SubscriptionsController::store', ['filter' => 'permission:subscription.create']);
    $routes->get('subscriptions/(:num)/edit', 'SubscriptionsController::edit/$1', ['filter' => 'permission:subscription.edit']);
    $routes->post('subscriptions/(:num)', 'SubscriptionsController::update/$1', ['filter' => 'permission:subscription.edit']);
    $routes->post('subscriptions/(:num)/extend', 'SubscriptionsController::extend/$1', ['filter' => 'permission:subscription.extend']);
    $routes->post('subscriptions/(:num)/suspend', 'SubscriptionsController::suspend/$1', ['filter' => 'permission:subscription.suspend']);
    $routes->post('subscriptions/(:num)/cancel', 'SubscriptionsController::cancel/$1', ['filter' => 'permission:subscription.suspend']);
    $routes->get('subscriptions/(:num)/history', 'SubscriptionsController::history/$1', ['filter' => 'permission:subscription.view']);

    // Platform users
    $routes->get('platform-users', 'PlatformUsersController::index', ['filter' => 'permission:user.view']);
    $routes->get('platform-users/create', 'PlatformUsersController::create', ['filter' => 'permission:user.manage']);
    $routes->post('platform-users', 'PlatformUsersController::store', ['filter' => 'permission:user.manage']);
    $routes->get('platform-users/(:num)/edit', 'PlatformUsersController::edit/$1', ['filter' => 'permission:user.manage']);
    $routes->post('platform-users/(:num)', 'PlatformUsersController::update/$1', ['filter' => 'permission:user.manage']);
    $routes->post('platform-users/(:num)/toggle', 'PlatformUsersController::toggle/$1', ['filter' => 'permission:user.manage']);

    // Roles & permissions
    $routes->get('roles', 'RolesController::index', ['filter' => 'permission:role.view']);
    $routes->get('roles/create', 'RolesController::create', ['filter' => 'permission:role.manage']);
    $routes->post('roles', 'RolesController::store', ['filter' => 'permission:role.manage']);
    $routes->get('roles/(:num)/edit', 'RolesController::edit/$1', ['filter' => 'permission:role.manage']);
    $routes->post('roles/(:num)', 'RolesController::update/$1', ['filter' => 'permission:role.manage']);
    $routes->get('permissions', 'PermissionsController::index', ['filter' => 'permission:role.view']);

    // Reports (placeholder — full reporting is a later phase)
    $routes->get('reports', 'ReportsController::index', ['filter' => 'permission:company.view']);

    // Audit & login logs
    $routes->get('audit-logs', 'AuditLogsController::index', ['filter' => 'permission:audit.view']);
    $routes->get('audit-logs/logins', 'AuditLogsController::logins', ['filter' => 'permission:audit.view']);

    // Settings
    $routes->get('settings', 'SettingsController::index', ['filter' => 'permission:settings.manage']);
    $routes->post('settings', 'SettingsController::update', ['filter' => 'permission:settings.manage']);
});
