<?php

declare(strict_types=1);

namespace App\Core\Middleware;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;

/**
 * AdminMiddleware — Yêu cầu role = admin
 *
 * Chạy SAU AuthMiddleware trong pipeline.
 * Nếu đã login nhưng không phải admin:
 *  - JSON → 403 Forbidden
 *  - HTML → redirect về trang chủ
 */
final class AdminMiddleware
{
    public function handle(Request $request, Response $response, callable $next): void
    {
        // Phải đăng nhập trước (AuthMiddleware nên chạy trước middleware này)
        if (!Session::isLoggedIn()) {
            if ($request->wantsJson()) {
                $response->error('Vui lòng đăng nhập.', [], 401)->send();
            } else {
                Session::setPreviousUrl($request->uri());
                $response->redirect(base_url('/login'))->send();
            }
            return;
        }

        // Kiểm tra role
        if (!Session::isAdmin()) {
            if ($request->wantsJson()) {
                $response->error('Bạn không có quyền truy cập.', [], 403)->send();
            } else {
                Session::flash('error', 'Bạn không có quyền truy cập khu vực này.');
                $response->redirect(base_url('/'))->send();
            }
            return;
        }

        $next();
    }
}
