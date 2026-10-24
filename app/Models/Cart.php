<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Session;

/**
 * Cart Model
 *
 * Quản lý giỏ hàng session-based (guest + logged-in).
 * Cart gắn với session_id; khi user đăng nhập, cart vẫn dùng session_id.
 *
 * Schema:
 *   carts      (id, user_id, session_id)
 *   cart_items (id, cart_id, variant_id, quantity)
 */
final class Cart extends BaseModel
{
    protected string $table = 'carts';

    // ── Cart Resolution ───────────────────────────────────────────────────────

    /**
     * Lấy hoặc tạo cart cho session hiện tại.
     * Trả về cart_id.
     */
    public function getOrCreateCart(): int
    {
        $sessionId = session_id();
        $cart = $this->rawOne(
            "SELECT id FROM `carts` WHERE `session_id` = ? LIMIT 1",
            [$sessionId]
        );

        if ($cart) {
            // Cập nhật user_id nếu user vừa đăng nhập
            $userId = Session::userId();
            if ($userId) {
                $this->db->execute(
                    "UPDATE `carts` SET `user_id` = ? WHERE `id` = ? AND `user_id` IS NULL",
                    [$userId, $cart['id']]
                );
            }
            return (int)$cart['id'];
        }

        // Tạo cart mới
        $userId = Session::userId();
        $this->db->execute(
            "INSERT INTO `carts` (`user_id`, `session_id`) VALUES (?, ?)",
            [$userId, $sessionId]
        );

        return (int)$this->db->lastInsertId();
    }

    // ── Cart Contents ─────────────────────────────────────────────────────────

    /**
     * Lấy toàn bộ giỏ hàng (kèm thông tin sản phẩm).
     *
     * @return array{items: array, item_count: int, subtotal: int, discount_amount: int, total: int, coupon: ?array}
     */
    public function getCart(): array
    {
        $cartId = $this->getOrCreateCart();

        $items = $this->raw(
            "SELECT
                ci.id,
                ci.variant_id,
                ci.quantity,
                pv.sku,
                pv.price,
                pv.sale_price,
                pv.stock_quantity,
                p.id   AS product_id,
                p.name AS product_name,
                p.slug AS product_slug,
                (SELECT image_path FROM product_images
                 WHERE product_id = p.id
                 ORDER BY sort_order ASC LIMIT 1) AS thumbnail,
                COALESCE(pv.sale_price, pv.price) AS effective_price
             FROM `cart_items` ci
             JOIN `product_variants` pv ON pv.id = ci.variant_id
             JOIN `products` p          ON p.id  = pv.product_id
             WHERE ci.cart_id = ?
             ORDER BY ci.added_at ASC",
            [$cartId]
        );

        // Lấy attributes (size, màu) cho từng variant
        foreach ($items as &$item) {
            $attrs = $this->raw(
                "SELECT av.value, a.name AS attr_name, a.slug AS attr_slug
                 FROM `variant_attribute_values` vav
                 JOIN `attribute_values` av ON av.id = vav.attribute_value_id
                 JOIN `attributes` a        ON a.id  = av.attribute_id
                 WHERE vav.variant_id = ?
                 ORDER BY a.sort_order",
                [$item['variant_id']]
            );
            $item['attributes'] = $attrs;
            $item['variant_info'] = implode(' · ', array_column($attrs, 'value'));
        }
        unset($item);

        // Tính tổng
        $subtotal   = array_sum(array_map(
            fn($i) => (int)$i['effective_price'] * (int)$i['quantity'],
            $items
        ));
        $itemCount  = array_sum(array_column($items, 'quantity'));

        // Lấy coupon từ session
        $coupon         = Session::get('cart_coupon');
        $discountAmount = 0;

        if ($coupon) {
            $discountAmount = $this->calcDiscount($coupon, $subtotal);
        }

        $total = max(0, $subtotal - $discountAmount);

        return [
            'cart_id'         => $cartId,
            'items'           => $items,
            'item_count'      => (int)$itemCount,
            'subtotal'        => $subtotal,
            'discount_amount' => $discountAmount,
            'total'           => $total,
            'coupon'          => $coupon,
        ];
    }

    // ── Item Operations ───────────────────────────────────────────────────────

    /**
     * Thêm hoặc tăng số lượng sản phẩm.
     * Trả về item_count mới.
     */
    public function addItem(int $variantId, int $quantity = 1): int
    {
        $cartId = $this->getOrCreateCart();

        // Kiểm tra tồn kho
        $variant = $this->rawOne(
            "SELECT stock_quantity FROM `product_variants` WHERE id = ?",
            [$variantId]
        );

        if (!$variant) {
            throw new \RuntimeException('Sản phẩm không tồn tại.');
        }

        // Kiểm tra item đã có trong giỏ chưa
        $existing = $this->rawOne(
            "SELECT id, quantity FROM `cart_items`
             WHERE `cart_id` = ? AND `variant_id` = ?",
            [$cartId, $variantId]
        );

        $newQty = ($existing ? $existing['quantity'] : 0) + $quantity;

        if ($newQty > $variant['stock_quantity']) {
            throw new \RuntimeException(
                "Chỉ còn {$variant['stock_quantity']} sản phẩm trong kho."
            );
        }

        if ($existing) {
            $this->db->execute(
                "UPDATE `cart_items` SET `quantity` = ? WHERE `id` = ?",
                [$newQty, $existing['id']]
            );
        } else {
            $this->db->execute(
                "INSERT INTO `cart_items` (`cart_id`, `variant_id`, `quantity`)
                 VALUES (?, ?, ?)",
                [$cartId, $variantId, $quantity]
            );
        }

        $this->touchCart($cartId);
        return $this->countItems($cartId);
    }

    /**
     * Cập nhật số lượng item.
     * Nếu quantity = 0 → xóa item.
     */
    public function updateItem(int $itemId, int $quantity): int
    {
        $cartId = $this->getOrCreateCart();

        $item = $this->rawOne(
            "SELECT ci.id, ci.variant_id, pv.stock_quantity
             FROM `cart_items` ci
             JOIN `product_variants` pv ON pv.id = ci.variant_id
             WHERE ci.id = ? AND ci.cart_id = ?",
            [$itemId, $cartId]
        );

        if (!$item) {
            throw new \RuntimeException('Sản phẩm không có trong giỏ hàng.');
        }

        if ($quantity <= 0) {
            $this->removeItem($itemId);
        } else {
            if ($quantity > $item['stock_quantity']) {
                throw new \RuntimeException(
                    "Chỉ còn {$item['stock_quantity']} sản phẩm trong kho."
                );
            }
            $this->db->execute(
                "UPDATE `cart_items` SET `quantity` = ? WHERE `id` = ?",
                [$quantity, $itemId]
            );
        }

        $this->touchCart($cartId);
        return $this->countItems($cartId);
    }

    /**
     * Xóa item khỏi giỏ.
     */
    public function removeItem(int $itemId): int
    {
        $cartId = $this->getOrCreateCart();
        $this->db->execute(
            "DELETE FROM `cart_items` WHERE `id` = ? AND `cart_id` = ?",
            [$itemId, $cartId]
        );
        $this->touchCart($cartId);
        return $this->countItems($cartId);
    }

    /**
     * Xóa toàn bộ giỏ hàng.
     */
    public function clearCart(): void
    {
        $cartId = $this->getOrCreateCart();
        $this->db->execute("DELETE FROM `cart_items` WHERE `cart_id` = ?", [$cartId]);
        Session::delete('cart_coupon');
        $this->touchCart($cartId);
    }

    // ── Coupon ────────────────────────────────────────────────────────────────

    /**
     * Áp dụng mã giảm giá.
     * Trả về coupon data + discount_amount.
     */
    public function applyCoupon(string $code): array
    {
        $cart    = $this->getCart();
        $subtotal = $cart['subtotal'];

        $coupon = $this->rawOne(
            "SELECT * FROM `coupons`
             WHERE `code` = ?
               AND `is_active` = 1
               AND (`starts_at` IS NULL OR `starts_at` <= NOW())
               AND (`ends_at`   IS NULL OR `ends_at`   >= NOW())
               AND (`max_uses`  IS NULL OR `used_count` < `max_uses`)
             LIMIT 1",
            [strtoupper(trim($code))]
        );

        if (!$coupon) {
            throw new \RuntimeException('Mã giảm giá không hợp lệ hoặc đã hết hạn.');
        }

        if ($coupon['min_order_value'] && $subtotal < $coupon['min_order_value']) {
            $min = number_format($coupon['min_order_value'], 0, ',', '.') . 'đ';
            throw new \RuntimeException("Đơn hàng tối thiểu {$min} để áp dụng mã này.");
        }

        $discount = $this->calcDiscount($coupon, $subtotal);

        // Lưu coupon vào session
        Session::set('cart_coupon', [
            'id'    => $coupon['id'],
            'code'  => $coupon['code'],
            'name'  => $coupon['name'],
            'type'  => $coupon['type'],
            'value' => $coupon['value'],
        ]);

        return [
            'coupon'          => $coupon,
            'discount_amount' => $discount,
        ];
    }

    /**
     * Xóa coupon khỏi giỏ.
     */
    public function removeCoupon(): void
    {
        Session::delete('cart_coupon');
    }

    // ── Private Utilities ─────────────────────────────────────────────────────

    private function countItems(int $cartId): int
    {
        return (int)$this->rawColumn(
            "SELECT COALESCE(SUM(quantity), 0) FROM `cart_items` WHERE `cart_id` = ?",
            [$cartId]
        );
    }

    private function touchCart(int $cartId): void
    {
        $this->db->execute(
            "UPDATE `carts` SET `updated_at` = NOW() WHERE `id` = ?",
            [$cartId]
        );
    }

    private function calcDiscount(array $coupon, int $subtotal): int
    {
        if ($coupon['type'] === 'percent') {
            $discount = (int)round($subtotal * $coupon['value'] / 100);
            if (!empty($coupon['max_discount'])) {
                $discount = min($discount, (int)$coupon['max_discount']);
            }
        } else {
            $discount = (int)$coupon['value'];
        }
        return min($discount, $subtotal);
    }
}