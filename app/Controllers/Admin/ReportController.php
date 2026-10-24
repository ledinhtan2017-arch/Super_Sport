<?php
declare(strict_types=1);
namespace App\Controllers\Admin;
use App\Core\Request;
use App\Core\Response;
use App\Models\Order;
use App\Models\User;
use App\Models\Product;

final class ReportController
{
    public function index(Request $request, Response $response): void
    {
        $orderModel   = new Order();
        $userModel    = new User();
        $productModel = new Product();

        $stats = $orderModel->stats();

        $response->view('admin/reports/index', [
            'title'     => 'Bao cao — SPORTZONE Admin',
            'pageTitle' => 'Bao cao & Thong ke',
            'stats'     => $stats,
            'total_customers' => $userModel->count(['role' => 'customer']),
            'total_products'  => $productModel->count(),
        ], 'admin')->send();
    }
}
