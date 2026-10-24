<?php

declare(strict_types=1);

namespace App\Models;

final class Coupon extends BaseModel
{
    protected string $table    = 'coupons';
    protected array  $fillable = [
        'code', 'name', 'type', 'value', 'max_discount',
        'min_order_value', 'max_uses', 'starts_at', 'ends_at', 'is_active',
    ];

    public function allForAdmin(): array
    {
        return $this->raw(
            "SELECT * FROM coupons ORDER BY created_at DESC"
        );
    }

    public function findByCode(string $code): ?array
    {
        return $this->rawOne("SELECT * FROM coupons WHERE code = ? LIMIT 1", [strtoupper($code)]);
    }

    public function isValid(string $code, float $orderTotal): array
    {
        $coupon = $this->findByCode($code);
        if (!$coupon) return ['valid' => false, 'message' => 'Mã giảm giá không tồn tại.'];
        if (!$coupon['is_active']) return ['valid' => false, 'message' => 'Mã giảm giá đã hết hiệu lực.'];

        $now = date('Y-m-d H:i:s');
        if ($coupon['starts_at'] && $now < $coupon['starts_at']) {
            return ['valid' => false, 'message' => 'Mã giảm giá chưa có hiệu lực.'];
        }
        if ($coupon['ends_at'] && $now > $coupon['ends_at']) {
            return ['valid' => false, 'message' => 'Mã giảm giá đã hết hạn.'];
        }
        if ($coupon['max_uses'] && $coupon['used_count'] >= $coupon['max_uses']) {
            return ['valid' => false, 'message' => 'Mã giảm giá đã được sử dụng hết.'];
        }
        if ($coupon['min_order_value'] && $orderTotal < $coupon['min_order_value']) {
            return ['valid' => false, 'message' => 'Đơn hàng chưa đạt giá trị tối thiểu ' . number_format($coupon['min_order_value']) . 'đ.'];
        }

        $discount = $coupon['type'] === 'percent'
            ? ($orderTotal * $coupon['value'] / 100)
            : (float)$coupon['value'];

        if ($coupon['max_discount'] && $discount > $coupon['max_discount']) {
            $discount = (float)$coupon['max_discount'];
        }

        return ['valid' => true, 'coupon' => $coupon, 'discount' => $discount];
    }

    public function incrementUsed(int $id): void
    {
        $this->db->execute("UPDATE coupons SET used_count = used_count + 1 WHERE id = ?", [$id]);
    }

    public function createCoupon(array $data): int
    {
        $code = strtoupper(trim($data['code'] ?? ''));
        $this->db->execute(
            "INSERT INTO coupons (code, name, type, value, max_discount, min_order_value, max_uses, starts_at, ends_at, is_active)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [
                $code,
                $data['name']            ?? null,
                $data['type']            ?? 'percent',
                (float)($data['value']   ?? 0),
                $data['max_discount']    ?? null,
                $data['min_order_value'] ?? null,
                $data['max_uses']        ?? null,
                $data['starts_at']       ?? null,
                $data['ends_at']         ?? null,
                !empty($data['is_active']) ? 1 : 0,
            ]
        );
        return (int)$this->db->lastInsertId();
    }


    /**
     * Validate a coupon code for a given order subtotal.
     * Returns ['valid' => true, 'discount' => N, 'coupon' => [...]] or ['valid' => false, 'message' => '...']
     */
    public function validate(string $code, float $subtotal): array
    {
        $code   = strtoupper(trim($code));
        $coupon = $this->findByCode($code);

        if (!$coupon) {
            return ['valid' => false, 'message' => 'Mã giảm giá không tồn tại.'];
        }
        if (!(bool)$coupon['is_active']) {
            return ['valid' => false, 'message' => 'Mã giảm giá đã ngừng hoạt động.'];
        }
        if ($coupon['starts_at'] && strtotime($coupon['starts_at']) > time()) {
            return ['valid' => false, 'message' => 'Mã giảm giá chưa có hiệu lực.'];
        }
        if ($coupon['ends_at'] && strtotime($coupon['ends_at']) < time()) {
            return ['valid' => false, 'message' => 'Mã giảm giá đã hết hạn.'];
        }
        if ($coupon['max_uses'] !== null && (int)$coupon['used_count'] >= (int)$coupon['max_uses']) {
            return ['valid' => false, 'message' => 'Mã giảm giá đã hết lượt sử dụng.'];
        }
        if ((float)$coupon['min_order_value'] > 0 && $subtotal < (float)$coupon['min_order_value']) {
            return [
                'valid'   => false,
                'message' => 'Đơn hàng chưa đạt giá trị tối thiểu ' . number_format((float)$coupon['min_order_value']) . 'đ.',
            ];
        }

        // Calculate discount
        if ($coupon['type'] === 'percent') {
            $discount = $subtotal * (float)$coupon['value'] / 100;
            if ((float)$coupon['max_discount'] > 0) {
                $discount = min($discount, (float)$coupon['max_discount']);
            }
        } else {
            $discount = (float)$coupon['value'];
        }
        $discount = round(min($discount, $subtotal), 0);

        return ['valid' => true, 'discount' => $discount, 'coupon' => $coupon];
    }
}
