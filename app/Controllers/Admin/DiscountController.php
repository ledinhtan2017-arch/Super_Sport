<?php
declare(strict_types=1);
namespace App\Controllers\Admin;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Models\Coupon;

final class DiscountController
{
    private Coupon $model;
    public function __construct() { $this->model = new Coupon(); }

    public function index(Request $request, Response $response): void
    {
        $coupons = $this->model->allForAdmin();
        $response->view('admin/discounts/index', [
            'title'     => 'Khuyen mai — SPORTZONE Admin',
            'pageTitle' => 'Quan ly khuyen mai',
            'coupons'   => $coupons,
        ], 'admin')->send();
    }

    public function store(Request $request, Response $response): void
    {
        $data = $request->body();
        $code = strtoupper(trim($data['code'] ?? ''));
        if (!$code) {
            Session::flash('error', 'Ma coupon khong duoc trong.');
            $response->redirect(base_url('/admin/discounts'))->send();
            return;
        }
        if ($this->model->findByCode($code)) {
            Session::flash('error', 'Ma coupon da ton tai.');
            $response->redirect(base_url('/admin/discounts'))->send();
            return;
        }
        $this->model->create([
            'code'            => $code,
            'name'            => trim($data['name'] ?? $code),
            'type'            => in_array($data['type']??'', ['percent','fixed'], true) ? $data['type'] : 'percent',
            'value'           => (float)($data['value'] ?? 0),
            'max_discount'    => ($data['max_discount'] ?? '') !== '' ? (float)$data['max_discount'] : null,
            'min_order_value' => ($data['min_order_value'] ?? '') !== '' ? (float)$data['min_order_value'] : null,
            'max_uses'        => ($data['max_uses'] ?? '') !== '' ? (int)$data['max_uses'] : null,
            'starts_at'       => ($data['starts_at'] ?? '') !== '' ? $data['starts_at'] : null,
            'ends_at'         => ($data['ends_at'] ?? '') !== '' ? $data['ends_at'] : null,
            'is_active'       => isset($data['is_active']) ? 1 : 0,
        ]);
        Session::flash('success', 'Da them coupon moi!');
        $response->redirect(base_url('/admin/discounts'))->send();
    }

    public function update(Request $request, Response $response): void
    {
        $id   = (int)$request->param('id');
        $data = $request->body();
        $this->model->update($id, [
            'name'            => trim($data['name'] ?? ''),
            'type'            => in_array($data['type']??'', ['percent','fixed'], true) ? $data['type'] : 'percent',
            'value'           => (float)($data['value'] ?? 0),
            'max_discount'    => ($data['max_discount'] ?? '') !== '' ? (float)$data['max_discount'] : null,
            'min_order_value' => ($data['min_order_value'] ?? '') !== '' ? (float)$data['min_order_value'] : null,
            'max_uses'        => ($data['max_uses'] ?? '') !== '' ? (int)$data['max_uses'] : null,
            'starts_at'       => ($data['starts_at'] ?? '') !== '' ? $data['starts_at'] : null,
            'ends_at'         => ($data['ends_at'] ?? '') !== '' ? $data['ends_at'] : null,
            'is_active'       => isset($data['is_active']) ? 1 : 0,
        ]);
        Session::flash('success', 'Da cap nhat coupon!');
        $response->redirect(base_url('/admin/discounts'))->send();
    }

    public function destroy(Request $request, Response $response): void
    {
        $id = (int)$request->param('id');
        $this->model->delete($id);
        Session::flash('success', 'Da xoa coupon.');
        $response->redirect(base_url('/admin/discounts'))->send();
    }
}
