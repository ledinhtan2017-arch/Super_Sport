<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Models\Cart;
use App\Models\Order;

/**
 * CheckoutController
 */
final class CheckoutController
{
    private Cart  $cart;
    private Order $orderModel;

    public function __construct()
    {
        $this->cart       = new Cart();
        $this->orderModel = new Order();
    }

    /** GET /checkout */
    public function index(Request $request, Response $response): void
    {
        $cartData = $this->cart->getCart();

        if (empty($cartData['items'])) {
            Session::flash('error', 'Giỏ hàng của bạn đang trống.');
            $response->redirect(base_url('/cart'))->send();
            return;
        }

        $addresses = [];
        $userId = Session::userId();
        if ($userId) {
            $addresses = $this->orderModel->getUserAddresses((int)$userId);
        }

        $response->view('checkout/index', [
            'title'       => 'Thanh toán — SPORTZONE',
            'description' => 'Hoàn tất đơn hàng của bạn.',
            'cart'        => $cartData,
            'addresses'   => $addresses,
        ])->send();
    }

    /** GET /checkout/success?order=ORDER_NUMBER */
    public function success(Request $request, Response $response): void
    {
        $orderNumber = $request->query('order', '');
        $order       = null;

        if ($orderNumber) {
            $order = $this->orderModel->findByOrderNumber($orderNumber);
            if ($order && (int)$order['user_id'] !== (int)Session::userId()) {
                $order = null;
            }
        }

        if (!$order) {
            $response->redirect(base_url('/'))->send();
            return;
        }

        $response->view('checkout/success', [
            'title'       => 'Đặt hàng thành công — SPORTZONE',
            'description' => 'Cảm ơn bạn đã đặt hàng.',
            'order'       => $order,
        ])->send();
    }
}