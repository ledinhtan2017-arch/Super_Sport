<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Helpers\Validator;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;

/**
 * Admin\ProductController — CRUD sản phẩm
 */
final class ProductController
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

    /** GET /admin/products */
    public function index(Request $request, Response $response): void
    {
        $page    = max(1, (int)$request->query('page', 1));
        $perPage = 20;
        $search  = trim((string)$request->query('search', ''));
        $status  = $request->query('status', '');

        $filters = [];
        if ($status !== '') $filters['status'] = $status;
        if ($search !== '') $filters['search'] = $search;

        $paginator  = $this->productModel->adminList($filters, $page, $perPage);
        $categories = $this->categoryModel->allActive();
        $brands     = $this->brandModel->allActive();

        $response->view('admin/products/index', [
            'title'      => 'Sản phẩm — SPORTZONE Admin',
            'pageTitle'  => 'Quản lý sản phẩm',
            'paginator'  => $paginator,
            'products'   => $paginator['data'],
            'categories' => $categories,
            'brands'     => $brands,
            'search'     => $search,
            'status'     => $status,
        ], 'admin')->send();
    }

    /** GET /admin/products/create */
    public function create(Request $request, Response $response): void
    {
        $categories = $this->categoryModel->allActive();
        $brands     = $this->brandModel->allActive();

        $response->view('admin/products/form', [
            'title'      => 'Thêm sản phẩm — SPORTZONE Admin',
            'pageTitle'  => 'Thêm sản phẩm mới',
            'product'    => null,
            'categories' => $categories,
            'brands'     => $brands,
            'old'        => Session::get('_old_input', []),
        ], 'admin')->send();
        Session::delete('_old_input');
    }

    /** POST /admin/products */
    public function store(Request $request, Response $response): void
    {
        $data = $request->body();

        $v = (new Validator($data))
            ->required('name')->maxLength('name', 200)
            ->required('slug')->maxLength('slug', 220)
            ->required('category_id')
            ->required('brand_id');

        if ($v->fails()) {
            Session::set('_old_input', $data);
            Session::flash('error', implode(' ', array_merge(...array_values($v->errors()))));
            $response->redirect(base_url('/admin/products/create'))->send();
            return;
        }

        $slug = strtolower(trim($data['slug']));

        // Check slug unique
        $existing = $this->productModel->findWhere(['slug' => $slug]);
        if ($existing) {
            Session::set('_old_input', $data);
            Session::flash('error', 'Slug đã tồn tại. Vui lòng dùng slug khác.');
            $response->redirect(base_url('/admin/products/create'))->send();
            return;
        }

        $id = $this->productModel->create([
            'name'        => trim($data['name']),
            'slug'        => $slug,
            'category_id' => (int)$data['category_id'],
            'brand_id'    => (int)$data['brand_id'],
            'description' => $data['description'] ?? null,
            'is_featured' => isset($data['is_featured']) ? 1 : 0,
            'status'      => $data['status'] ?? 'draft',
        ]);

        Session::flash('success', 'Thêm sản phẩm thành công!');
        $response->redirect(base_url("/admin/products/{$id}/edit"))->send();
    }

    /** GET /admin/products/{id}/edit */
    public function edit(Request $request, Response $response): void
    {
        $id      = (int)$request->param('id');
        $product = $this->productModel->find($id);

        if (!$product) {
            Session::flash('error', 'Sản phẩm không tồn tại.');
            $response->redirect(base_url('/admin/products'))->send();
            return;
        }

        $categories = $this->categoryModel->allActive();
        $brands     = $this->brandModel->allActive();

        $response->view('admin/products/form', [
            'title'      => 'Sửa sản phẩm — SPORTZONE Admin',
            'pageTitle'  => 'Sửa sản phẩm: ' . $product['name'],
            'product'    => $product,
            'categories' => $categories,
            'brands'     => $brands,
            'old'        => [],
        ], 'admin')->send();
    }

    /** POST /admin/products/{id} */
    public function update(Request $request, Response $response): void
    {
        $id   = (int)$request->param('id');
        $data = $request->body();

        $product = $this->productModel->find($id);
        if (!$product) {
            Session::flash('error', 'Sản phẩm không tồn tại.');
            $response->redirect(base_url('/admin/products'))->send();
            return;
        }

        $v = (new Validator($data))->required('name')->maxLength('name', 200)->required('slug');

        if ($v->fails()) {
            Session::flash('error', implode(' ', array_merge(...array_values($v->errors()))));
            $response->redirect(base_url("/admin/products/{$id}/edit"))->send();
            return;
        }

        $slug = strtolower(trim($data['slug']));
        $existing = $this->productModel->findWhere(['slug' => $slug]);
        if ($existing && (int)$existing['id'] !== $id) {
            Session::flash('error', 'Slug đã được sử dụng bởi sản phẩm khác.');
            $response->redirect(base_url("/admin/products/{$id}/edit"))->send();
            return;
        }

        $this->productModel->update($id, [
            'name'        => trim($data['name']),
            'slug'        => $slug,
            'category_id' => (int)($data['category_id'] ?? $product['category_id']),
            'brand_id'    => (int)($data['brand_id'] ?? $product['brand_id']),
            'description' => $data['description'] ?? null,
            'is_featured' => isset($data['is_featured']) ? 1 : 0,
            'status'      => $data['status'] ?? 'draft',
        ]);

        Session::flash('success', 'Cập nhật sản phẩm thành công!');
        $response->redirect(base_url("/admin/products/{$id}/edit"))->send();
    }

    /** DELETE /admin/products/{id} */
    public function destroy(Request $request, Response $response): void
    {
        $id = (int)$request->param('id');
        $this->productModel->delete($id);
        Session::flash('success', 'Đã xóa sản phẩm.');
        $response->redirect(base_url('/admin/products'))->send();
    }

    /** GET /admin/products/{id}/variants */
    public function variants(Request $request, Response $response): void
    {
        $id      = (int)$request->param('id');
        $product = $this->productModel->find($id);

        if (!$product) {
            Session::flash('error', 'Sản phẩm không tồn tại.');
            $response->redirect(base_url('/admin/products'))->send();
            return;
        }

        $variants = $this->productModel->getVariants($id);

        $response->view('admin/products/variants', [
            'title'     => 'Biến thể — SPORTZONE Admin',
            'pageTitle' => 'Biến thể: ' . $product['name'],
            'product'   => $product,
            'variants'  => $variants,
        ], 'admin')->send();
    }
}
