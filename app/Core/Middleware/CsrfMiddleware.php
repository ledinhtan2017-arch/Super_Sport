<?php

declare(strict_types=1);

namespace App\Core\Middleware;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Helpers\CsrfToken;

/**
 * CsrfMiddleware — Bảo vệ CSRF cho state-changing requests
 *
 * Áp dụng cho POST, PUT, PATCH, DELETE.
 * Token đọc từ:
 *  1. POST field '_csrf_token'
 *  2. Header 'X-CSRF-Token' (AJAX)
 *
 * GET requests được bỏ qua (idempotent).
 */
final class CsrfMiddleware
{
    private const SAFE_METHODS = ['GET', 'HEAD', 'OPTIONS'];

    public function handle(Request $request, Response $response, callable $next): void
    {
        if (in_array($request->method(), self::SAFE_METHODS, true)) {
            $next();
            return;
        }

        $token = $request->csrfToken();

        if ($token === null || !CsrfToken::validate($token)) {
            if ($request->wantsJson()) {
                $response->error('Token CSRF không hợp lệ. Vui lòng tải lại trang.', [], 419)->send();
            } else {
                Session::flash('error', 'Phiên làm việc hết hạn. Vui lòng thử lại.');
                $response->redirect($request->header('Referer', base_url('/')))->send();
            }
            return;
        }

        $next();
    }
}
