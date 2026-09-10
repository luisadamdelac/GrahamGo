<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

// ---------------------------------------------------------------------
// Public / Auth
// ---------------------------------------------------------------------
$routes->get('/', 'LandingController::index');
$routes->post('contact', 'LandingController::submitContact');

$routes->get('login', 'Auth::login');
$routes->post('login', 'Auth::attemptLogin');
$routes->get('register', 'Auth::register');
$routes->post('register', 'Auth::attemptRegister');
$routes->get('logout', 'Auth::logout');
$routes->get('owner/logout', 'Auth::ownerLogout');
$routes->get('logout-all', 'Auth::logoutAll');

$routes->get('forgot-password', 'Auth::forgotPassword');
$routes->post('forgot-password', 'Auth::attemptForgotPassword');
$routes->get('verify-otp', 'Auth::verifyOtp');
$routes->post('verify-otp', 'Auth::attemptVerifyOtp');
$routes->get('reset-password', 'Auth::resetPassword');
$routes->post('reset-password', 'Auth::attemptResetPassword');

// ---------------------------------------------------------------------
// Customer area
// ---------------------------------------------------------------------
$routes->group('', ['filter' => 'customerAuth'], static function ($routes) {
    $routes->get('home', 'Customer\ProductController::index');
    $routes->get('products/(:num)', 'Customer\ProductController::show/$1');

    $routes->get('reserve/(:num)', 'Customer\ReservationController::create/$1');
    $routes->post('reserve/(:num)', 'Customer\ReservationController::store/$1');

    $routes->get('my-reservations', 'Customer\ReservationController::index');
    $routes->get('my-reservations/(:num)', 'Customer\ReservationController::show/$1');
    $routes->post('my-reservations/(:num)/cancel', 'Customer\ReservationController::cancel/$1');

    $routes->get('profile', 'Customer\ProfileController::index');
    $routes->post('profile', 'Customer\ProfileController::update');
    $routes->post('profile/avatar', 'Customer\ProfileController::updateAvatar');
});

// ---------------------------------------------------------------------
// Owner area
// ---------------------------------------------------------------------
$routes->group('owner', ['filter' => 'ownerAuth'], static function ($routes) {
    $routes->get('dashboard', 'Owner\DashboardController::index');
    $routes->get('notifications/alerts-count', 'Owner\DashboardController::alertsCount');

    // Products
    $routes->get('products', 'Owner\ProductController::index');
    $routes->get('products/new', 'Owner\ProductController::create');
    $routes->post('products', 'Owner\ProductController::store');
    $routes->get('products/(:num)/edit', 'Owner\ProductController::edit/$1');
    $routes->post('products/(:num)', 'Owner\ProductController::update/$1');
    $routes->post('products/(:num)/toggle', 'Owner\ProductController::toggleStatus/$1');
    $routes->post('products/(:num)/delete', 'Owner\ProductController::delete/$1');

    // Customers
    $routes->get('customers', 'Owner\CustomerController::index');
    $routes->get('customers/quick-search', 'Owner\CustomerController::quickSearch');
    $routes->get('customers/(:num)', 'Owner\CustomerController::show/$1');

    // Reservations
    $routes->get('reservations', 'Owner\ReservationController::index');
    $routes->get('reservations/(:num)', 'Owner\ReservationController::show/$1');
    $routes->post('reservations/(:num)/confirm', 'Owner\ReservationController::confirm/$1');
    $routes->post('reservations/(:num)/ready', 'Owner\ReservationController::ready/$1');
    $routes->post('reservations/(:num)/claim', 'Owner\ReservationController::claim/$1');
    $routes->post('reservations/(:num)/cancel', 'Owner\ReservationController::cancel/$1');

    // Inventory
    $routes->get('inventory', 'Owner\InventoryController::index');
    $routes->get('inventory/batches', 'Owner\InventoryController::allBatches');
    $routes->get('inventory/(:num)/batches', 'Owner\InventoryController::batches/$1');
    $routes->post('inventory/(:num)/adjust', 'Owner\InventoryController::adjust/$1');

    // Sales
    $routes->get('sales', 'Owner\SaleController::index');
    $routes->get('walk-in-sale', 'Owner\WalkInSaleController::create');
    $routes->post('walk-in-sale', 'Owner\WalkInSaleController::store');

    // Reports
    $routes->get('reports', 'Owner\ReportController::index');
    $routes->get('reports/reservations', 'Owner\ReportController::reservations');
    $routes->get('reports/sales', 'Owner\ReportController::sales');
    $routes->get('reports/inventory', 'Owner\ReportController::inventory');

    // Profile
    $routes->get('profile', 'Owner\ProfileController::index');
    $routes->post('profile', 'Owner\ProfileController::update');
    $routes->post('profile/avatar', 'Owner\ProfileController::updateAvatar');

    // Settings
    $routes->get('settings', 'Owner\SettingController::index');
    $routes->post('settings', 'Owner\SettingController::update');
    $routes->post('settings/test-email', 'Owner\SettingController::sendTest');
    $routes->post('settings/reservation-rules', 'Owner\SettingController::updateReservationRules');
});
