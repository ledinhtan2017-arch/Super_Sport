<?php
declare(strict_types=1);
namespace App\Controllers\Api;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Models\User;
use App\Models\Order;

final class AccountApiController
{
    private User  $userModel;
    private Order $orderModel;

    public function __construct()
    {
        $this->userModel  = new User();
        $this->orderModel = new Order();
    }

    public function profile(Request $request, Response $response): void
    {
        $user = $this->userModel->find((int)Session::userId());
        unset($user['password']);
        $response->success($user)->send();
    }

    public function updateProfile(Request $request, Response $response): void
    {
        $data = $request->json();
        $allowed = ['name','phone'];
        $update  = array_intersect_key($data, array_flip($allowed));
        if (empty($update)) {
            $response->error('Không có dữ liệu cập nhật.', [], 422)->send();
            return;
        }
        $this->userModel->update((int)Session::userId(), $update);
        $response->success(null, 'Cập nhật thành công.')->send();
    }

    public function changePassword(Request $request, Response $response): void
    {
        $data = $request->json();
        $user = $this->userModel->find((int)Session::userId());

        if (!password_verify($data['current_password'] ?? '', $user['password'])) {
            $response->error('Mật khẩu hiện tại không đúng.', [], 422)->send();
            return;
        }
        if (strlen($data['password'] ?? '') < 8) {
            $response->error('Mật khẩu mới tối thiểu 8 ký tự.', [], 422)->send();
            return;
        }

        $this->userModel->update((int)Session::userId(), [
            'password' => password_hash($data['password'], PASSWORD_BCRYPT),
        ]);
        $response->success(null, 'Đổi mật khẩu thành công.')->send();
    }

    public function addresses(Request $request, Response $response): void
    {
        $addresses = $this->orderModel->getUserAddresses((int)Session::userId());
        $response->success($addresses)->send();
    }

    public function addAddress(Request $request, Response $response): void
    {
        $data = $request->json();
        $data['user_id'] = Session::userId();
        if (!empty($data['is_default'])) {
            $this->orderModel->clearDefaultAddress((int)Session::userId());
        }
        $id = $this->orderModel->insertAddress($data);
        $response->success(['id' => $id], 'Thêm địa chỉ thành công.', 201)->send();
    }

    public function updateAddress(Request $request, Response $response): void
    {
        $id   = (int)$request->param('id');
        $data = $request->json();
        $existing = $this->orderModel->findAddress($id, (int)Session::userId());
        if (!$existing) {
            $response->error('Địa chỉ không tồn tại.', [], 404)->send();
            return;
        }
        if (!empty($data['is_default'])) {
            $this->orderModel->clearDefaultAddress((int)Session::userId());
        }
        $this->orderModel->updateAddress($id, $data);
        $response->success(null, 'Cập nhật thành công.')->send();
    }

    public function deleteAddress(Request $request, Response $response): void
    {
        $id = (int)$request->param('id');
        $this->orderModel->deleteAddress($id, (int)Session::userId());
        $response->success(null, 'Đã xóa.')->send();
    }
}