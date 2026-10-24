<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Order;
use App\Models\User;

/**
 * Admin\DashboardController — Trang tổng quan admin
 */
final class DashboardController
{
    public function index(Request $request, Response $response): void
    {
        $productModel = new Product();
        $userModel    = new User();
        $orderModel   = new Order();

        $orderStats = $orderModel->stats();
        $stats = [
            'revenue'       => $orderStats['month_revenue'],
            'total_revenue' => $orderStats['total_revenue'],
            'orders'        => $orderStats['total_orders'],
            'orders_today'  => $orderStats['today_orders'],
            'pending'       => $orderStats['pending'],
            'customers'     => $userModel->count(['role' => 'customer', 'is_active' => 1]),
            'products'      => $productModel->count(),
        ];

        $recentOrders = $orderModel->adminList(1, 8)['data'];

        $response->view('admin/dashboard/index', [
            'title'        => 'Dashboard — SPORTZONE Admin',
            'pageTitle'    => 'Tổng quan',
            'stats'        => $stats,
            'recentOrders' => $recentOrders,
        ], 'admin')->send();
    }
}
