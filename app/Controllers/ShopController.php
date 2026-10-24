<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;

/**
 * ShopController — Trang danh sách sản phẩm & tìm kiếm
 */
final class ShopController
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

    /**
     * GET /shop — Danh sách sản phẩm với filter + sort + pagination
     */
    public function index(Request $request, Response $response): void
    {
        $page    = max(1, (int)$request->query('page', 1));
        $perPage = 16;

        // Thu thập filters từ query string
        $filters = [];
        foreach (['category', 'brand', 'sale', 'featured', 'min_price', 'max_price'] as $key) {
            $val = $request->query($key);
            if ($val !== null && $val !== '') {
                $filters[$key] = $val;
            }
        }

        $sort   = $request->query('sort', 'newest');
        $allowed = ['newest', 'price_asc', 'price_desc', 'popular'];
        if (!in_array($sort, $allowed, true)) {
            $sort = 'newest';
        }

        $paginator  = $this->productModel->shopList($filters, $sort, $page, $perPage);
        $categories = $this->categoryModel->allActive();
        $brands     = $this->brandModel->allActive();

        // Active category/brand label
        $activeCategory = null;
        $activeBrand    = null;
        if (!empty($filters['category'])) {
            foreach ($categories as $cat) {
                if ($cat['slug'] === $filters['category']) {
                    $activeCategory = $cat;
                    break;
                }
            }
        }
        if (!empty($filters['brand'])) {
            foreach ($brands as $b) {
                if ($b['slug'] === $filters['brand']) {
                    $activeBrand = $b;
                    break;
                }
            }
        }

        $pageTitle = 'Tất cả sản phẩm';
        if ($activeCategory) $pageTitle = $activeCategory['name'];
        if ($activeBrand)    $pageTitle = $activeBrand['name'];
        if (!empty($filters['sale']))     $pageTitle = 'Sale 🔥';
        if (!empty($filters['featured'])) $pageTitle = 'Sản phẩm nổi bật';

        $description = "Mua sắm đồ thể thao chính hãng tại SPORTZONE — {$pageTitle}. Giày, áo, quần thể thao từ Nike, Adidas, Puma và nhiều thương hiệu.";

        $response->view('shop/index', [
            'title'          => "{$pageTitle} — SPORTZONE",
            'description'    => $description,
            'products'       => $paginator['data'],
            'paginator'      => $paginator,
            'categories'     => $categories,
            'brands'         => $brands,
            'filters'        => $filters,
            'sort'           => $sort,
            'activeCategory' => $activeCategory,
            'activeBrand'    => $activeBrand,
            'pageTitle'      => $pageTitle,
        ])->send();
    }

    /**
     * GET /search — Tìm kiếm sản phẩm
     */
    public function search(Request $request, Response $response): void
    {
        $keyword = trim((string)$request->query('q', ''));
        $page    = max(1, (int)$request->query('page', 1));
        $perPage = 16;

        $paginator = ['data' => [], 'total' => 0, 'per_page' => $perPage,
                      'current_page' => 1, 'last_page' => 1, 'from' => 0, 'to' => 0];

        if ($keyword !== '') {
            $paginator = $this->productModel->search($keyword, $page, $perPage);
        }

        $response->view('shop/search', [
            'title'       => $keyword !== ''
                ? "Tìm kiếm: \"{$keyword}\" — SPORTZONE"
                : 'Tìm kiếm — SPORTZONE',
            'description' => "Kết quả tìm kiếm cho \"{$keyword}\" tại SPORTZONE.",
            'keyword'     => $keyword,
            'products'    => $paginator['data'],
            'paginator'   => $paginator,
        ])->send();
    }
}