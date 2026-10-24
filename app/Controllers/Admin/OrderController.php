<?php
declare(strict_types=1);
namespace App\Controllers\Admin;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Models\Order;

final class OrderController
{
    private Order $model;
    public function __construct() { $this->model = new Order(); }

    public function index(Request $request, Response $response): void
    {
        $page    = max(1, (int)$request->query('page', 1));
        $filters = [];
        foreach (['status','payment_status','search'] as $k) {
            $v = $request->query($k, '');
            if ($v !== '') $filters[$k] = $v;
        }
        $paginator = $this->model->adminList($page, 20, $filters);
        $response->view('admin/orders/index', [
            'title'     => 'Don hang — SPORTZONE Admin',
            'pageTitle' => 'Quan ly don hang',
            'paginator' => $paginator,
            'orders'    => $paginator['data'],
            'filters'   => $filters,
        ], 'admin')->send();
    }

    public function show(Request $request, Response $response): void
    {
        $id    = (int)$request->param('id');
        $order = $this->model->detail($id);
        if (!$order) {
            Session::flash('error', 'Don hang khong ton tai.');
            $response->redirect(base_url('/admin/orders'))->send();
            return;
        }
        $response->view('admin/orders/show', [
            'title'     => 'Don hang #' . $order['order_number'] . ' — SPORTZONE Admin',
            'pageTitle' => 'Chi tiet don hang',
            'order'     => $order,
        ], 'admin')->send();
    }

    public function updateStatus(Request $request, Response $response): void
    {
        $id        = (int)$request->param('id');
        $data      = $request->body();
        $newStatus = $data['status'] ?? '';
        $note      = $data['note'] ?? '';
        $adminId   = \App\Core\Session::user()['id'] ?? null;

        $allowed = ['pending','confirmed','processing','shipping','delivered','cancelled','refunded'];
        if (!in_array($newStatus, $allowed, true)) {
            Session::flash('error', 'Trang thai khong hop le.');
            $response->redirect(base_url("/admin/orders/{$id}"))->send();
            return;
        }
        $this->model->updateStatus($id, $newStatus, $adminId ? (int)$adminId : null, $note);
        Session::flash('success', 'Da cap nhat trang thai don hang!');
        $response->redirect(base_url("/admin/orders/{$id}"))->send();
    }
}
