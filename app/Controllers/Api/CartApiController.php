<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Core\Request;
use App\Core\Response;
use App\Models\Cart;

/**
 * CartApiController — REST API giỏ hàng
 * Session-based, không yêu cầu đăng nhập.
 * Prefix: /api/v1/cart
 */
final class CartApiController
{
    private Cart $cartModel;

    public function __construct()
    {
        $this->cartModel = new Cart();
    }

    /** GET /api/v1/cart */
    public function index(Request $request, Response $response): void
    {
        $cart = $this->cartModel->getCart();
        $response->json(['success' => true, 'data' => $cart])->send();
    }

    /** POST /api/v1/cart/items — Thêm sản phẩm vào giỏ */
    public function add(Request $request, Response $response): void
    {
        $data      = $request->body();
        $variantId = (int)($data['variant_id'] ?? 0);
        $quantity  = max(1, (int)($data['quantity'] ?? 1));

        if (!$variantId) {
            $response->json([
                'success' => false,
                'message' => 'Vui lòng chọn sản phẩm.',
            ], 422)->send();
            return;
        }

        try {
            $itemCount = $this->cartModel->addItem($variantId, $quantity);
            $response->json([
                'success'    => true,
                'message'    => 'Đã thêm vào giỏ hàng.',
                'item_count' => $itemCount,
            ])->send();
        } catch (\RuntimeException $e) {
            $response->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422)->send();
        }
    }

    /** PUT /api/v1/cart/items/{id} — Cập nhật số lượng */
    public function update(Request $request, Response $response): void
    {
        $itemId   = (int)$request->param('id');
        $data     = $request->body();
        $quantity = (int)($data['quantity'] ?? 0);

        try {
            $itemCount = $this->cartModel->updateItem($itemId, $quantity);
            $response->json([
                'success'    => true,
                'message'    => 'Đã cập nhật giỏ hàng.',
                'item_count' => $itemCount,
            ])->send();
        } catch (\RuntimeException $e) {
            $response->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422)->send();
        }
    }

    /** DELETE /api/v1/cart/items/{id} — Xóa item */
    public function remove(Request $request, Response $response): void
    {
        $itemId = (int)$request->param('id');

        try {
            $itemCount = $this->cartModel->removeItem($itemId);
            $response->json([
                'success'    => true,
                'message'    => 'Đã xóa sản phẩm.',
                'item_count' => $itemCount,
            ])->send();
        } catch (\RuntimeException $e) {
            $response->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422)->send();
        }
    }

    /** DELETE /api/v1/cart — Xóa toàn bộ giỏ */
    public function clear(Request $request, Response $response): void
    {
        $this->cartModel->clearCart();
        $response->json([
            'success'    => true,
            'message'    => 'Đã xóa giỏ hàng.',
            'item_count' => 0,
        ])->send();
    }

    /** POST /api/v1/cart/coupon — Áp dụng mã giảm giá */
    public function applyCoupon(Request $request, Response $response): void
    {
        $data = $request->body();
        $code = trim($data['code'] ?? '');

        if (!$code) {
            $response->json([
                'success' => false,
                'message' => 'Vui lòng nhập mã giảm giá.',
            ], 422)->send();
            return;
        }

        try {
            $result = $this->cartModel->applyCoupon($code);
            $cart   = $this->cartModel->getCart();
            $response->json([
                'success'         => true,
                'message'         => 'Áp dụng mã thành công!',
                'coupon'          => $result['coupon'],
                'discount_amount' => $result['discount_amount'],
                'item_count'      => $cart['item_count'],
            ])->send();
        } catch (\RuntimeException $e) {
            $response->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422)->send();
        }
    }

    /** DELETE /api/v1/cart/coupon — Xóa mã giảm giá */
    public function removeCoupon(Request $request, Response $response): void
    {
        $this->cartModel->removeCoupon();
        $response->json([
            'success' => true,
            'message' => 'Đã xóa mã giảm giá.',
        ])->send();
    }
}