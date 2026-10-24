<?php

declare(strict_types=1);

namespace App\Core\Middleware;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;

/**
 * AuthMiddleware — Yêu cầu đăng nhập
 *
 * Nếu chưa đăng nhập:
 *  - AJAX/JSON request → 401 JSON
 *  - HTML request → redirect về /login (lưu URL hiện tại)
 */
final class AuthMiddleware
{
    public function handle(Request $request, Response $response, callable $next): void
    {
        if (Session::isLoggedIn()) {
            $next();
            return;
        }

        if ($request->wantsJson()) {
            $response->error('Vui lòng đăng nhập để tiếp tục.', [], 401)->send();
            return;
        }

        Session::setPreviousUrl($request->uri());
        Session::flash('error', 'Vui lòng đăng nhập để tiếp tục.');
        $response->redirect(base_url('/login'))->send();
    }
}
