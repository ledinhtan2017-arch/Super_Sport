<?php
declare(strict_types=1);
namespace App\Controllers\Api;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Coupon;

/**
 * OrderApiController — POST /api/v1/orders (create order from cart)
 */
final class OrderApiController
{
    private Cart   $cart;
    private Order  $orderModel;
    private Coupon $couponModel;

    public function __construct()
    {
        $this->cart        = new Cart();
        $this->orderModel  = new Order();
        $this->couponModel = new Coupon();
    }

    /** POST /api/v1/orders */
    public function create(Request $request, Response $response): void
    {
        $userId   = (int)Session::userId();
        $cartData = $this->cart->getCart();

        if (empty($cartData['items'])) {
            $response->error('Giỏ hàng trống.', [], 422)->send();
            return;
        }

        $body = $request->json();
        $required = ['recipient_name','phone','address_line','district','province','payment_method'];
        foreach ($required as $field) {
            if (empty($body[$field])) {
                $response->error("Thiếu thông tin: {$field}", [], 422)->send();
                return;
            }
        }

        $paymentMethod = $body['payment_method'];
        if (!in_array($paymentMethod, ['cod', 'bank_transfer', 'momo'], true)) {
            $response->error('Phương thức thanh toán không hợp lệ.', [], 422)->send();
            return;
        }

        // Generate order number
        $orderNumber = 'SZ' . strtoupper(substr(uniqid(), -8));

        // Calculate totals
        $subtotal       = (float)$cartData['subtotal'];
        $discountAmount = (float)($cartData['discount_amount'] ?? 0);
        $shippingFee    = $subtotal >= 500000 ? 0 : 30000;
        $total          = $subtotal - $discountAmount + $shippingFee;

        // Create order
        $orderId = $this->orderModel->createOrder([
            'user_id'         => $userId,
            'order_number'    => $orderNumber,
            'status'          => 'pending',
            'payment_method'  => $paymentMethod,
            'payment_status'  => 'pending',
            'subtotal'        => $subtotal,
            'discount_amount' => $discountAmount,
            'shipping_fee'    => $shippingFee,
            'total'           => $total,
            'coupon_code'     => $cartData['coupon']['code'] ?? null,
            'note'            => $body['note'] ?? null,
        ], $cartData['items'], [
            'recipient_name' => $body['recipient_name'],
            'phone'          => $body['phone'],
            'address_line'   => $body['address_line'],
            'ward'           => $body['ward'] ?? '',
            'district'       => $body['district'],
            'province'       => $body['province'],
        ]);

        // Increment coupon use
        if (!empty($cartData['coupon']['id'])) {
            $this->couponModel->incrementUsed((int)$cartData['coupon']['id']);
        }

        // Clear cart
        $this->cart->clearCart();

        $response->success([
            'order_number' => $orderNumber,
            'order_id'     => $orderId,
            'total'        => $total,
            'redirect'     => base_url('/checkout/success?order=' . $orderNumber),
        ], 'Đặt hàng thành công!', 201)->send();
    }

    /** GET /api/v1/orders */
    public function index(Request $request, Response $response): void
    {
        $page   = (int)$request->query('page', '1');
        $result = $this->orderModel->getUserOrders((int)Session::userId(), $page);
        $response->success($result)->send();
    }

    /** GET /api/v1/orders/{id} */
    public function show(Request $request, Response $response): void
    {
        $id    = (int)$request->param('id');
        $order = $this->orderModel->findWithDetails($id);

        if (!$order || (int)$order['user_id'] !== (int)Session::userId()) {
            $response->error('Đơn hàng không tồn tại.', [], 404)->send();
            return;
        }

        $response->success($order)->send();
    }

    /** POST /api/v1/orders/{id}/cancel */
    public function cancel(Request $request, Response $response): void
    {
        $id    = (int)$request->param('id');
        $order = $this->orderModel->find($id);

        if (!$order || (int)$order['user_id'] !== (int)Session::userId()) {
            $response->error('Đơn hàng không tồn tại.', [], 404)->send();
            return;
        }

        if (!in_array($order['status'], ['pending','confirmed'], true)) {
            $response->error('Không thể huỷ đơn hàng ở trạng thái này.', [], 422)->send();
            return;
        }

        $this->orderModel->updateStatus($id, 'cancelled', (int)Session::userId(), 'Khách hàng huỷ đơn.');
        $response->success(null, 'Huỷ đơn hàng thành công.')->send();
    }
}