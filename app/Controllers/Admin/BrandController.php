<?php
declare(strict_types=1);
namespace App\Controllers\Admin;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Models\Brand;

final class BrandController
{
    private Brand $model;
    public function __construct() { $this->model = new Brand(); }

    public function index(Request $request, Response $response): void
    {
        $brands = $this->model->allForAdmin();
        $response->view('admin/brands/index', [
            'title'     => 'Thuong hieu — SPORTZONE Admin',
            'pageTitle' => 'Quan ly thuong hieu',
            'brands'    => $brands,
        ], 'admin')->send();
    }

    public function store(Request $request, Response $response): void
    {
        $data = $request->body();
        $name = trim($data['name'] ?? '');
        if (!$name) {
            Session::flash('error', 'Ten thuong hieu khong duoc trong.');
            $response->redirect(base_url('/admin/brands'))->send();
            return;
        }
        $slug = strtolower(preg_replace('/\s+/', '-', $name));
        $this->model->create([
            'name'        => $name,
            'slug'        => $data['slug'] ?? $slug,
            'description' => $data['description'] ?? null,
            'logo_url'    => $data['logo_url'] ?? null,
            'is_active'   => 1,
            'sort_order'  => (int)($data['sort_order'] ?? 0),
        ]);
        Session::flash('success', 'Da them thuong hieu moi!');
        $response->redirect(base_url('/admin/brands'))->send();
    }

    public function update(Request $request, Response $response): void
    {
        $id   = (int)$request->param('id');
        $data = $request->body();
        $name = trim($data['name'] ?? '');
        if (!$name) {
            Session::flash('error', 'Ten thuong hieu khong duoc trong.');
            $response->redirect(base_url('/admin/brands'))->send();
            return;
        }
        $this->model->update($id, [
            'name'        => $name,
            'slug'        => $data['slug'] ?? strtolower(preg_replace('/\s+/', '-', $name)),
            'description' => $data['description'] ?? null,
            'logo_url'    => $data['logo_url'] ?? null,
            'is_active'   => isset($data['is_active']) ? 1 : 0,
            'sort_order'  => (int)($data['sort_order'] ?? 0),
        ]);
        Session::flash('success', 'Da cap nhat thuong hieu!');
        $response->redirect(base_url('/admin/brands'))->send();
    }

    public function destroy(Request $request, Response $response): void
    {
        $id = (int)$request->param('id');
        $this->model->delete($id);
        Session::flash('success', 'Da xoa thuong hieu.');
        $response->redirect(base_url('/admin/brands'))->send();
    }
}
