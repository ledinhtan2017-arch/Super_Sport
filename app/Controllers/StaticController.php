<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;

/**
 * StaticController — Trang tĩnh: About, Contact, Privacy, Return Policy
 */
final class StaticController
{
    public function about(Request $request, Response $response): void
    {
        $response->view('static/about', [
            'title'       => 'Về chúng tôi — SPORTZONE',
            'description' => 'SPORTZONE — cửa hàng đồ thể thao chính hãng hàng đầu Việt Nam.',
        ])->send();
    }

    public function contact(Request $request, Response $response): void
    {
        $response->view('static/contact', [
            'title'       => 'Liên hệ — SPORTZONE',
            'description' => 'Liên hệ với SPORTZONE để được hỗ trợ tư vấn sản phẩm.',
        ])->send();
    }

    public function privacy(Request $request, Response $response): void
    {
        $response->view('static/privacy', [
            'title'       => 'Chính sách bảo mật — SPORTZONE',
            'description' => 'Chính sách bảo mật thông tin khách hàng tại SPORTZONE.',
        ])->send();
    }

    public function returnPolicy(Request $request, Response $response): void
    {
        $response->view('static/return-policy', [
            'title'       => 'Chính sách đổi trả — SPORTZONE',
            'description' => 'Chính sách đổi trả 30 ngày tại SPORTZONE.',
        ])->send();
    }
}