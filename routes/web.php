<?php

declare(strict_types=1);

// Load the router class
require_once __DIR__ . '/../src/core/EnhancedRouter.php';

use core\EnhancedRouter;

/**
 * Web Routes for Time2Eat Application
 * Define all web routes with middleware and authentication
 */

// Use the router passed from the application, or create a new one if not available
if (!isset($router) || !($router instanceof EnhancedRouter)) {
    $router = new EnhancedRouter();
}

// ============================================================================
// PUBLIC ROUTES (No Authentication Required)
// ============================================================================

// Home and Public Pages
$router->get('/', 'HomeController@index')->name('home');
$router->get('/about', 'HomeController@about')->name('about');
$router->get('/contact', 'HomeController@contact')->name('contact');
$router->post('/contact', 'HomeController@submitContact')->name('contact.submit');

// Browse and Search
$router->get('/browse', 'BrowseController@index')->name('browse');
$router->get('/browse/search', 'BrowseController@searchRestaurants')->name('browse.search');
$router->get('/browse/category/{slug}', 'BrowseController@category')->name('browse.category');
$router->get('/browse/restaurant/{id}', 'BrowseController@restaurant')->name('browse.restaurant');

// Search Routes
$router->get('/search', 'SearchController@index')->name('search.index');
$router->get('/search/suggestions', 'SearchController@suggestions')->name('search.suggestions');
$router->get('/search/live', 'SearchController@liveSearch')->name('search.live');

// Public Reviews
$router->get('/reviews', 'ReviewController@index')->name('reviews.index');

// Food Items
$router->get('/food/{id}', 'FoodController@show')->name('food.show');
$router->get('/menu/{restaurant_id}', 'FoodController@menu')->name('food.menu');

// Authentication Routes
$router->get('/login', 'AuthController@showLogin')->name('login');
$router->post('/login', 'AuthController@processLogin')->name('login.submit');
$router->get('/register', 'AuthController@showRegister')->name('register');
$router->post('/register', 'AuthController@processRegister')->name('register.submit');
$router->get('/forgot-password', 'AuthController@showForgotPassword')->name('password.request');
$router->post('/forgot-password', 'AuthController@forgotPassword')->name('password.email');
$router->get('/reset-password/{token}', 'AuthController@showResetPassword')->name('password.reset');
$router->post('/reset-password', 'AuthController@resetPassword')->name('password.update');

// Security Routes
$router->get('/captcha/generate', 'CaptchaController@generate')->name('captcha.generate');
$router->post('/captcha/validate', 'CaptchaController@validate')->name('captcha.validate');
$router->get('/captcha/image', 'CaptchaController@image')->name('captcha.image');
$router->get('/captcha/refresh', 'CaptchaController@refresh')->name('captcha.refresh');


// ============================================================================
// CHECKOUT ROUTES (CheckoutController handles its own authentication)
// ============================================================================

$router->get('/checkout', 'CheckoutController@index')->name('checkout');
$router->post('/checkout/address', 'CheckoutController@setAddress')->name('checkout.address');
$router->post('/checkout/payment', 'CheckoutController@setPayment')->name('checkout.payment');
$router->post('/checkout/place-order', 'CheckoutController@placeOrder')->name('checkout.place-order');
$router->post('/checkout/validate-affiliate', 'CheckoutController@validateAffiliateCode')->name('checkout.validate-affiliate');

// AUTHENTICATED ROUTES (Require Login)
// ============================================================================

$router->group(['middleware' => ['AuthMiddleware']], function($router) {

    // General Dashboard Route (redirects based on user role)
    $router->get('/dashboard', 'DashboardController@index')->name('dashboard');

    // Logout (support both GET and POST for compatibility)
    $router->get('/logout', 'AuthController@logout')->name('logout');
    $router->post('/logout', 'AuthController@logout')->name('logout.post');
    
    // User Profile
    $router->get('/profile', 'ProfileController@show')->name('profile');
    $router->get('/profile/edit', 'ProfileController@edit')->name('profile.edit');
    $router->put('/profile', 'ProfileController@update')->name('profile.update');
    $router->delete('/profile', 'ProfileController@destroy')->name('profile.delete');
    
    // Password Management
    $router->get('/profile/password', 'ProfileController@showChangePassword')->name('profile.password');
    $router->put('/profile/password', 'ProfileController@changePassword')->name('profile.password.update');
    
    // Cart is now a modal - no controller routes needed
    // Use direct API endpoints in api/cart/ for cart operations
    
    // Checkout Process (moved outside AuthMiddleware group - CheckoutController handles its own auth)
    
    // Orders
    $router->get('/orders', 'OrderController@index')->name('orders');
    $router->get('/orders/{id}', 'OrderController@show')->name('orders.show');
    $router->post('/orders/{id}/cancel', 'OrderController@cancel')->name('orders.cancel');
    $router->post('/orders/{id}/rate', 'OrderController@rate')->name('orders.rate');

    // Reviews and Ratings
    $router->get('/reviews/create', 'ReviewController@create')->name('reviews.create');
    $router->post('/reviews/store', 'ReviewController@store')->name('reviews.store');
    $router->get('/reviews/edit/{id}', 'ReviewController@edit')->name('reviews.edit');
    $router->put('/reviews/update/{id}', 'ReviewController@update')->name('reviews.update');
    $router->delete('/reviews/delete/{id}', 'ReviewController@delete')->name('reviews.delete');
    $router->post('/reviews/{id}/helpful', 'ReviewController@markHelpful')->name('reviews.helpful');
    $router->post('/reviews/{id}/report', 'ReviewController@report')->name('reviews.report');

    // Order Cancellations and Refunds
    $router->get('/cancellations/create/{orderId}', 'CancellationController@create')->name('cancellations.create');
    $router->post('/cancellations/store', 'CancellationController@store')->name('cancellations.store');
    
    // Favorites
    $router->get('/favorites', 'FavoriteController@index')->name('favorites');
    $router->post('/favorites/add', 'FavoriteController@add')->name('favorites.add');
    $router->delete('/favorites/remove/{id}', 'FavoriteController@remove')->name('favorites.remove');
    
    // Addresses
    $router->get('/addresses', 'AddressController@index')->name('addresses');
    $router->get('/addresses/create', 'AddressController@create')->name('addresses.create');
    $router->post('/addresses', 'AddressController@store')->name('addresses.store');
    $router->get('/addresses/{id}/edit', 'AddressController@edit')->name('addresses.edit');
    $router->put('/addresses/{id}', 'AddressController@update')->name('addresses.update');
    $router->delete('/addresses/{id}', 'AddressController@destroy')->name('addresses.delete');
    
    // Notifications
    $router->get('/notifications', 'NotificationController@index')->name('notifications');
    $router->post('/notifications/{id}/read', 'NotificationController@markAsRead')->name('notifications.read');
    $router->post('/notifications/read-all', 'NotificationController@markAllAsRead')->name('notifications.read-all');
});

// ============================================================================
// CUSTOMER DASHBOARD ROUTES
// ============================================================================

$router->group(['prefix' => '/customer', 'middleware' => ['AuthMiddleware', 'RoleMiddleware:customer']], function($router) {

    // Customer Dashboard
    $router->get('/dashboard', 'CustomerDashboardController@index')->name('customer.dashboard');

    // Orders
    $router->get('/orders', 'CustomerDashboardController@orders')->name('customer.orders');
    $router->get('/orders/{id}/confirmation', 'CustomerDashboardController@orderConfirmation')->name('customer.orders.confirmation');
    $router->get('/orders/{id}/track', 'CustomerDashboardController@trackOrder')->name('customer.orders.track');

    // Messages
    $router->get('/messages', 'CustomerDashboardController@messages')->name('customer.messages');
    $router->get('/messages/{id}', 'CustomerDashboardController@getConversation')->name('customer.messages.conversation');
    $router->post('/messages/send', 'CustomerDashboardController@sendMessage')->name('customer.messages.send');
    $router->post('/messages/compose', 'CustomerDashboardController@composeMessage')->name('customer.messages.compose');
    $router->get('/messages/restaurants', 'CustomerDashboardController@getRestaurants')->name('customer.messages.restaurants');
    $router->get('/messages/orders', 'CustomerDashboardController@getOrders')->name('customer.messages.orders');

    // Notifications
    $router->get('/notifications', 'CustomerDashboardController@notifications')->name('customer.notifications');

    // Customer Disputes
    $router->get('/disputes', 'DisputeController@customerDisputes')->name('customer.disputes');
    $router->get('/disputes/create', 'DisputeController@create')->name('customer.disputes.create');
    $router->post('/disputes/store', 'DisputeController@store')->name('customer.disputes.store');

    // Customer Communication
    $router->get('/messages/riders', 'CustomerDashboardController@getRiders')->name('customer.messages.riders');
    $router->get('/messages/orders-with-riders', 'CustomerDashboardController@getOrdersWithRiders')->name('customer.messages.orders-with-riders');
    $router->post('/messages/compose-to-rider', 'CustomerDashboardController@composeMessageToRider')->name('customer.messages.compose-to-rider');
    $router->post('/messages/compose-to-restaurant', 'CustomerDashboardController@composeMessageToRestaurant')->name('customer.messages.compose-to-restaurant');
    $router->post('/messages/compose-to-support', 'CustomerDashboardController@composeMessageToSupport')->name('customer.messages.compose-to-support');

    // Favorites
    $router->get('/favorites', 'CustomerDashboardController@favorites')->name('customer.favorites');
    $router->delete('/favorites/restaurants/{id}', 'CustomerDashboardController@removeFavoriteRestaurant')->name('customer.favorites.restaurant.remove');
    $router->delete('/favorites/items/{id}', 'CustomerDashboardController@removeFavoriteMenuItem')->name('customer.favorites.item.remove');

    // Addresses
    $router->get('/addresses', 'CustomerDashboardController@addresses')->name('customer.addresses');
    $router->post('/addresses/create', 'CustomerDashboardController@createAddress')->name('customer.addresses.create');
    $router->put('/addresses/{id}', 'CustomerDashboardController@updateAddress')->name('customer.addresses.update');
    $router->delete('/addresses/{id}', 'CustomerDashboardController@deleteAddress')->name('customer.addresses.delete');

    // Payment Methods
    $router->get('/payments', 'CustomerDashboardController@payments')->name('customer.payments');
    $router->post('/payments', 'CustomerDashboardController@payments')->name('customer.payments.action');

    // Affiliate Program
    $router->get('/affiliates', 'CustomerDashboardController@affiliates')->name('customer.affiliates');
    $router->post('/affiliates/join', 'CustomerDashboardController@joinAffiliateProgram')->name('customer.affiliates.join');
    $router->post('/affiliates/withdraw', 'CustomerDashboardController@requestWithdrawal')->name('customer.affiliates.withdraw');

    // Profile
    $router->get('/profile', 'CustomerDashboardController@profile')->name('customer.profile');
    $router->post('/profile/update', 'CustomerDashboardController@updateProfile')->name('customer.profile.update');
    $router->post('/profile/change-password', 'CustomerDashboardController@changePassword')->name('customer.profile.change-password');

    // Role Change Requests
    $router->get('/role-request', 'Time2Eat\Controllers\Customer\RoleRequestController@showRequestForm')->name('customer.role-request');
    $router->post('/role-request/submit', 'Time2Eat\Controllers\Customer\RoleRequestController@submitRequest')->name('customer.role-request.submit');
    $router->get('/role-request/status', 'Time2Eat\Controllers\Customer\RoleRequestController@getRequestStatus')->name('customer.role-request.status');
});

// ============================================================================
// ADDRESS MANAGEMENT API ROUTES
// ============================================================================

$router->group(['prefix' => '/api/addresses', 'middleware' => ['AuthMiddleware']], function($router) {
    $router->get('/', 'AddressController@index')->name('api.addresses.index');
    $router->get('/{id}', 'AddressController@show')->name('api.addresses.show');
    $router->post('/', 'AddressController@store')->name('api.addresses.store');
    $router->put('/{id}', 'AddressController@update')->name('api.addresses.update');
    $router->patch('/{id}', 'AddressController@update')->name('api.addresses.patch');
    $router->delete('/{id}', 'AddressController@destroy')->name('api.addresses.destroy');
    $router->post('/{id}/set-default', 'AddressController@setDefault')->name('api.addresses.set-default');
    $router->get('/default', 'AddressController@getDefault')->name('api.addresses.default');
    $router->post('/search-location', 'AddressController@searchByLocation')->name('api.addresses.search-location');
});

// ============================================================================
// VENDOR ROUTES (Restaurant Owners)
// ============================================================================

$router->group(['prefix' => '/vendor', 'middleware' => ['AuthMiddleware', 'RoleMiddleware:vendor']], function($router) {
    
    // Vendor Dashboard
    $router->get('/dashboard', 'VendorDashboardController@index')->name('vendor.dashboard');
    
    // Restaurant Setup (for new vendors)
    $router->get('/setup', 'VendorDashboardController@setup')->name('vendor.setup');
    $router->post('/setup', 'VendorDashboardController@processSetup')->name('vendor.setup.submit');
    
    // Restaurant Management
    $router->get('/restaurant', 'VendorDashboardController@profile')->name('vendor.restaurant');
    
    // Menu Management
    $router->get('/menu', 'VendorMenuController@index')->name('vendor.menu.index');
    $router->get('/menu/create', 'VendorMenuController@create')->name('vendor.menu.create');
    $router->post('/menu', 'VendorMenuController@store')->name('vendor.menu.store');
    $router->get('/menu/{id}/edit', 'VendorMenuController@edit')->name('vendor.menu.edit');
    $router->put('/menu/{id}', 'VendorMenuController@updateItem')->name('vendor.menu.update');
    $router->delete('/menu/{id}', 'VendorMenuController@destroy')->name('vendor.menu.destroy');
    $router->post('/menu/{id}/toggle-status', 'VendorMenuController@toggleAvailability')->name('vendor.menu.toggle');
    $router->post('/menu/toggle/{id}', 'VendorMenuController@toggleAvailability')->name('vendor.menu.toggle.alt');
    $router->post('/menu/stock/{id}', 'VendorMenuController@updateStock')->name('vendor.menu.stock');
    $router->get('/menu/import', 'VendorMenuController@showImport')->name('vendor.menu.import');
    $router->post('/menu/import', 'VendorMenuController@importCsv')->name('vendor.menu.import.store');
    $router->get('/menu/template', 'VendorMenuController@downloadTemplate')->name('vendor.menu.template');
    
    // Orders Management
    $router->get('/orders', 'VendorDashboardController@orders')->name('vendor.orders');
    $router->get('/orders/{id}', 'VendorDashboardController@getOrderDetails')->name('vendor.orders.details');
    $router->get('/orders/{id}/items', 'VendorDashboardController@getOrderItems')->name('vendor.orders.items');
    $router->post('/orders/status', 'VendorDashboardController@updateOrderStatus')->name('vendor.orders.status');

    // Restaurant Management
    $router->get('/profile', 'VendorDashboardController@profile')->name('vendor.profile');
    $router->post('/profile', 'VendorDashboardController@profile')->name('vendor.profile.update');
    $router->post('/toggle-availability', 'VendorDashboardController@toggleAvailability')->name('vendor.toggle-availability');

    // Analytics and Earnings
    $router->get('/analytics', 'VendorDashboardController@analytics')->name('vendor.analytics');
    $router->get('/earnings', 'VendorDashboardController@earnings')->name('vendor.earnings');
    $router->post('/earnings/payout', 'VendorDashboardController@requestPayout')->name('vendor.earnings.payout');
    
    // Reviews
    $router->get('/reviews', 'VendorDashboardController@reviews')->name('vendor.reviews');
    
    // Categories Management
    $router->get('/categories', 'VendorDashboardController@categories')->name('vendor.categories');
    $router->post('/categories', 'VendorDashboardController@storeCategory')->name('vendor.categories.store');
    $router->get('/categories/{id}', 'VendorDashboardController@getCategory')->name('vendor.categories.show');
    $router->put('/categories/{id}', 'VendorDashboardController@updateCategory')->name('vendor.categories.update');
    $router->delete('/categories/{id}', 'VendorDashboardController@deleteCategory')->name('vendor.categories.destroy');
    
    // Messages - Specific routes MUST come before parameterized routes
    $router->get('/messages', 'VendorDashboardController@messages')->name('vendor.messages');
    $router->get('/messages/customers', 'VendorDashboardController@getCustomers')->name('vendor.messages.customers');
    $router->get('/messages/orders', 'VendorDashboardController@getOrders')->name('vendor.messages.orders');
    $router->get('/messages/riders', 'VendorDashboardController@getRiders')->name('vendor.messages.riders');
    $router->get('/messages/orders-with-riders', 'VendorDashboardController@getOrdersWithRiders')->name('vendor.messages.orders-with-riders');
    $router->post('/messages/send', 'VendorDashboardController@sendMessage')->name('vendor.messages.send');
    $router->post('/messages/compose', 'VendorDashboardController@composeMessage')->name('vendor.messages.compose');
    $router->post('/messages/compose-to-rider', 'VendorDashboardController@composeMessageToRider')->name('vendor.messages.compose-to-rider');
    $router->get('/messages/{id}', 'VendorDashboardController@getConversation')->name('vendor.messages.conversation');
    $router->post('/messages/{id}/resolve', 'VendorDashboardController@resolveConversation')->name('vendor.messages.resolve');
    $router->post('/messages/{id}/block', 'VendorDashboardController@blockUser')->name('vendor.messages.block');

    // Notifications
    $router->get('/notifications', 'VendorDashboardController@notifications')->name('vendor.notifications');

    // Vendor Disputes
    $router->get('/disputes', 'DisputeController@vendorDisputes')->name('vendor.disputes');
    
    // Settings
    $router->get('/settings', 'VendorDashboardController@settings')->name('vendor.settings');
    $router->post('/settings/account', 'VendorDashboardController@updateAccount')->name('vendor.settings.account');
    $router->post('/settings/notifications', 'VendorDashboardController@updateNotifications')->name('vendor.settings.notifications');
    $router->post('/settings/password', 'VendorDashboardController@updatePassword')->name('vendor.settings.password');
    $router->post('/settings/payment', 'VendorDashboardController@updatePayment')->name('vendor.settings.payment');
    $router->post('/settings/preferences', 'VendorDashboardController@updatePreferences')->name('vendor.settings.preferences');
    $router->post('/settings/2fa', 'VendorDashboardController@toggle2FA')->name('vendor.settings.2fa');
});

// ============================================================================
// RIDER ROUTES (Delivery Personnel)
// ============================================================================

$router->group(['prefix' => '/rider', 'middleware' => ['AuthMiddleware', 'RoleMiddleware:rider']], function($router) {
    
    // Rider Dashboard
    $router->get('/dashboard', 'RiderDashboardController@index')->name('rider.dashboard');
    
    // Delivery Management
    $router->get('/deliveries', 'RiderDashboardController@deliveries')->name('rider.deliveries');
    $router->get('/available', 'RiderDashboardController@available')->name('rider.available');
    $router->post('/accept-order', 'RiderDashboardController@acceptOrder')->name('rider.accept-order');
    $router->post('/delivery-status', 'RiderDashboardController@updateDeliveryStatus')->name('rider.delivery-status');
    $router->get('/deliveries/available', 'Time2Eat\Controllers\RiderDeliveryController@getAvailableDeliveries')->name('rider.deliveries.available');
    $router->post('/deliveries/{id}/accept', 'Time2Eat\Controllers\RiderDeliveryController@acceptDelivery')->name('rider.deliveries.accept');
    $router->post('/deliveries/{id}/pickup', 'Time2Eat\Controllers\RiderDeliveryController@updateDeliveryStatus')->name('rider.deliveries.pickup');
    $router->post('/deliveries/{id}/deliver', 'Time2Eat\Controllers\RiderDeliveryController@updateDeliveryStatus')->name('rider.deliveries.deliver');

    // Rider Management
    $router->get('/schedule', 'RiderDashboardController@schedule')->name('rider.schedule');
    $router->post('/schedule', 'RiderDashboardController@schedule')->name('rider.schedule.update');
    $router->get('/performance', 'RiderDashboardController@performance')->name('rider.performance');
    
    // Messages
    $router->get('/messages', 'RiderDashboardController@messages')->name('rider.messages');
    $router->get('/messages/{id}', 'RiderDashboardController@getConversation')->name('rider.messages.conversation');
    $router->post('/messages/send', 'RiderDashboardController@sendMessage')->name('rider.messages.send');
    $router->get('/messages/deliveries', 'RiderDashboardController@getRiderDeliveries')->name('rider.messages.deliveries');
    $router->post('/messages/compose', 'RiderDashboardController@composeMessage')->name('rider.messages.compose');

    // Notifications
    $router->get('/notifications', 'RiderDashboardController@notifications')->name('rider.notifications');
    
    $router->get('/profile', 'RiderDashboardController@profile')->name('rider.profile');
    $router->post('/profile', 'RiderDashboardController@profile')->name('rider.profile.update');
    $router->get('/report-issue', 'RiderDashboardController@reportIssue')->name('rider.report-issue');
    $router->post('/report-issue', 'RiderDashboardController@reportIssue')->name('rider.report-issue.submit');
    $router->post('/toggle-availability', 'RiderDashboardController@toggleAvailability')->name('rider.toggle-availability');

    // Location Updates
    $router->post('/location/update', 'Time2Eat\Controllers\RiderDeliveryController@updateLocation')->name('rider.location.update');
    $router->post('/status/toggle', 'controllers\RiderDashboardController@toggleAvailability')->name('rider.status.toggle');

    // Earnings
    $router->get('/earnings', 'RiderDashboardController@earnings')->name('rider.earnings');
    // Note: EarningsController export method needs to be implemented in RiderDashboardController
    // $router->get('/earnings/export', 'controllers\RiderDashboardController@exportEarnings')->name('rider.earnings.export');
});

// ============================================================================
// ADMIN ROUTES (System Administration)
// ============================================================================

$router->group(['prefix' => '/admin', 'middleware' => ['AuthMiddleware', 'RoleMiddleware:admin']], function($router) {
    
    // Admin Dashboard
    $router->get('/dashboard', 'AdminDashboardController@index')->name('admin.dashboard');

    // Dashboard API endpoints
    $router->get('/api/deliveries/live', 'AdminDashboardController@liveDeliveries')->name('admin.api.deliveries.live');
    $router->get('/api/quick-actions', 'AdminDashboardController@quickActionCounts')->name('admin.api.quick-actions');
    $router->get('/api/dashboard/counts', 'AdminDashboardController@quickActionCounts')->name('admin.api.dashboard.counts');
    $router->post('/api/notifications/broadcast', 'Time2Eat\Controllers\Admin\DashboardApiController@broadcastNotification')->name('admin.api.notifications.broadcast');
    $router->post('/api/maintenance/toggle', 'Time2Eat\Controllers\Admin\DashboardApiController@toggleMaintenance')->name('admin.api.maintenance.toggle');
    $router->get('/api/dashboard/export', 'Time2Eat\Controllers\Admin\DashboardApiController@exportData')->name('admin.api.dashboard.export');
    
    // Admin Messaging System
    $router->get('/messages', 'AdminDashboardController@messages')->name('admin.messages');
    $router->get('/messages/{id}', 'AdminDashboardController@getConversation')->name('admin.messages.conversation');
    $router->post('/messages/send', 'AdminDashboardController@sendMessage')->name('admin.messages.send');
    $router->post('/messages/compose', 'AdminDashboardController@composeMessage')->name('admin.messages.compose');
    $router->get('/messages/customers', 'AdminDashboardController@getCustomers')->name('admin.messages.customers');
    $router->get('/messages/vendors', 'AdminDashboardController@getVendors')->name('admin.messages.vendors');
    $router->get('/messages/riders', 'AdminDashboardController@getRiders')->name('admin.messages.riders');
    $router->get('/messages/orders', 'AdminDashboardController@getRecentOrders')->name('admin.messages.orders');
    
    // Advanced User Management CRUD (specific routes first)
    $router->get('/users/create', 'UserController@create')->name('admin.users.create');
    $router->get('/users/{id}/edit', 'UserController@edit')->name('admin.users.edit');
    $router->get('/users/{id}', 'UserController@show')->name('admin.users.show');
    $router->post('/users', 'UserController@store')->name('admin.users.store');

    // User Management (general routes last)
    $router->get('/users', 'AdminDashboardController@users')->name('admin.users');
    $router->post('/users/status', 'AdminDashboardController@updateUserStatus')->name('admin.users.status');

    // Rider Management
    $router->get('/riders', 'AdminRiderController@index')->name('admin.riders');
    $router->post('/riders/update-status', 'AdminRiderController@updateStatus')->name('admin.riders.update-status');
    $router->post('/riders/delete', 'AdminRiderController@deleteRider')->name('admin.riders.delete');
    $router->get('/api/riders/{id}', 'AdminRiderController@getRiderDetails')->name('admin.api.riders.details');
    $router->put('/users/{id}', 'UserController@updateUser')->name('admin.users.update');
    $router->post('/users/{id}', 'UserController@updateUser')->name('admin.users.update.post');
    $router->delete('/users/{id}', 'UserController@destroy')->name('admin.users.destroy');
    $router->post('/users/{id}/toggle-status', 'UserController@toggleStatus')->name('admin.users.toggle');
    $router->post('/users/{id}/reset-password', 'UserController@resetPassword')->name('admin.users.reset-password');

    // Restaurant Management
    $router->get('/restaurants', 'AdminDashboardController@restaurants')->name('admin.restaurants');
    
    // Advanced Restaurant Management CRUD
    $router->get('/restaurants/create', 'Time2Eat\Controllers\Admin\RestaurantController@create')->name('admin.restaurants.create');
    $router->post('/restaurants', 'Time2Eat\Controllers\Admin\RestaurantController@store')->name('admin.restaurants.store');
    $router->get('/restaurants/{id}', 'Time2Eat\Controllers\Admin\RestaurantController@show')->name('admin.restaurants.show');
    $router->get('/restaurants/{id}/edit', 'Time2Eat\Controllers\Admin\RestaurantController@edit')->name('admin.restaurants.edit');
    $router->post('/restaurants/{id}/update', 'Time2Eat\Controllers\Admin\RestaurantController@updateRestaurant')->name('admin.restaurants.update');
    $router->put('/restaurants/{id}', 'Time2Eat\Controllers\Admin\RestaurantController@updateRestaurant')->name('admin.restaurants.update.put');
    $router->delete('/restaurants/{id}', 'Time2Eat\Controllers\Admin\RestaurantController@destroy')->name('admin.restaurants.destroy');
    $router->post('/restaurants/{id}/approve', 'Time2Eat\Controllers\Admin\RestaurantController@approve')->name('admin.restaurants.approve');
    $router->post('/restaurants/{id}/reject', 'Time2Eat\Controllers\Admin\RestaurantController@reject')->name('admin.restaurants.reject');
    $router->post('/restaurants/{id}/suspend', 'Time2Eat\Controllers\Admin\RestaurantController@suspend')->name('admin.restaurants.suspend');
    $router->post('/restaurants/{id}/activate', 'Time2Eat\Controllers\Admin\RestaurantController@activate')->name('admin.restaurants.activate');
    $router->post('/restaurants/{id}/toggle-status', 'Time2Eat\Controllers\Admin\RestaurantController@toggleStatus')->name('admin.restaurants.toggle-status');
    $router->post('/restaurants/{id}/commission', 'Time2Eat\Controllers\Admin\RestaurantController@updateCommission')->name('admin.restaurants.commission');

    // Order Management
    $router->get('/orders', 'AdminDashboardController@orders')->name('admin.orders');

    // Profit Analytics
    $router->get('/profit-analytics', 'AdminDashboardController@profitAnalytics')->name('admin.profit-analytics');
    $router->get('/profit-analytics/data', 'AdminDashboardController@profitAnalyticsData')->name('admin.profit-analytics.data');

    // Analytics Export
    $router->get('/analytics/export', 'controllers\\UnifiedAnalyticsController@export')->name('admin.analytics.export');

    // Enhanced User Management
    $router->get('/user-management/role-requests', 'Time2Eat\Controllers\Admin\UserManagementController@roleChangeRequests')->name('admin.user-management.role-requests');
    $router->post('/user-management/role-requests/{id}/approve', 'Time2Eat\Controllers\Admin\UserManagementController@approveRoleChange')->name('admin.user-management.approve-role');
    $router->post('/user-management/role-requests/{id}/reject', 'Time2Eat\Controllers\Admin\UserManagementController@rejectRoleChange')->name('admin.user-management.reject-role');
    $router->get('/user-management/activity', 'Time2Eat\Controllers\Admin\UserManagementController@userActivity')->name('admin.user-management.activity');
    $router->get('/user-management/analytics', 'Time2Eat\Controllers\Admin\UserManagementController@userAnalytics')->name('admin.user-management.analytics');
    $router->post('/user-management/send-message', 'Time2Eat\Controllers\Admin\UserManagementController@sendMessage')->name('admin.user-management.send-message');
    
    // Approval routes (integrated into user management)
    $router->post('/user-management/users/approve', 'Time2Eat\Controllers\Admin\UserManagementController@approveUser')->name('admin.user-management.approve-user');
    $router->post('/user-management/users/reject', 'Time2Eat\Controllers\Admin\UserManagementController@rejectUser')->name('admin.user-management.reject-user');
    $router->post('/user-management/restaurants/approve', 'Time2Eat\Controllers\Admin\UserManagementController@approveRestaurant')->name('admin.user-management.approve-restaurant');
    $router->post('/user-management/restaurants/reject', 'Time2Eat\Controllers\Admin\UserManagementController@rejectRestaurant')->name('admin.user-management.reject-restaurant');

    // Advanced Order Management CRUD
    $router->get('/orders/{id}', 'Time2Eat\Controllers\Admin\OrderController@show')->name('admin.orders.show');
    $router->get('/orders/{id}/track', 'Time2Eat\Controllers\Admin\OrderController@track')->name('admin.orders.track');
    $router->post('/orders/{id}/status', 'Time2Eat\Controllers\Admin\OrderController@updateStatus')->name('admin.orders.status');
    $router->post('/orders/{id}/cancel', 'Time2Eat\Controllers\Admin\OrderController@cancel')->name('admin.orders.cancel');
    $router->post('/orders/{id}/refund', 'Time2Eat\Controllers\Admin\OrderController@refund')->name('admin.orders.refund');

    // Analytics and Financial
    $router->get('/analytics', 'controllers\\UnifiedAnalyticsController@index')->name('admin.analytics');
    $router->get('/financial', 'AdminDashboardController@financial')->name('admin.financial');
    $router->get('/system-status', 'AdminDashboardController@systemStatus')->name('admin.system-status');
    
    // Categories Management
    $router->get('/categories', 'AdminDashboardController@categories')->name('admin.categories');

    // Advanced Category Management CRUD
    $router->get('/categories/api', 'Time2Eat\Controllers\Admin\CategoryController@index')->name('admin.categories.api');
    $router->post('/categories', 'Time2Eat\Controllers\Admin\CategoryController@store')->name('admin.categories.store');
    $router->get('/categories/{id}', 'Time2Eat\Controllers\Admin\CategoryController@show')->name('admin.categories.show');
    $router->put('/categories/{id}', 'Time2Eat\Controllers\Admin\CategoryController@update')->name('admin.categories.update');
    $router->post('/categories/{id}', 'Time2Eat\Controllers\Admin\CategoryController@update')->name('admin.categories.update.post');
    $router->delete('/categories/{id}', 'Time2Eat\Controllers\Admin\CategoryController@destroy')->name('admin.categories.destroy');
    $router->post('/categories/{id}/toggle-status', 'Time2Eat\Controllers\Admin\CategoryController@toggleStatus')->name('admin.categories.toggle');

    // System Logs
    $router->get('/logs', 'AdminDashboardController@logs')->name('admin.logs');

    // Delivery Management
    $router->get('/deliveries', 'AdminDashboardController@deliveries')->name('admin.deliveries');
    $router->get('/deliveries/zones', 'AdminDashboardController@deliveryZones')->name('admin.delivery-zones');
    
    // Dispute Management - Enhanced
    $router->get('/disputes', 'DisputeController@index')->name('admin.disputes');
    $router->get('/disputes/{id}', 'DisputeController@show')->name('admin.disputes.show');
    $router->post('/disputes/{id}/status', 'DisputeController@updateStatus')->name('admin.disputes.update-status');
    
    // Data Management
    $router->get('/data', 'AdminDashboardController@data')->name('admin.data');

    // Admin Tools
    // Removed duplicate analytics route - now using /admin/analytics
    $router->get('/tools/backups', 'AdminToolsController@backups')->name('admin.tools.backups');
    $router->post('/tools/backups', 'AdminToolsController@backups')->name('admin.tools.backups.post');
    $router->get('/tools/notifications', 'AdminToolsController@notifications')->name('admin.tools.notifications');
    $router->post('/tools/notifications', 'AdminToolsController@notifications')->name('admin.tools.notifications.post');
    
    // Notification CRUD routes
    $router->post('/tools/notifications/create', 'AdminToolsController@createNotification')->name('admin.tools.notifications.create');
    $router->post('/tools/notifications/update', 'AdminToolsController@updateNotification')->name('admin.tools.notifications.update');
    $router->post('/tools/notifications/delete', 'AdminToolsController@deleteNotification')->name('admin.tools.notifications.delete');
    $router->post('/tools/notifications/toggle', 'AdminToolsController@toggleNotification')->name('admin.tools.notifications.toggle');
    
    // User-facing notifications page
    $router->get('/notifications', 'AdminToolsController@viewNotifications')->name('admin.notifications');
    $router->post('/notifications/mark-read', 'AdminToolsController@markNotificationAsRead')->name('admin.notifications.mark-read');
    $router->post('/notifications/mark-all-read', 'AdminToolsController@markAllNotificationsAsRead')->name('admin.notifications.mark-all-read');
    $router->post('/notifications/delete', 'AdminToolsController@deleteNotification')->name('admin.notifications.delete');
    $router->get('/tools/settings', 'Time2Eat\Controllers\Admin\SettingsController@index')->name('admin.tools.settings');
    $router->post('/tools/settings/save', 'Time2Eat\Controllers\Admin\SettingsController@save')->name('admin.tools.settings.save');
    $router->post('/tools/settings/update', 'Time2Eat\Controllers\Admin\SettingsController@update')->name('admin.tools.settings.update');
    $router->post('/tools/settings/reset/{group}', 'Time2Eat\Controllers\Admin\SettingsController@resetGroup')->name('admin.tools.settings.reset.group');
    $router->post('/tools/settings/reset', 'Time2Eat\Controllers\Admin\SettingsController@reset')->name('admin.tools.settings.reset');
    $router->get('/tools/settings/group/{group}', 'Time2Eat\Controllers\Admin\SettingsController@getByGroup')->name('admin.tools.settings.group');

// Payment Settings
$router->get('/payment-settings', 'AdminPaymentSettingsController@index')->name('admin.payment-settings');
$router->post('/payment-settings/save', 'AdminPaymentSettingsController@save')->name('admin.payment-settings.save');
$router->post('/payment-settings/test-gateway', 'AdminPaymentSettingsController@testGateway')->name('admin.payment-settings.test-gateway');
$router->post('/payment-settings/check-cod-eligibility', 'AdminPaymentSettingsController@checkUserCODEligibility')->name('admin.payment-settings.check-cod-eligibility');
$router->get('/payment-settings/cod-trust-stats', 'AdminPaymentSettingsController@getCODTrustStats')->name('admin.payment-settings.cod-trust-stats');

// Withdrawal Management
$router->get('/withdrawals', 'AdminWithdrawalController@index')->name('admin.withdrawals');
$router->post('/withdrawals/process', 'AdminWithdrawalController@process')->name('admin.withdrawals.process');
$router->get('/withdrawals/details', 'AdminWithdrawalController@details')->name('admin.withdrawals.details');
$router->get('/withdrawals/stats', 'AdminWithdrawalController@stats')->name('admin.withdrawals.stats');
$router->get('/withdrawals/export', 'AdminWithdrawalController@export')->name('admin.withdrawals.export');

    // Map Settings
    $router->get('/tools/map-settings', 'AdminMapSettingsController@index')->name('admin.tools.map-settings');
    $router->post('/tools/map-settings/save', 'AdminMapSettingsController@save')->name('admin.tools.map-settings.save');

    // Data Management & Export Routes (merged)
    $router->get('/data', 'AdminDashboardController@data')->name('admin.data');
    $router->get('/export/{type}', 'AdminExportController@exportData')->name('admin.export');
    $router->get('/export/dashboard', 'AdminExportController@exportDashboard')->name('admin.export.dashboard');
    $router->get('/export/users', 'AdminExportController@exportUsers')->name('admin.export.users');
    $router->get('/export/orders', 'AdminExportController@exportOrders')->name('admin.export.orders');
    $router->get('/export/restaurants', 'AdminExportController@exportRestaurants')->name('admin.export.restaurants');
    $router->get('/export/payments', 'AdminExportController@exportPayments')->name('admin.export.payments');
    $router->get('/export/reviews', 'AdminExportController@exportReviews')->name('admin.export.reviews');
    $router->get('/export/analytics', 'AdminExportController@exportAnalytics')->name('admin.export.analytics');
    $router->get('/export/all', 'AdminExportController@exportAllData')->name('admin.export.all');

    // Cancellation Management
    $router->get('/cancellations', 'CancellationController@index')->name('admin.cancellations.index');
    $router->get('/cancellations/{id}', 'CancellationController@show')->name('admin.cancellations.show');
    $router->post('/cancellations/{id}/approve', 'CancellationController@approve')->name('admin.cancellations.approve');
    $router->post('/cancellations/{id}/reject', 'CancellationController@reject')->name('admin.cancellations.reject');

    // Review Management
    $router->get('/reviews', 'Time2Eat\Controllers\Admin\ReviewController@index')->name('admin.reviews.index');
    $router->get('/reviews/pending', 'Time2Eat\Controllers\Admin\ReviewController@pending')->name('admin.reviews.pending');
    $router->post('/reviews/{id}/approve', 'Time2Eat\Controllers\Admin\ReviewController@approve')->name('admin.reviews.approve');
    $router->post('/reviews/{id}/reject', 'Time2Eat\Controllers\Admin\ReviewController@reject')->name('admin.reviews.reject');
    $router->post('/reviews/{id}/hide', 'Time2Eat\Controllers\Admin\ReviewController@hide')->name('admin.reviews.hide');
    $router->post('/reviews/{id}/delete', 'Time2Eat\Controllers\Admin\ReviewController@delete')->name('admin.reviews.delete');
    $router->post('/reviews/bulk-action', 'Time2Eat\Controllers\Admin\ReviewController@bulkAction')->name('admin.reviews.bulk-action');
    $router->get('/reviews/{id}/details', 'Time2Eat\Controllers\Admin\ReviewController@details')->name('admin.reviews.details');
    $router->get('/reviews/stats', 'Time2Eat\Controllers\Admin\ReviewController@stats')->name('admin.reviews.stats');
    $router->get('/reviews/export', 'Time2Eat\Controllers\Admin\ReviewController@export')->name('admin.reviews.export');

    // Performance Management
    $router->get('/performance', 'PerformanceController@dashboard')->name('admin.performance.dashboard');
    $router->post('/performance/optimize', 'PerformanceController@optimize')->name('admin.performance.optimize');
    $router->post('/performance/optimize-images', 'PerformanceController@optimizeImages')->name('admin.performance.optimize-images');
    $router->post('/performance/clear-cache', 'PerformanceController@clearCache')->name('admin.performance.clear-cache');
    $router->get('/performance/metrics', 'PerformanceController@metrics')->name('admin.performance.metrics');
    $router->get('/performance/test', 'PerformanceController@test')->name('admin.performance.test');

    // Database Performance Optimization
    $router->post('/performance/database/optimize', 'PerformanceController@optimizeDatabase')->name('admin.performance.database.optimize');
    $router->post('/performance/database/migrate', 'PerformanceController@runDatabaseMigration')->name('admin.performance.database.migrate');
    $router->get('/performance/database/metrics', 'PerformanceController@getDatabaseMetrics')->name('admin.performance.database.metrics');
});

// ============================================================================
// PERFORMANCE ROUTES (Public)
// ============================================================================

// Optimized image serving
$router->get('/images/optimized', 'PerformanceController@serveImage')->name('images.optimized');

// ============================================================================
// API ROUTES (JSON Responses)
// ============================================================================

// ============================================================================
// PUBLIC API ROUTES (No authentication required)
// ============================================================================

// Restaurant API
$router->get('/api/restaurants', 'controllers\Api\RestaurantController@index')->name('api.restaurants.index');
$router->get('/api/restaurants/{id}', 'controllers\Api\RestaurantController@show')->name('api.restaurants.show');
$router->get('/api/restaurants/{id}/menu', 'controllers\Api\RestaurantController@menu')->name('api.restaurants.menu');

// User Favorites API (requires authentication)
$router->group(['prefix' => '/api/user', 'middleware' => ['AuthMiddleware']], function($router) {
    $router->get('/favorites', 'controllers\Api\FavoritesController@index')->name('api.favorites.index');
    $router->post('/favorites', 'controllers\Api\FavoritesController@store')->name('api.favorites.store');
    $router->delete('/favorites/{id}', 'controllers\Api\FavoritesController@destroy')->name('api.favorites.destroy');
    $router->get('/favorites/check/{menu_item_id}', 'controllers\Api\FavoritesController@check')->name('api.favorites.check');
});

$router->group(['prefix' => '/api/v1'], function($router) {

    // Authentication API
    $router->post('/auth/login', 'controllers\Api\AuthController@login');
    $router->post('/auth/register', 'controllers\Api\AuthController@register');
    $router->post('/auth/refresh', 'controllers\Api\AuthController@refresh');
    $router->get('/auth/check', 'controllers\Api\AuthController@checkAuth');

    // Cart API routes removed - using direct API files in api/cart/ instead
    // Direct API endpoints: /eat/api/cart/add.php, /eat/api/cart/update.php, etc.

    // Authenticated API
    $router->group(['middleware' => ['ApiAuthMiddleware']], function($router) {
        $router->post('/auth/logout', 'controllers\Api\AuthController@logout');
        // Note: ProfileController may not exist - verify before using
        // $router->get('/profile', 'controllers\Api\ProfileController@show');
        // $router->put('/profile', 'controllers\Api\ProfileController@update');

        // Cart API - using direct API files in api/cart/ instead

        // Orders API - Fixed namespace
        $router->get('/orders', 'controllers\Api\OrderController@index');
        $router->post('/orders', 'controllers\Api\OrderController@store');
        $router->get('/orders/{id}', 'controllers\Api\OrderController@show');
        $router->post('/orders/{id}/cancel', 'controllers\Api\OrderController@cancel');
        $router->get('/orders/{id}/items', 'controllers\Api\OrderController@getOrderItems');
        $router->post('/orders/{id}/reorder', 'controllers\Api\OrderController@reorderItems');
        $router->post('/orders/{id}/rate', 'controllers\Api\OrderController@rateOrder');
        $router->post('/orders/{id}/confirm-receipt', 'controllers\Api\OrderController@confirmReceipt');
        $router->get('/orders/{id}/tracking', 'controllers\Api\OrderController@getTrackingData');
        $router->post('/orders/{id}/message-rider', 'controllers\Api\OrderController@messageRider');

        // Payment API
        $router->post('/payments/process', 'PaymentController@processPayment');
        $router->get('/payments/status', 'PaymentController@getPaymentStatus');
        $router->post('/payments/refund', 'PaymentController@processRefund');

        // Reviews API
        $router->get('/reviews', 'ReviewController@apiGetReviews');
    });

    // Payment Webhooks (no authentication required)
    $router->post('/payments/webhook/{provider}', 'PaymentController@webhook');
});

// ============================================================================
// WEBHOOK ROUTES (External Services)
// ============================================================================

$router->group(['prefix' => '/webhooks'], function($router) {
    $router->post('/stripe', 'Webhook\StripeController@handle');
    $router->post('/paypal', 'Webhook\PayPalController@handle');
    $router->post('/orange-money', 'Webhook\OrangeMoneyController@handle');
    $router->post('/mtn-momo', 'Webhook\MTNMoMoController@handle');
});

// ============================================================================
// CART AND CHECKOUT ROUTES
// ============================================================================

// Cart Routes - Cart is now a modal, no separate page needed
// Cart modal is available on all pages via cart component
// Direct API endpoints in api/cart/ handle cart operations
$router->get('/cart', function() {
    // Show info page explaining cart is now a modal
    require __DIR__ . '/../src/views/cart/modal-info.php';
})->name('cart.info');

// Checkout routes moved to top of file (before AuthMiddleware group)

// Payment Routes
$router->get('/payment/success', 'PaymentController@success')->name('payment.success');
$router->get('/payment/failure', 'PaymentController@failure')->name('payment.failure');
$router->get('/payment/tranzak-return', 'PaymentController@tranzakReturn')->name('payment.tranzak.return');
$router->post('/api/payment/tranzak/notify', 'PaymentController@webhook')->name('payment.tranzak.webhook');

// Tranzak webhook route matching Developer Portal configuration
// URL: https://www.time2eat.org/api/payments/webhook/tranzak
$router->post('/api/payments/webhook/tranzak', 'PaymentController@webhook')->name('payment.tranzak.webhook.portal');

// Real-time Tracking Routes
$router->get('/track', 'TrackingController@getTrackingPage')->name('tracking.search');
$router->get('/track/{code}', 'TrackingController@getTrackingPage')->name('tracking.live');

// Tracking API Routes
$router->group(['prefix' => '/api/tracking'], function($router) {
    // Public tracking (no auth required)
    $router->get('/code/{tracking_code}', 'TrackingController@trackByCode')->name('api.tracking.code');
    $router->get('/order/{order_id}', 'controllers\Api\TrackingApiController@getOrderTracking')->name('api.tracking.order.get');

    // Authenticated tracking routes
    $router->group(['middleware' => ['AuthMiddleware']], function($router) {
        $router->post('/order', 'TrackingController@trackOrder')->name('api.tracking.order');
        $router->post('/rider/location', 'controllers\Api\TrackingApiController@updateRiderLocation')->name('api.tracking.rider.location');
        $router->get('/customer/{order_id}', 'controllers\Api\TrackingApiController@getCustomerLocation')->name('api.tracking.customer');
        $router->get('/route/{delivery_id}', 'controllers\Api\TrackingApiController@getDeliveryRoute')->name('api.tracking.route');
        $router->post('/location/update', 'TrackingController@updateRiderLocation')->name('api.tracking.location.update');
        $router->post('/status/update', 'TrackingController@updateDeliveryStatus')->name('api.tracking.status.update');
        $router->get('/updates/{delivery_id}', 'TrackingController@getDeliveryUpdates')->name('api.tracking.updates');
    });
});

// Affiliate Routes
$router->group(['prefix' => '/affiliate', 'middleware' => ['AuthMiddleware']], function($router) {
    $router->get('/dashboard', 'AffiliateController@dashboard')->name('affiliate.dashboard');
    $router->get('/earnings', 'AffiliateController@earnings')->name('affiliate.earnings');
    $router->get('/withdrawals', 'AffiliateController@withdrawals')->name('affiliate.withdrawals');
    $router->get('/referrals', 'AffiliateController@referrals')->name('affiliate.referrals');
    $router->get('/request-withdrawal', 'AffiliateController@requestWithdrawal')->name('affiliate.request-withdrawal');
    $router->post('/request-withdrawal', 'AffiliateController@processWithdrawalRequest')->name('affiliate.process-withdrawal');
    $router->get('/stats', 'AffiliateController@getAffiliateStats')->name('affiliate.stats');
});

// Public Affiliate API Routes
$router->get('/api/affiliate/validate-code', 'AffiliateController@validateReferralCode')->name('api.affiliate.validate-code');
$router->get('/api/validate-referral', 'AuthController@validateReferral')->name('api.validate-referral');

// Push Notification API Routes
$router->group(['prefix' => '/api/push', 'middleware' => ['AuthMiddleware']], function($router) {
    $router->post('/subscribe', 'PushNotificationController@subscribe')->name('api.push.subscribe');
    $router->post('/unsubscribe', 'PushNotificationController@unsubscribe')->name('api.push.unsubscribe');
    $router->get('/subscriptions', 'PushNotificationController@getSubscriptions')->name('api.push.subscriptions');
    $router->post('/send', 'PushNotificationController@sendNotification')->name('api.push.send');
});

// Performance API Routes
$router->group(['prefix' => '/api/performance'], function($router) {
    $router->post('/metrics', 'PerformanceController@recordMetrics')->name('api.performance.metrics');
    $router->get('/status', 'PerformanceController@getStatus')->name('api.performance.status');
});

// Admin Affiliate Routes
$router->group(['prefix' => '/admin/affiliate', 'middleware' => ['AuthMiddleware']], function($router) {
    $router->get('/dashboard', 'AdminAffiliateController@dashboard')->name('admin.affiliate.dashboard');
    $router->get('/affiliates', 'AdminAffiliateController@affiliates')->name('admin.affiliate.affiliates');
    $router->get('/withdrawals', 'AdminAffiliateController@withdrawals')->name('admin.affiliate.withdrawals');
    $router->get('/payouts', 'AdminAffiliateController@payouts')->name('admin.affiliate.payouts');
    $router->get('/details', 'AdminAffiliateController@affiliateDetails')->name('admin.affiliate.details');
    $router->post('/approve-withdrawal', 'AdminAffiliateController@approveWithdrawal')->name('admin.affiliate.approve-withdrawal');
    $router->post('/reject-withdrawal', 'AdminAffiliateController@rejectWithdrawal')->name('admin.affiliate.reject-withdrawal');
    $router->post('/update-commission', 'AdminAffiliateController@updateCommissionRate')->name('admin.affiliate.update-commission');
    $router->post('/update-status', 'AdminAffiliateController@updateAffiliateStatus')->name('admin.affiliate.update-status');
    $router->post('/process-payouts', 'AdminAffiliateController@processPayouts')->name('admin.affiliate.process-payouts');
    $router->post('/update-payout-status', 'AdminAffiliateController@updatePayoutStatus')->name('admin.affiliate.update-payout-status');
    // Removed analytics and communication routes - functionality consolidated into main page
});

// Individual Affiliate CRUD Routes (matching JavaScript calls)
$router->group(['prefix' => '/admin/affiliates', 'middleware' => ['AuthMiddleware']], function($router) {
    // Removed commission-settings route - commissions are now edited inline
    
    // Parameterized routes AFTER specific routes
    $router->get('/{id}', 'AdminAffiliateController@viewAffiliate')->name('admin.affiliates.view');
    // Removed edit route - now using inline editing
    $router->post('/{id}/suspend', 'AdminAffiliateController@suspendAffiliate')->name('admin.affiliates.suspend');
    $router->post('/{id}/activate', 'AdminAffiliateController@activateAffiliate')->name('admin.affiliates.activate');
    $router->put('/{id}', 'AdminAffiliateController@updateAffiliate')->name('admin.affiliates.update');
});

// ============================================================================
// UTILITY ROUTES
// ============================================================================

// File Uploads
$router->post('/upload/image', 'UploadController@image')->middleware(['AuthMiddleware']);
$router->post('/upload/document', 'UploadController@document')->middleware(['AuthMiddleware']);

// Real-time Features
$router->get('/sse/orders/{id}', 'SSEController@orderUpdates')->middleware(['AuthMiddleware']);
$router->get('/sse/notifications', 'SSEController@notifications')->middleware(['AuthMiddleware']);

// ============================================================================
// RIDER DELIVERY MANAGEMENT ROUTES
// ============================================================================

// Rider Delivery Views
$router->get('/rider/delivery-dashboard', function() {
    require_once __DIR__ . '/../src/views/rider/delivery-dashboard.php';
})->middleware(['AuthMiddleware', 'RoleMiddleware:rider']);

$router->get('/rider/navigation', function() {
    require_once __DIR__ . '/../src/views/rider/navigation.php';
})->middleware(['AuthMiddleware', 'RoleMiddleware:rider']);

$router->get('/rider/earnings', 'RiderDashboardController@earnings')->middleware(['AuthMiddleware', 'RoleMiddleware:rider']);
$router->post('/rider/request-withdrawal', 'RiderDashboardController@requestWithdrawal')->middleware(['AuthMiddleware', 'RoleMiddleware:rider']);

// Rider Delivery API Routes
$router->group(['prefix' => '/api/rider', 'middleware' => ['AuthMiddleware', 'RoleMiddleware:rider']], function($router) {
    $router->get('/available-deliveries', 'RiderDeliveryController@getAvailableDeliveries');
    $router->post('/accept-delivery', 'RiderDeliveryController@acceptDelivery');
    $router->post('/reject-delivery', 'RiderDeliveryController@rejectDelivery');
    $router->post('/update-delivery-status', 'RiderDeliveryController@updateDeliveryStatus');
    $router->post('/update-location', 'RiderDeliveryController@updateLocation');
    $router->get('/navigation-route', 'RiderDeliveryController@getNavigationRoute');
    $router->get('/earnings', 'RiderDeliveryController@getEarnings');
    $router->post('/toggle-availability', 'RiderDashboardController@toggleAvailability');
    $router->get('/status', 'RiderDashboardController@getStatus')->name('api.rider.status');
});

// Notification API Routes
$router->group(['prefix' => '/api/notifications'], function($router) {
    $router->get('/unread-count', 'NotificationController@getUnreadCount')->name('api.notifications.unread');
    $router->get('/recent', 'NotificationController@getRecentNotifications')->name('api.notifications.recent');
    $router->post('/mark-read', 'NotificationController@markNotificationRead')->name('api.notifications.mark-read');
    $router->post('/mark-all-read', 'NotificationController@markAllNotificationsRead')->name('api.notifications.mark-all-read');
    $router->post('/subscribe', 'NotificationController@subscribe')->name('api.notifications.subscribe');
    $router->get('/vapid-key', 'NotificationController@getVapidKey')->name('api.notifications.vapid');
});

// Message API Routes
$router->group(['prefix' => '/api/messages'], function($router) {
    $router->get('/unread-count', 'NotificationController@getUnreadCount')->name('api.messages.unread');
});

// Unified Order Management API Routes
$router->group(['prefix' => '/api/unified-orders', 'middleware' => ['AuthMiddleware']], function($router) {
    // Order management endpoints
    $router->post('/update-status', 'UnifiedOrderController@updateStatus')->name('api.unified-orders.update-status');
    $router->get('/dashboard', 'UnifiedOrderController@getOrdersForDashboard')->name('api.unified-orders.dashboard');
    $router->get('/stats', 'UnifiedOrderController@getPlatformStats')->name('api.unified-orders.stats');
    $router->get('/flow-status', 'UnifiedOrderController@getOrderFlowStatus')->name('api.unified-orders.flow-status');

    // Order details and tracking
    $router->get('/{id}/details', 'UnifiedOrderController@getOrderDetails')->name('api.unified-orders.details');
    $router->get('/{id}/tracking', 'UnifiedOrderController@getOrderTracking')->name('api.unified-orders.tracking');
    $router->post('/{id}/cancel', 'UnifiedOrderController@cancelOrder')->name('api.unified-orders.cancel');

    // Real-time updates
    $router->get('/live-updates', 'UnifiedOrderController@getLiveUpdates')->name('api.unified-orders.live-updates');
    $router->post('/mark-notification-read', 'UnifiedOrderController@markNotificationRead')->name('api.unified-orders.mark-notification-read');
});

// Health Check
$router->get('/health', function() {
    return json_encode(['status' => 'ok', 'timestamp' => time()]);
});

// Email verification settings
$router->post('/admin/settings/email-verification', 'Admin\\SettingsController@updateEmailVerification');

return $router;
