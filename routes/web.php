<?php

declare(strict_types=1);

/**
 * routes/web.php — Web Routes (HTML responses)
 *
 * $router đã được inject từ App::loadRoutes()
 *
 * Format handler: [ControllerClass::class, 'methodName']
 * Middleware: class name string (instantiated trong Router)
 */

use App\Controllers\HomeController;
use App\Controllers\ShopController;
use App\Controllers\ProductController;
use App\Controllers\CartController;
use App\Controllers\CheckoutController;
use App\Controllers\AuthController;
use App\Controllers\AccountController;
use App\Controllers\StaticController;
use App\Controllers\Admin\DashboardController;
use App\Controllers\Admin\ProductController    as AdminProductController;
use App\Controllers\Admin\CategoryController   as AdminCategoryController;
use App\Controllers\Admin\BrandController      as AdminBrandController;
use App\Controllers\Admin\OrderController      as AdminOrderController;
use App\Controllers\Admin\UserController       as AdminUserController;
use App\Controllers\Admin\DiscountController   as AdminDiscountController;
use App\Controllers\Admin\ReportController     as AdminReportController;
use App\Core\Middleware\AuthMiddleware;
use App\Core\Middleware\AdminMiddleware;
use App\Core\Middleware\CsrfMiddleware;

// ── Storefront ──────────────────────────────────────────────────────────────

$router->get('/', [HomeController::class, 'index']);

// Shop & Product
$router->get('/shop',              [ShopController::class,   'index']);
$router->get('/product/{slug}',   [ProductController::class, 'show']);
$router->get('/search',           [ShopController::class,   'search']);

// Cart
$router->get('/cart',             [CartController::class, 'index']);

// Checkout
$router->get('/checkout',         [CheckoutController::class, 'index'],   [AuthMiddleware::class]);
$router->get('/checkout/success', [CheckoutController::class, 'success'], [AuthMiddleware::class]);

// ── Auth ────────────────────────────────────────────────────────────────────

$router->get( '/login',           [AuthController::class, 'showLogin']);
$router->post('/login',           [AuthController::class, 'login'],    [CsrfMiddleware::class]);

$router->get( '/register',        [AuthController::class, 'showRegister']);
$router->post('/register',        [AuthController::class, 'register'], [CsrfMiddleware::class]);

$router->post('/logout',          [AuthController::class, 'logout'],   [CsrfMiddleware::class]);

$router->get( '/forgot-password', [AuthController::class, 'showForgotPassword']);
$router->post('/forgot-password', [AuthController::class, 'forgotPassword'], [CsrfMiddleware::class]);

$router->get( '/reset-password',  [AuthController::class, 'showResetPassword']);
$router->post('/reset-password',  [AuthController::class, 'resetPassword'],  [CsrfMiddleware::class]);

// ── Account (requires login) ────────────────────────────────────────────────

$router->group(['prefix' => '/account', 'middleware' => [AuthMiddleware::class]], function ($router) {
    $router->get( '/profile',        [AccountController::class, 'profile']);
    $router->post('/profile',        [AccountController::class, 'updateProfile'], [CsrfMiddleware::class]);
    $router->post('/password',       [AccountController::class, 'changePassword'], [CsrfMiddleware::class]);

    $router->get( '/orders',         [AccountController::class, 'orders']);
    $router->get( '/orders/{id:\d+}',[AccountController::class, 'orderDetail']);

    $router->get( '/addresses',      [AccountController::class, 'addresses']);
    $router->post('/addresses',      [AccountController::class, 'addAddress'],    [CsrfMiddleware::class]);
    $router->put( '/addresses/{id:\d+}', [AccountController::class, 'updateAddress'], [CsrfMiddleware::class]);
    $router->delete('/addresses/{id:\d+}',[AccountController::class, 'deleteAddress'], [CsrfMiddleware::class]);
});

// ── Static Pages ────────────────────────────────────────────────────────────

$router->get('/about',          [StaticController::class, 'about']);
$router->get('/contact',        [StaticController::class, 'contact']);
$router->get('/privacy',        [StaticController::class, 'privacy']);
$router->get('/return-policy',  [StaticController::class, 'returnPolicy']);

// ── Admin (requires admin role) ─────────────────────────────────────────────

$router->group(['prefix' => '/admin', 'middleware' => [AdminMiddleware::class]], function ($router) {

    $router->get('/',           [DashboardController::class, 'index']);
    $router->get('/dashboard',  [DashboardController::class, 'index']);

    // Products
    $router->get(    '/products',                [AdminProductController::class, 'index']);
    $router->get(    '/products/create',         [AdminProductController::class, 'create']);
    $router->post(   '/products',                [AdminProductController::class, 'store'],   [CsrfMiddleware::class]);
    $router->get(    '/products/{id:\d+}/edit',  [AdminProductController::class, 'edit']);
    $router->post(   '/products/{id:\d+}',       [AdminProductController::class, 'update'],  [CsrfMiddleware::class]);
    $router->delete( '/products/{id:\d+}',       [AdminProductController::class, 'destroy'], [CsrfMiddleware::class]);
    $router->get(    '/products/{id:\d+}/variants', [AdminProductController::class, 'variants']);

    // Categories
    $router->get(    '/categories',              [AdminCategoryController::class, 'index']);
    $router->post(   '/categories',              [AdminCategoryController::class, 'store'],   [CsrfMiddleware::class]);
    $router->post(   '/categories/{id:\d+}',     [AdminCategoryController::class, 'update'],  [CsrfMiddleware::class]);
    $router->delete( '/categories/{id:\d+}',     [AdminCategoryController::class, 'destroy'], [CsrfMiddleware::class]);

    // Brands
    $router->get(    '/brands',                  [AdminBrandController::class, 'index']);
    $router->post(   '/brands',                  [AdminBrandController::class, 'store'],   [CsrfMiddleware::class]);
    $router->post(   '/brands/{id:\d+}',         [AdminBrandController::class, 'update'],  [CsrfMiddleware::class]);
    $router->delete( '/brands/{id:\d+}',         [AdminBrandController::class, 'destroy'], [CsrfMiddleware::class]);

    // Orders
    $router->get(    '/orders',                  [AdminOrderController::class, 'index']);
    $router->get(    '/orders/{id:\d+}',         [AdminOrderController::class, 'show']);
    $router->post(   '/orders/{id:\d+}/status',  [AdminOrderController::class, 'updateStatus'], [CsrfMiddleware::class]);

    // Users
    $router->get(    '/users',                   [AdminUserController::class, 'index']);
    $router->post(   '/users/{id:\d+}',          [AdminUserController::class, 'update'],  [CsrfMiddleware::class]);

    // Discounts
    $router->get(    '/discounts',               [AdminDiscountController::class, 'index']);
    $router->post(   '/discounts',               [AdminDiscountController::class, 'store'],   [CsrfMiddleware::class]);
    $router->post(   '/discounts/{id:\d+}',      [AdminDiscountController::class, 'update'],  [CsrfMiddleware::class]);
    $router->delete( '/discounts/{id:\d+}',      [AdminDiscountController::class, 'destroy'], [CsrfMiddleware::class]);

    // Reports
    $router->get(    '/reports',                 [AdminReportController::class, 'index']);
});
