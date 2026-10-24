<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;

/**
 * HomeController — Trang chủ
 */
final class HomeController
{
    private Product  $productModel;
    private Category $categoryModel;
    private Brand    $brandModel;

    public function __construct()
    {
        $this->productModel  = new Product();
        $this->categoryModel = new Category();
        $this->brandModel    = new Brand();
    }

    public function index(Request $request, Response $response): void
    {
        $featured    = $this->productModel->featured(8);
        $categories  = $this->categoryModel->allActive();
        $new_arrivals= $this->productModel->newArrivals(8);
        $brands      = $this->brandModel->allActive();

        $response->view('home/index', [
            'title'        => 'SPORTZONE — Gear For Every Champion',
            'description'  => 'Mua sắm đồ thể thao chính hãng: giày, áo, quần và phụ kiện từ Nike, Adidas, Puma và nhiều thương hiệu hàng đầu.',
            'featured'     => $featured,
            'categories'   => $categories,
            'new_arrivals' => $new_arrivals,
            'brands'       => $brands,
        ])->send();
    }
}