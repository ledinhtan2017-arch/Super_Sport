<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Models\User;
use App\Helpers\Validator;

/**
 * AuthController — Đăng nhập / Đăng ký / Quên mật khẩu / Đặt lại mật khẩu
 *
 * Tất cả form yêu cầu CsrfMiddleware (đã khai báo trong routes/web.php).
 */
final class AuthController
{
    private User $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    // ── Login ─────────────────────────────────────────────────────────────────

    /** GET /login */
    public function showLogin(Request $request, Response $response): void
    {
        if (Session::isLoggedIn()) {
            $response->redirect(base_url('/'))->send();
            return;
        }

        $response->view('auth/login', [
            'title'       => 'Đăng nhập — SPORTZONE',
            'description' => 'Đăng nhập vào tài khoản SPORTZONE của bạn.',
            'old'         => Session::get('_old_input', []),
        ])->send();
    }

    /** POST /login */
    public function login(Request $request, Response $response): void
    {
        $data = $request->body();

        $v = (new Validator($data))
            ->label('email', 'Email')
            ->label('password', 'Mật khẩu')
            ->required('email')->email('email')
            ->required('password');

        if ($v->fails()) {
            Session::set('_old_input', ['email' => $data['email'] ?? '']);
            Session::flash('error', implode(' ', array_merge(...array_values($v->errors()))));
            $response->redirect(base_url('/login'))->send();
            return;
        }

        $user = $this->userModel->authenticate(
            strtolower(trim($data['email'])),
            $data['password']
        );

        if (!$user) {
            Session::set('_old_input', ['email' => $data['email'] ?? '']);
            Session::flash('error', 'Email hoặc mật khẩu không đúng.');
            $response->redirect(base_url('/login'))->send();
            return;
        }

        Session::login($user);
        Session::delete('_old_input');

        $redirect = Session::getPreviousUrl(
            Session::isAdmin() ? base_url('/admin') : base_url('/')
        );
        $response->redirect($redirect)->send();
    }

    // ── Register ──────────────────────────────────────────────────────────────

    /** GET /register */
    public function showRegister(Request $request, Response $response): void
    {
        if (Session::isLoggedIn()) {
            $response->redirect(base_url('/'))->send();
            return;
        }

        $response->view('auth/register', [
            'title'       => 'Đăng ký — SPORTZONE',
            'description' => 'Tạo tài khoản SPORTZONE để mua sắm dễ dàng hơn.',
            'old'         => Session::get('_old_input', []),
        ])->send();
    }

    /** POST /register */
    public function register(Request $request, Response $response): void
    {
        $data = $request->body();

        $v = (new Validator($data))
            ->label('name', 'Họ tên')
            ->label('email', 'Email')
            ->label('password', 'Mật khẩu')
            ->label('password_confirmation', 'Xác nhận mật khẩu')
            ->required('name')->minLength('name', 2)->maxLength('name', 100)
            ->required('email')->email('email')
            ->required('password')->minLength('password', 8)
            ->confirmed('password');

        if ($v->fails()) {
            Session::set('_old_input', [
                'name'  => $data['name']  ?? '',
                'email' => $data['email'] ?? '',
            ]);
            Session::flash('error', implode(' ', array_merge(...array_values($v->errors()))));
            $response->redirect(base_url('/register'))->send();
            return;
        }

        $email = strtolower(trim($data['email']));

        if ($this->userModel->emailExists($email)) {
            Session::set('_old_input', [
                'name'  => $data['name']  ?? '',
                'email' => $data['email'] ?? '',
            ]);
            Session::flash('error', 'Email này đã được sử dụng.');
            $response->redirect(base_url('/register'))->send();
            return;
        }

        $userId = $this->userModel->register(
            trim($data['name']),
            $email,
            $data['password'],
            trim($data['phone'] ?? '')
        );

        // Auto-login sau khi đăng ký
        $user = $this->userModel->find($userId);
        Session::login($user);
        Session::delete('_old_input');

        Session::flash('success', 'Chào mừng bạn đến với SPORTZONE! 🎉');
        $response->redirect(base_url('/'))->send();
    }

    // ── Logout ────────────────────────────────────────────────────────────────

    /** POST /logout */
    public function logout(Request $request, Response $response): void
    {
        Session::logout();
        Session::flash('success', 'Bạn đã đăng xuất thành công.');
        $response->redirect(base_url('/login'))->send();
    }

    // ── Forgot Password ───────────────────────────────────────────────────────

    /** GET /forgot-password */
    public function showForgotPassword(Request $request, Response $response): void
    {
        $response->view('auth/forgot-password', [
            'title'       => 'Quên mật khẩu — SPORTZONE',
            'description' => 'Đặt lại mật khẩu tài khoản SPORTZONE.',
        ])->send();
    }

    /** POST /forgot-password */
    public function forgotPassword(Request $request, Response $response): void
    {
        $data  = $request->body();
        $email = strtolower(trim($data['email'] ?? ''));

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Session::flash('error', 'Vui lòng nhập địa chỉ email hợp lệ.');
            $response->redirect(base_url('/forgot-password'))->send();
            return;
        }

        // Luôn hiển thị message thành công để không lộ thông tin email tồn tại
        $token = $this->userModel->createResetToken($email);

        if ($token) {
            // TODO: Gửi email (khi tích hợp mailer)
            // Mail::send($email, 'reset-password', ['token' => $token]);
            // Hiện tại: log token để test
            \App\Core\Logger::info("Password reset token for {$email}: {$token}");
        }

        Session::flash('success', 'Nếu email tồn tại, chúng tôi đã gửi hướng dẫn đặt lại mật khẩu.');
        $response->redirect(base_url('/forgot-password'))->send();
    }

    // ── Reset Password ────────────────────────────────────────────────────────

    /** GET /reset-password?token=xxx */
    public function showResetPassword(Request $request, Response $response): void
    {
        $token = trim((string)$request->query('token', ''));

        if (!$token) {
            Session::flash('error', 'Link đặt lại mật khẩu không hợp lệ hoặc đã hết hạn.');
            $response->redirect(base_url('/forgot-password'))->send();
            return;
        }

        $response->view('auth/reset-password', [
            'title'       => 'Đặt lại mật khẩu — SPORTZONE',
            'description' => 'Đặt lại mật khẩu tài khoản SPORTZONE.',
            'token'       => $token,
        ])->send();
    }

    /** POST /reset-password */
    public function resetPassword(Request $request, Response $response): void
    {
        $data  = $request->body();
        $token = trim($data['token'] ?? '');

        $v = (new Validator($data))
            ->label('password', 'Mật khẩu mới')
            ->label('password_confirmation', 'Xác nhận mật khẩu')
            ->required('password')->minLength('password', 8)
            ->confirmed('password');

        if ($v->fails() || !$token) {
            Session::flash('error', implode(' ', array_merge(...array_values($v->errors()))));
            $response->redirect(base_url('/reset-password?token=' . urlencode($token)))->send();
            return;
        }

        $ok = $this->userModel->resetPassword($token, $data['password']);

        if (!$ok) {
            Session::flash('error', 'Link đặt lại mật khẩu không hợp lệ hoặc đã hết hạn. Vui lòng thử lại.');
            $response->redirect(base_url('/forgot-password'))->send();
            return;
        }

        Session::flash('success', 'Mật khẩu đã được đặt lại thành công. Vui lòng đăng nhập.');
        $response->redirect(base_url('/login'))->send();
    }
}