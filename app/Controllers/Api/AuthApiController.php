<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Models\User;
use App\Helpers\Validator;

/**
 * AuthApiController — REST API cho đăng nhập / đăng ký / quên mật khẩu
 * Tất cả endpoints trả về JSON.
 */
final class AuthApiController
{
    private User $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    /** POST /api/v1/auth/register */
    public function register(Request $request, Response $response): void
    {
        $data = $request->body();

        $v = (new Validator($data))
            ->label('name', 'Họ tên')
            ->label('email', 'Email')
            ->label('password', 'Mật khẩu')
            ->required('name')->minLength('name', 2)->maxLength('name', 100)
            ->required('email')->email('email')
            ->required('password')->minLength('password', 8)
            ->confirmed('password');

        if ($v->fails()) {
            $response->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ.',
                'errors'  => $v->errors(),
            ], 422)->send();
            return;
        }

        $email = strtolower(trim($data['email']));

        if ($this->userModel->emailExists($email)) {
            $response->json([
                'success' => false,
                'message' => 'Email này đã được sử dụng.',
                'errors'  => ['email' => ['Email đã tồn tại.']],
            ], 422)->send();
            return;
        }

        $userId = $this->userModel->register(
            trim($data['name']),
            $email,
            $data['password'],
            trim($data['phone'] ?? '')
        );

        $user = $this->userModel->find($userId);
        Session::login($user);

        $response->json([
            'success' => true,
            'message' => 'Đăng ký thành công.',
            'user'    => [
                'id'    => $user['id'],
                'name'  => $user['name'],
                'email' => $user['email'],
                'role'  => $user['role'],
            ],
        ], 201)->send();
    }

    /** POST /api/v1/auth/login */
    public function login(Request $request, Response $response): void
    {
        $data = $request->body();

        $v = (new Validator($data))
            ->label('email', 'Email')
            ->label('password', 'Mật khẩu')
            ->required('email')->email('email')
            ->required('password');

        if ($v->fails()) {
            $response->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ.',
                'errors'  => $v->errors(),
            ], 422)->send();
            return;
        }

        $user = $this->userModel->authenticate(
            strtolower(trim($data['email'])),
            $data['password']
        );

        if (!$user) {
            $response->json([
                'success' => false,
                'message' => 'Email hoặc mật khẩu không đúng.',
            ], 401)->send();
            return;
        }

        Session::login($user);

        $response->json([
            'success' => true,
            'message' => 'Đăng nhập thành công.',
            'user'    => [
                'id'    => $user['id'],
                'name'  => $user['name'],
                'email' => $user['email'],
                'role'  => $user['role'],
            ],
        ])->send();
    }

    /** POST /api/v1/auth/logout */
    public function logout(Request $request, Response $response): void
    {
        Session::logout();
        $response->json([
            'success' => true,
            'message' => 'Đăng xuất thành công.',
        ])->send();
    }

    /** POST /api/v1/auth/forgot-password */
    public function forgotPassword(Request $request, Response $response): void
    {
        $data  = $request->body();
        $email = strtolower(trim($data['email'] ?? ''));

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $response->json([
                'success' => false,
                'message' => 'Email không hợp lệ.',
            ], 422)->send();
            return;
        }

        $token = $this->userModel->createResetToken($email);
        if ($token) {
            \App\Core\Logger::info("Password reset token for {$email}: {$token}");
        }

        // Luôn trả về success để tránh lộ thông tin
        $response->json([
            'success' => true,
            'message' => 'Nếu email tồn tại, hướng dẫn đã được gửi.',
        ])->send();
    }

    /** POST /api/v1/auth/reset-password */
    public function resetPassword(Request $request, Response $response): void
    {
        $data  = $request->body();
        $token = trim($data['token'] ?? '');

        $v = (new Validator($data))
            ->label('token', 'Token')
            ->label('password', 'Mật khẩu mới')
            ->required('token')
            ->required('password')->minLength('password', 8)
            ->confirmed('password');

        if ($v->fails()) {
            $response->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ.',
                'errors'  => $v->errors(),
            ], 422)->send();
            return;
        }

        $ok = $this->userModel->resetPassword($token, $data['password']);

        if (!$ok) {
            $response->json([
                'success' => false,
                'message' => 'Token không hợp lệ hoặc đã hết hạn.',
            ], 422)->send();
            return;
        }

        $response->json([
            'success' => true,
            'message' => 'Mật khẩu đã được đặt lại thành công.',
        ])->send();
    }
}