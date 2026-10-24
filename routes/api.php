<?php

declare(strict_types=1);

/**
 * routes/api.php — JSON API Routes (prefix: /api/v1)
 *
 * Tất cả response là JSON.
 * Middleware CSRF vẫn áp dụng cho state-changing endpoints.
 * Auth đọc từ session (same-origin AJAX).
 */

use App\Controllers\Api\AuthApiController;
use App\Controllers\Api\ProductApiController;
use App\Controllers\Api\CartApiController;
use App\Controllers\Api\OrderApiController;
use App\Controllers\Api\AccountApiController;
use App\Controllers\Api\Admin\ProductApiController  as AdminProductApi;
use App\Controllers\Api\Admin\OrderApiController    as AdminOrderApi;
use App\Controllers\Api\Admin\UserApiController     as AdminUserApi;
use App\Controllers\Api\Admin\DiscountApiController as AdminDiscountApi;
use App\Controllers\Api\Admin\DashboardApiController as AdminDashboardApi;
use App\Controllers\Api\Admin\InventoryApiController as AdminInventoryApi;
use App\Core\Middleware\AuthMiddleware;
use App\Core\Middleware\AdminMiddleware;
use App\Core\Middleware\CsrfMiddleware;
$router->group(['prefix' => '/api/v1'], function ($router) {

    // â”€â”€ Auth â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    $router->post('/auth/register',        [AuthApiController::class, 'register'],       [CsrfMiddleware::class]);
    $router->post('/auth/login',           [AuthApiController::class, 'login'],          [CsrfMiddleware::class]);
    $router->post('/auth/logout',          [AuthApiController::class, 'logout'],         [CsrfMiddleware::class]);
    $router->post('/auth/forgot-password', [AuthApiController::class, 'forgotPassword'], [CsrfMiddleware::class]);
    $router->post('/auth/reset-password',  [AuthApiController::class, 'resetPassword'],  [CsrfMiddleware::class]);

    // â”€â”€ Products (public) â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    $router->get('/products',                [ProductApiController::class, 'index']);
    $router->get('/products/{slug}',         [ProductApiController::class, 'show']);
    $router->get('/products/{id:\d+}/variants', [ProductApiController::class, 'variants']);
    $router->get('/categories',              [ProductApiController::class, 'categories']);
    $router->get('/brands',                  [ProductApiController::class, 'brands']);
    $router->get('/search',                  [ProductApiController::class, 'search']);
    $router->get('/search/suggestions',      [ProductApiController::class, 'suggestions']);

    // â”€â”€ Cart (session-based, no auth required) â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    $router->get(    '/cart',              [CartApiController::class, 'index']);
    $router->post(   '/cart/items',        [CartApiController::class, 'add'],    [CsrfMiddleware::class]);
    $router->put(    '/cart/items/{id:\d+}', [CartApiController::class, 'update'], [CsrfMiddleware::class]);
    $router->delete( '/cart/items/{id:\d+}', [CartApiController::class, 'remove'], [CsrfMiddleware::class]);
    $router->delete( '/cart',              [CartApiController::class, 'clear'],  [CsrfMiddleware::class]);
    $router->post(   '/cart/coupon',       [CartApiController::class, 'applyCoupon'], [CsrfMiddleware::class]);
    $router->delete( '/cart/coupon',       [CartApiController::class, 'removeCoupon'], [CsrfMiddleware::class]);

    // â”€â”€ Orders â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    $router->post('/orders',               [OrderApiController::class, 'create'], [AuthMiddleware::class, CsrfMiddleware::class]);
    $router->get( '/orders',               [OrderApiController::class, 'index'],  [AuthMiddleware::class]);
    $router->get( '/orders/{id:\d+}',      [OrderApiController::class, 'show'],   [AuthMiddleware::class]);
    $router->post('/orders/{id:\d+}/cancel', [OrderApiController::class, 'cancel'], [AuthMiddleware::class, CsrfMiddleware::class]);

    // â”€â”€ Account â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    $router->get(  '/account/profile',          [AccountApiController::class, 'profile'],         [AuthMiddleware::class]);
    $router->put(  '/account/profile',          [AccountApiController::class, 'updateProfile'],   [AuthMiddleware::class, CsrfMiddleware::class]);
    $router->put(  '/account/password',         [AccountApiController::class, 'changePassword'],  [AuthMiddleware::class, CsrfMiddleware::class]);
    $router->get(  '/account/addresses',        [AccountApiController::class, 'addresses'],       [AuthMiddleware::class]);
    $router->post( '/account/addresses',        [AccountApiController::class, 'addAddress'],      [AuthMiddleware::class, CsrfMiddleware::class]);
    $router->put(  '/account/addresses/{id:\d+}',[AccountApiController::class, 'updateAddress'], [AuthMiddleware::class, CsrfMiddleware::class]);
    $router->delete('/account/addresses/{id:\d+}',[AccountApiController::class, 'deleteAddress'],[AuthMiddleware::class, CsrfMiddleware::class]);

    // â”€â”€ Admin API â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    $router->group(['prefix' => '/admin', 'middleware' => [AdminMiddleware::class]], function ($router) {

        // Dashboard stats
        $router->get('/dashboard/stats',  [AdminDashboardApi::class, 'stats']);
        $router->get('/reports/revenue',  [AdminDashboardApi::class, 'revenue']);

        // Products
        $router->get(    '/products',             [AdminProductApi::class, 'index']);
        $router->post(   '/products',             [AdminProductApi::class, 'store'],   [CsrfMiddleware::class]);
        $router->get(    '/products/{id:\d+}',    [AdminProductApi::class, 'show']);
        $router->put(    '/products/{id:\d+}',    [AdminProductApi::class, 'update'],  [CsrfMiddleware::class]);
        $router->delete( '/products/{id:\d+}',    [AdminProductApi::class, 'destroy'], [CsrfMiddleware::class]);

        // Product images
        $router->post(   '/products/{id:\d+}/images',              [AdminProductApi::class, 'uploadImage'],  [CsrfMiddleware::class]);
        $router->delete( '/products/{id:\d+}/images/{imgId:\d+}',  [AdminProductApi::class, 'deleteImage'],  [CsrfMiddleware::class]);
        $router->put(    '/products/{id:\d+}/images/reorder',      [AdminProductApi::class, 'reorderImages'],[CsrfMiddleware::class]);

        // Variants
        $router->get(    '/products/{id:\d+}/variants',        [AdminProductApi::class, 'variants']);
        $router->post(   '/products/{id:\d+}/variants',        [AdminProductApi::class, 'addVariant'],    [CsrfMiddleware::class]);
        $router->put(    '/variants/{id:\d+}',                 [AdminProductApi::class, 'updateVariant'], [CsrfMiddleware::class]);
        $router->delete( '/variants/{id:\d+}',                 [AdminProductApi::class, 'deleteVariant'], [CsrfMiddleware::class]);

        // Inventory
        $router->put(    '/variants/{id:\d+}/stock',           [AdminInventoryApi::class, 'updateStock'], [CsrfMiddleware::class]);
        $router->get(    '/inventory/low-stock',               [AdminInventoryApi::class, 'lowStock']);

        // Orders
        $router->get(    '/orders',               [AdminOrderApi::class, 'index']);
        $router->get(    '/orders/{id:\d+}',      [AdminOrderApi::class, 'show']);
        $router->put(    '/orders/{id:\d+}',      [AdminOrderApi::class, 'updateStatus'], [CsrfMiddleware::class]);

        // Users
        $router->get(    '/users',                [AdminUserApi::class, 'index']);
        $router->put(    '/users/{id:\d+}',       [AdminUserApi::class, 'update'],  [CsrfMiddleware::class]);

        // Discounts
        $router->get(    '/discounts',            [AdminDiscountApi::class, 'index']);
        $router->post(   '/discounts',            [AdminDiscountApi::class, 'store'],   [CsrfMiddleware::class]);
        $router->put(    '/discounts/{id:\d+}',   [AdminDiscountApi::class, 'update'],  [CsrfMiddleware::class]);
        $router->delete( '/discounts/{id:\d+}',   [AdminDiscountApi::class, 'destroy'], [CsrfMiddleware::class]);
    });
});
