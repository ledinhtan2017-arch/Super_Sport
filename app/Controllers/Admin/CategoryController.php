<?php
declare(strict_types=1);
namespace App\Controllers\Admin;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Models\Category;

final class CategoryController
{
    private Category $model;
    public function __construct() { $this->model = new Category(); }

    public function index(Request $request, Response $response): void
    {
        $categories = $this->model->allForAdmin();
        $response->view('admin/categories/index', [
            'title'      => 'Danh muc — SPORTZONE Admin',
            'pageTitle'  => 'Quan ly danh muc',
            'categories' => $categories,
        ], 'admin')->send();
    }

    public function store(Request $request, Response $response): void
    {
        $data = $request->body();
        $name = trim($data['name'] ?? '');
        if (!$name) {
            Session::flash('error', 'Ten danh muc khong duoc trong.');
            $response->redirect(base_url('/admin/categories'))->send();
            return;
        }
        $slug = $data['slug'] ?? $this->toSlug($name);
        $existing = $this->model->findBySlug($slug);
        if ($existing) {
            Session::flash('error', 'Slug da ton tai.');
            $response->redirect(base_url('/admin/categories'))->send();
            return;
        }
        $this->model->create([
            'name'        => $name,
            'slug'        => $slug,
            'parent_id'   => ($data['parent_id'] ?? '') !== '' ? (int)$data['parent_id'] : null,
            'description' => $data['description'] ?? null,
            'sort_order'  => (int)($data['sort_order'] ?? 0),
            'is_active'   => 1,
        ]);
        Session::flash('success', 'Da them danh muc moi!');
        $response->redirect(base_url('/admin/categories'))->send();
    }

    public function update(Request $request, Response $response): void
    {
        $id   = (int)$request->param('id');
        $data = $request->body();
        $name = trim($data['name'] ?? '');
        if (!$name) {
            Session::flash('error', 'Ten danh muc khong duoc trong.');
            $response->redirect(base_url('/admin/categories'))->send();
            return;
        }
        $slug = $data['slug'] ?? $this->toSlug($name);
        $existing = $this->model->findBySlug($slug);
        if ($existing && (int)$existing['id'] !== $id) {
            Session::flash('error', 'Slug da duoc su dung boi danh muc khac.');
            $response->redirect(base_url('/admin/categories'))->send();
            return;
        }
        $this->model->update($id, [
            'name'        => $name,
            'slug'        => $slug,
            'parent_id'   => ($data['parent_id'] ?? '') !== '' ? (int)$data['parent_id'] : null,
            'description' => $data['description'] ?? null,
            'sort_order'  => (int)($data['sort_order'] ?? 0),
            'is_active'   => isset($data['is_active']) ? 1 : 0,
        ]);
        Session::flash('success', 'Da cap nhat danh muc!');
        $response->redirect(base_url('/admin/categories'))->send();
    }

    public function destroy(Request $request, Response $response): void
    {
        $id = (int)$request->param('id');
        $this->model->delete($id);
        Session::flash('success', 'Da xoa danh muc.');
        $response->redirect(base_url('/admin/categories'))->send();
    }

    private function toSlug(string $text): string
    {
        $text = mb_strtolower($text, 'UTF-8');
        $map  = ['a'=>'[aáàảãạăắằẳẵặâấầẩẫậ]','d'=>'[dđ]','e'=>'[eéèẻẽẹêếềểễệ]','i'=>'[iíìỉĩị]','o'=>'[oóòỏõọôốồổỗộơớờởỡợ]','u'=>'[uúùủũụưứừửữự]','y'=>'[yýỳỷỹỵ]'];
        foreach ($map as $ascii => $pattern) {
            $text = preg_replace("/{$pattern}/u", $ascii, $text) ?? $text;
        }
        $text = preg_replace('/[^a-z0-9\s-]/', '', $text) ?? $text;
        $text = preg_replace('/[\s-]+/', '-', trim($text)) ?? $text;
        return $text;
    }
}
