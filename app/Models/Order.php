<?php

declare(strict_types=1);

namespace App\Models;

final class Order extends BaseModel
{
    protected string $table    = 'orders';
    protected array  $fillable = [
        'user_id', 'order_number', 'subtotal', 'discount_amount',
        'shipping_fee', 'total', 'coupon_id', 'coupon_code',
        'payment_method', 'payment_status', 'status', 'note',
    ];

    public function adminList(int $page = 1, int $perPage = 20, array $filters = []): array
    {
        $page   = max(1, $page);
        $offset = ($page - 1) * $perPage;

        $conditions = [];
        $params     = [];

        if (!empty($filters['status'])) {
            $conditions[] = 'o.status = ?';
            $params[]     = $filters['status'];
        }
        if (!empty($filters['payment_status'])) {
            $conditions[] = 'o.payment_status = ?';
            $params[]     = $filters['payment_status'];
        }
        if (!empty($filters['search'])) {
            $like = '%' . $filters['search'] . '%';
            $conditions[] = '(o.order_number LIKE ? OR u.name LIKE ? OR u.email LIKE ?)';
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }

        $where = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';

        $total = (int)$this->rawColumn(
            "SELECT COUNT(*) FROM orders o LEFT JOIN users u ON u.id = o.user_id {$where}",
            $params
        );

        $data = $this->raw(
            "SELECT o.*, u.name AS customer_name, u.email AS customer_email
             FROM orders o
             LEFT JOIN users u ON u.id = o.user_id
             {$where}
             ORDER BY o.created_at DESC
             LIMIT ? OFFSET ?",
            array_merge($params, [$perPage, $offset])
        );

        return [
            'data'         => $data,
            'total'        => $total,
            'per_page'     => $perPage,
            'current_page' => $page,
            'last_page'    => $total > 0 ? (int)ceil($total / $perPage) : 1,
            'from'         => $total > 0 ? $offset + 1 : 0,
            'to'           => min($offset + $perPage, $total),
        ];
    }

    public function detail(int $id): ?array
    {
        $order = $this->rawOne(
            "SELECT o.*, u.name AS customer_name, u.email AS customer_email, u.phone AS customer_phone
             FROM orders o LEFT JOIN users u ON u.id = o.user_id
             WHERE o.id = ? LIMIT 1",
            [$id]
        );
        if (!$order) return null;

        $order['items'] = $this->raw(
            "SELECT oi.*, p.slug AS product_slug FROM order_items oi
             LEFT JOIN products p ON p.id = oi.product_id WHERE oi.order_id = ?",
            [$id]
        );
        $order['shipping'] = $this->rawOne("SELECT * FROM order_shipping WHERE order_id = ? LIMIT 1", [$id]);
        $order['history']  = $this->raw(
            "SELECT osh.*, u.name AS changed_by_name FROM order_status_history osh
             LEFT JOIN users u ON u.id = osh.changed_by WHERE osh.order_id = ? ORDER BY osh.created_at ASC",
            [$id]
        );
        return $order;
    }

    public function updateStatus(int $id, string $newStatus, ?int $adminId = null, string $note = ''): bool
    {
        $order = $this->find($id);
        if (!$order) return false;
        $this->db->execute("UPDATE orders SET status = ? WHERE id = ?", [$newStatus, $id]);
        $this->db->execute("INSERT INTO order_status_history (order_id, changed_by, old_status, new_status, note) VALUES (?,?,?,?,?)",
            [$id, $adminId, $order['status'], $newStatus, $note]
        );
        return true;
    }

    public function stats(): array
    {
        $today = date('Y-m-d');
        $month = date('Y-m');
        return [
            'total_orders'  => (int)$this->rawColumn("SELECT COUNT(*) FROM orders"),
            'pending'       => (int)$this->rawColumn("SELECT COUNT(*) FROM orders WHERE status = 'pending'"),
            'today_orders'  => (int)$this->rawColumn("SELECT COUNT(*) FROM orders WHERE DATE(created_at) = ?", [$today]),
            'month_revenue' => (float)($this->rawColumn("SELECT COALESCE(SUM(total),0) FROM orders WHERE payment_status='paid' AND DATE_FORMAT(created_at,'%Y-%m')=?", [$month]) ?? 0),
            'total_revenue' => (float)($this->rawColumn("SELECT COALESCE(SUM(total),0) FROM orders WHERE payment_status='paid'") ?? 0),
        ];
    }

    public static function statusLabel(string $status): string
    {
        return match($status) {
            'pending'    => 'Chờ xác nhận',
            'confirmed'  => 'Đã xác nhận',
            'processing' => 'Đang xử lý',
            'shipping'   => 'Đang giao',
            'delivered'  => 'Đã giao',
            'cancelled'  => 'Đã huỷ',
            'refunded'   => 'Đã hoàn tiền',
            default      => $status,
        };
    }

    public static function paymentLabel(string $status): string
    {
        return match($status) {
            'pending'  => 'Chưa TT',
            'paid'     => 'Đã TT',
            'failed'   => 'Thất bại',
            'refunded' => 'Hoàn tiền',
            default    => $status,
        };
    }

    // ─── Customer-Facing Queries ──────────────────────────────────────────────

    public function getUserOrders(int $userId, int $page = 1, int $perPage = 10): array
    {
        $page   = max(1, $page);
        $offset = ($page - 1) * $perPage;

        $total = (int)$this->db->fetchColumn(
            "SELECT COUNT(*) FROM orders WHERE user_id = ?",
            [$userId]
        );

        $data = $this->db->fetchAll(
            "SELECT o.id, o.order_number, o.status, o.payment_status,
                    o.total, o.created_at,
                    COUNT(oi.id) AS item_count
             FROM orders o
             LEFT JOIN order_items oi ON oi.order_id = o.id
             WHERE o.user_id = ?
             GROUP BY o.id
             ORDER BY o.created_at DESC
             LIMIT ? OFFSET ?",
            [$userId, $perPage, $offset]
        );

        return [
            'data'         => $data,
            'total'        => $total,
            'per_page'     => $perPage,
            'current_page' => $page,
            'last_page'    => $total > 0 ? (int)ceil($total / $perPage) : 1,
        ];
    }

    public function findByOrderNumber(string $orderNumber): ?array
    {
        $order = $this->db->fetchOne(
            "SELECT * FROM orders WHERE order_number = ? LIMIT 1",
            [$orderNumber]
        );

        if (!$order) return null;

        $order['items']   = $this->getOrderItems((int)$order['id']);
        $order['shipping'] = $this->db->fetchOne(
            "SELECT * FROM order_shipping WHERE order_id = ? LIMIT 1",
            [(int)$order['id']]
        );

        return $order;
    }

    private function getOrderItems(int $orderId): array
    {
        return $this->db->fetchAll(
            "SELECT oi.*, p.name AS product_name, p.slug AS product_slug,
                    pv.sku AS variant_sku
             FROM order_items oi
             LEFT JOIN product_variants pv ON pv.id = oi.variant_id
             LEFT JOIN products p ON p.id = pv.product_id
             WHERE oi.order_id = ?",
            [$orderId]
        );
    }

    public function getUserAddresses(int $userId): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM user_addresses WHERE user_id = ? ORDER BY is_default DESC, id DESC",
            [$userId]
        );
    }

    public function findAddress(int $id, int $userId): ?array
    {
        return $this->db->fetchOne(
            "SELECT * FROM user_addresses WHERE id = ? AND user_id = ? LIMIT 1",
            [$id, $userId]
        );
    }

    public function insertAddress(array $data): int
    {
        $this->db->execute(
            "INSERT INTO user_addresses (user_id, recipient_name, phone, address_line, ward, district, province, is_default)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
            [
                $data['user_id'],
                $data['recipient_name'] ?? '',
                $data['phone']         ?? '',
                $data['address_line']  ?? '',
                $data['ward']          ?? '',
                $data['district']      ?? '',
                $data['province']      ?? '',
                !empty($data['is_default']) ? 1 : 0,
            ]
        );
        return (int)$this->db->lastInsertId();
    }

    public function updateAddress(int $id, array $data): void
    {
        $this->db->execute(
            "UPDATE user_addresses SET recipient_name=?, phone=?, address_line=?, ward=?, district=?, province=?, is_default=?
             WHERE id = ?",
            [
                $data['recipient_name'] ?? '',
                $data['phone']          ?? '',
                $data['address_line']   ?? '',
                $data['ward']           ?? '',
                $data['district']       ?? '',
                $data['province']       ?? '',
                !empty($data['is_default']) ? 1 : 0,
                $id,
            ]
        );
    }

    public function clearDefaultAddress(int $userId): void
    {
        $this->db->execute(
            "UPDATE user_addresses SET is_default = 0 WHERE user_id = ?",
            [$userId]
        );
    }

    public function deleteAddress(int $id, int $userId): void
    {
        $this->db->execute(
            "DELETE FROM user_addresses WHERE id = ? AND user_id = ?",
            [$id, $userId]
        );
    }

    // ─── Label helpers ────────────────────────────────────────────────────────



    public function createOrder(array $orderData, array $items, array $shippingData): int
    {
        $this->db->execute(
            "INSERT INTO orders (user_id, order_number, status, payment_method, payment_status,
                                 subtotal, discount_amount, shipping_fee, total, coupon_code, note)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [
                $orderData['user_id'],
                $orderData['order_number'],
                $orderData['status'],
                $orderData['payment_method'],
                $orderData['payment_status'],
                $orderData['subtotal'],
                $orderData['discount_amount'],
                $orderData['shipping_fee'],
                $orderData['total'],
                $orderData['coupon_code'],
                $orderData['note'],
            ]
        );

        $orderId = (int)$this->db->lastInsertId();

        // Insert order items
        foreach ($items as $item) {
            $this->db->execute(
                "INSERT INTO order_items (order_id, variant_id, quantity, unit_price, subtotal)
                 VALUES (?, ?, ?, ?, ?)",
                [
                    $orderId,
                    $item['variant_id'],
                    $item['quantity'],
                    $item['price'],
                    $item['price'] * $item['quantity'],
                ]
            );

            // Decrease stock
            $this->db->execute(
                "UPDATE product_variants SET stock_quantity = GREATEST(0, stock_quantity - ?) WHERE id = ?",
                [$item['quantity'], $item['variant_id']]
            );
        }

        // Insert shipping info
        $this->db->execute(
            "INSERT INTO order_shipping (order_id, recipient_name, phone, address_line, ward, district, province)
             VALUES (?, ?, ?, ?, ?, ?, ?)",
            [
                $orderId,
                $shippingData['recipient_name'],
                $shippingData['phone'],
                $shippingData['address_line'],
                $shippingData['ward'],
                $shippingData['district'],
                $shippingData['province'],
            ]
        );

        // Insert initial status history
        $this->db->execute(
            "INSERT INTO order_status_history (order_id, old_status, new_status, changed_by, note)
             VALUES (?, NULL, 'pending', ?, 'Khách hàng đặt hàng.')",
            [$orderId, $orderData['user_id']]
        );

        return $orderId;
    }

    public function findWithDetails(int $id): ?array
    {
        $order = $this->find($id);
        if (!$order) return null;
        $order['items']   = $this->getOrderItems($id);
        $order['shipping'] = $this->db->fetchOne(
            "SELECT * FROM order_shipping WHERE order_id = ? LIMIT 1", [$id]
        );
        $order['status_history'] = $this->db->fetchAll(
            "SELECT osh.*, u.name AS changed_by_name FROM order_status_history osh
             LEFT JOIN users u ON u.id = osh.changed_by
             WHERE osh.order_id = ? ORDER BY osh.created_at ASC", [$id]
        );
        return $order;
    }
}
