<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Models\Cart;

/**
 * CartController — Trang giỏ hàng HTML
 * GET /cart
 */
final class CartController
{
    private Cart $cartModel;

    public function __construct()
    {
        $this->cartModel = new Cart();
    }

    /** GET /cart */
    public function index(Request $request, Response $response): void
    {
        $cart = $this->cartModel->getCart();

        $response->view('cart/index', [
            'title'       => 'Giỏ hàng — SPORTZONE',
            'description' => 'Xem và quản lý giỏ hàng của bạn tại SPORTZONE.',
            'cart'        => $cart,
        ])->send();
    }
}