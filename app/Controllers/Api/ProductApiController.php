<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Core\Request;
use App\Core\Response;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;

/**
 * ProductApiController — REST API sản phẩm public
 * Prefix: /api/v1
 */
final class ProductApiController
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

    /** GET /api/v1/products — Danh sách sản phẩm (filter + sort + pagination) */
    public function index(Request $request, Response $response): void
    {
        $page    = max(1, (int)$request->query('page', 1));
        $perPage = min(48, max(4, (int)$request->query('per_page', 16)));

        $filters = [];
        foreach (['category', 'brand', 'sale', 'featured', 'min_price', 'max_price'] as $key) {
            $val = $request->query($key);
            if ($val !== null && $val !== '') {
                $filters[$key] = $val;
            }
        }

        $sort    = $request->query('sort', 'newest');
        $allowed = ['newest', 'price_asc', 'price_desc', 'popular'];
        if (!in_array($sort, $allowed, true)) {
            $sort = 'newest';
        }

        $paginator = $this->productModel->shopList($filters, $sort, $page, $perPage);

        $response->json([
            'success' => true,
            'data'    => $paginator,
        ])->send();
    }

    /** GET /api/v1/products/{slug} — Chi tiết sản phẩm */
    public function show(Request $request, Response $response): void
    {
        $slug    = $request->param('slug', '');
        $product = $this->productModel->findBySlugFull($slug);

        if (!$product) {
            $response->json(['success' => false, 'message' => 'Sản phẩm không tồn tại.'], 404)->send();
            return;
        }

        $images   = $this->productModel->getImages($product['id']);
        $variants = $this->productModel->getVariants($product['id']);
        $related  = $this->productModel->related($product['category_id'], $product['id'], 4);

        $response->json([
            'success' => true,
            'data'    => array_merge($product, [
                'images'   => $images,
                'variants' => $variants,
                'related'  => $related,
            ]),
        ])->send();
    }

    /** GET /api/v1/products/{id}/variants — Variants của sản phẩm */
    public function variants(Request $request, Response $response): void
    {
        $id      = (int)$request->param('id');
        $product = $this->productModel->find($id);

        if (!$product) {
            $response->json(['success' => false, 'message' => 'Sản phẩm không tồn tại.'], 404)->send();
            return;
        }

        $variants = $this->productModel->getVariants($id);
        $response->json(['success' => true, 'data' => $variants])->send();
    }

    /** GET /api/v1/categories */
    public function categories(Request $request, Response $response): void
    {
        $categories = $this->categoryModel->allActive();
        $response->json(['success' => true, 'data' => $categories])->send();
    }

    /** GET /api/v1/brands */
    public function brands(Request $request, Response $response): void
    {
        $brands = $this->brandModel->allActive();
        $response->json(['success' => true, 'data' => $brands])->send();
    }

    /** GET /api/v1/search?q=keyword */
    public function search(Request $request, Response $response): void
    {
        $keyword = trim((string)$request->query('q', ''));
        $page    = max(1, (int)$request->query('page', 1));
        $perPage = min(48, max(4, (int)$request->query('per_page', 16)));

        if ($keyword === '') {
            $response->json([
                'success' => true,
                'data'    => ['data' => [], 'total' => 0, 'per_page' => $perPage,
                              'current_page' => 1, 'last_page' => 1],
            ])->send();
            return;
        }

        $paginator = $this->productModel->search($keyword, $page, $perPage);
        $response->json(['success' => true, 'data' => $paginator])->send();
    }

    /** GET /api/v1/search/suggestions?q=keyword — Gợi ý tìm kiếm */
    public function suggestions(Request $request, Response $response): void
    {
        $keyword = trim((string)$request->query('q', ''));

        if (strlen($keyword) < 2) {
            $response->json(['success' => true, 'data' => []])->send();
            return;
        }

        $results = $this->productModel->searchSuggestions($keyword, 8);
        $response->json(['success' => true, 'data' => $results])->send();
    }
}