<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Helpers\Validator;
use App\Models\User;
use App\Models\Order;

/**
 * AccountController — Trang tài khoản khách hàng
 */
final class AccountController
{
    private User  $userModel;
    private Order $orderModel;

    public function __construct()
    {
        $this->userModel  = new User();
        $this->orderModel = new Order();
    }

    /** GET /account/profile */
    public function profile(Request $request, Response $response): void
    {
        $user = $this->userModel->find((int)Session::userId());

        $response->view('account/profile', [
            'title'       => 'Thông tin tài khoản — SPORTZONE',
            'description' => 'Quản lý thông tin cá nhân.',
            'user'        => $user,
        ])->send();
    }

    /** POST /account/profile */
    public function updateProfile(Request $request, Response $response): void
    {
        $data = $request->only(['name', 'phone']);

        $v = new Validator($data);
        $v->required('name')->maxLength('name', 100)
          ->optional('phone')->phone('phone');

        if ($v->fails()) {
            Session::flash('errors', $v->errors());
            Session::flash('old', $data);
            $response->redirect(base_url('/account/profile'))->send();
            return;
        }

        $this->userModel->update((int)Session::userId(), [
            'name'  => trim($data['name']),
            'phone' => $data['phone'] ?? null,
        ]);

        Session::flash('success', 'Cập nhật thông tin thành công!');
        $response->redirect(base_url('/account/profile'))->send();
    }

    /** POST /account/password */
    public function changePassword(Request $request, Response $response): void
    {
        $data = $request->only(['current_password', 'password', 'password_confirmation']);
        $user = $this->userModel->find((int)Session::userId());

        if (!password_verify($data['current_password'] ?? '', $user['password'])) {
            Session::flash('error', 'Mật khẩu hiện tại không đúng.');
            $response->redirect(base_url('/account/profile'))->send();
            return;
        }

        $v = new Validator($data);
        $v->required('password')->minLength('password', 8)->confirmed('password');

        if ($v->fails()) {
            Session::flash('errors', $v->errors());
            $response->redirect(base_url('/account/profile'))->send();
            return;
        }

        $this->userModel->update((int)Session::userId(), [
            'password' => password_hash($data['password'], PASSWORD_BCRYPT),
        ]);

        Session::flash('success', 'Đổi mật khẩu thành công!');
        $response->redirect(base_url('/account/profile'))->send();
    }

    /** GET /account/orders */
    public function orders(Request $request, Response $response): void
    {
        $page   = (int)$request->query('page', '1');
        $result = $this->orderModel->getUserOrders((int)Session::userId(), $page, 10);

        $response->view('account/orders', [
            'title'       => 'Đơn hàng của tôi — SPORTZONE',
            'description' => 'Theo dõi đơn hàng của bạn.',
            'orders'      => $result['data'],
            'paginator'   => $result,
        ])->send();
    }

    /** GET /account/orders/{id} */
    public function orderDetail(Request $request, Response $response): void
    {
        $id    = (int)$request->param('id');
        $order = $this->orderModel->findWithDetails($id);

        if (!$order || (int)$order['user_id'] !== (int)Session::userId()) {
            $response->status(404)->view('errors/404', ['title' => '404'])->send();
            return;
        }

        $response->view('account/order-detail', [
            'title'       => 'Chi tiết đơn — ' . $order['order_number'],
            'description' => 'Chi tiết đơn hàng.',
            'order'       => $order,
        ])->send();
    }

    /** GET /account/addresses */
    public function addresses(Request $request, Response $response): void
    {
        $addresses = $this->orderModel->getUserAddresses((int)Session::userId());

        $response->view('account/addresses', [
            'title'       => 'Địa chỉ của tôi — SPORTZONE',
            'description' => 'Quản lý địa chỉ giao hàng.',
            'addresses'   => $addresses,
        ])->send();
    }

    /** POST /account/addresses */
    public function addAddress(Request $request, Response $response): void
    {
        $data = $request->only(['recipient_name','phone','address_line','ward','district','province','is_default']);
        $data['user_id'] = Session::userId();

        if (!empty($data['is_default'])) {
            $this->orderModel->clearDefaultAddress((int)Session::userId());
        }

        $this->orderModel->insertAddress($data);

        Session::flash('success', 'Thêm địa chỉ thành công!');
        $response->redirect(base_url('/account/addresses'))->send();
    }

    /** PUT /account/addresses/{id} */
    public function updateAddress(Request $request, Response $response): void
    {
        $id   = (int)$request->param('id');
        $data = $request->only(['recipient_name','phone','address_line','ward','district','province','is_default']);

        $existing = $this->orderModel->findAddress($id, (int)Session::userId());
        if (!$existing) {
            Session::flash('error', 'Địa chỉ không tồn tại.');
            $response->redirect(base_url('/account/addresses'))->send();
            return;
        }

        if (!empty($data['is_default'])) {
            $this->orderModel->clearDefaultAddress((int)Session::userId());
        }

        $this->orderModel->updateAddress($id, $data);

        Session::flash('success', 'Cập nhật địa chỉ thành công!');
        $response->redirect(base_url('/account/addresses'))->send();
    }

    /** DELETE /account/addresses/{id} */
    public function deleteAddress(Request $request, Response $response): void
    {
        $id = (int)$request->param('id');
        $this->orderModel->deleteAddress($id, (int)Session::userId());

        if ($request->wantsJson()) {
            $response->success(null, 'Xóa thành công.')->send();
        } else {
            Session::flash('success', 'Đã xóa địa chỉ.');
            $response->redirect(base_url('/account/addresses'))->send();
        }
    }
}