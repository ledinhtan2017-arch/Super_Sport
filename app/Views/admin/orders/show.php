<?php
/**
 * admin/orders/show.php
 * Variables: $order (with items, shipping, history)
 */
$statusColors  = ['pending'=>'warning','confirmed'=>'info','processing'=>'primary','shipping'=>'accent','delivered'=>'success','cancelled'=>'danger','refunded'=>'muted'];
$paymentColors = ['pending'=>'warning','paid'=>'success','failed'=>'danger','refunded'=>'muted'];
?>
<div class="admin-back-link">
    <a href="<?= base_url('/admin/orders') ?>" class="btn btn--ghost">← Danh sách đơn hàng</a>
</div>

<div class="order-detail-grid">

    <!-- Left Column -->
    <div class="order-detail-main">

        <!-- Header Info -->
        <div class="admin-table-card">
            <div class="admin-table-card__header">
                <h2 class="admin-table-card__title"><?= e($order['order_number']) ?></h2>
                <div class="flex-gap">
                    <span class="badge badge--<?= $statusColors[$order['status']] ?? 'muted' ?> badge--lg"><?= \App\Models\Order::statusLabel($order['status']) ?></span>
                    <span class="badge badge--<?= $paymentColors[$order['payment_status']] ?? 'muted' ?> badge--lg"><?= \App\Models\Order::paymentLabel($order['payment_status']) ?></span>
                </div>
            </div>
            <div class="order-meta-grid">
                <div class="order-meta-item"><span class="order-meta-label">Ngày tạo</span><span><?= date('d/m/Y H:i:s', strtotime($order['created_at'])) ?></span></div>
                <div class="order-meta-item"><span class="order-meta-label">Thanh toán</span><span><?= $order['payment_method'] === 'cod' ? 'COD (Tiền mặt khi nhận)' : e(strtoupper($order['payment_method'])) ?></span></div>
                <?php if ($order['coupon_code']): ?>
                <div class="order-meta-item"><span class="order-meta-label">Mã giảm giá</span><span><code><?= e($order['coupon_code']) ?></code></span></div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Items -->
        <div class="admin-table-card">
            <div class="admin-table-card__header">
                <h3 class="admin-table-card__title">Sản phẩm đặt mua</h3>
            </div>
            <div class="table-wrap">
            <table class="admin-table">
                <thead><tr><th>Sản phẩm</th><th>Biến thể</th><th>Đơn giá</th><th>SL</th><th>Thành tiền</th></tr></thead>
                <tbody>
                <?php foreach ($order['items'] as $item): ?>
                <tr>
                    <td>
                        <a href="<?= base_url('/product/' . e($item['product_slug'] ?? '')) ?>" class="admin-link" target="_blank">
                            <?= e($item['product_name']) ?>
                        </a>
                    </td>
                    <td class="text-muted text-sm"><?= e($item['variant_info'] ?? '') ?><br><code class="text-xs"><?= e($item['variant_sku'] ?? '') ?></code></td>
                    <td><?= number_format((float)$item['unit_price']) ?>đ</td>
                    <td><?= $item['quantity'] ?></td>
                    <td><?= number_format((float)$item['subtotal']) ?>đ</td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            </div>
            <div class="order-summary">
                <div class="order-summary-row"><span>Tạm tính</span><span><?= number_format((float)$order['subtotal']) ?>đ</span></div>
                <?php if ((float)$order['discount_amount'] > 0): ?>
                <div class="order-summary-row text-success"><span>Giảm giá</span><span>-<?= number_format((float)$order['discount_amount']) ?>đ</span></div>
                <?php endif; ?>
                <div class="order-summary-row"><span>Phí giao hàng</span><span><?= number_format((float)$order['shipping_fee']) ?>đ</span></div>
                <div class="order-summary-row order-summary-row--total"><span>Tổng cộng</span><span><?= number_format((float)$order['total']) ?>đ</span></div>
            </div>
        </div>

        <!-- Status History -->
        <div class="admin-table-card">
            <div class="admin-table-card__header"><h3 class="admin-table-card__title">Lịch sử trạng thái</h3></div>
            <div class="status-timeline">
                <?php foreach ($order['history'] as $h): ?>
                <div class="status-timeline__item">
                    <div class="status-timeline__dot"></div>
                    <div class="status-timeline__content">
                        <div class="status-timeline__status">
                            <?php if ($h['old_status']): ?>
                            <span class="badge badge--muted"><?= \App\Models\Order::statusLabel($h['old_status']) ?></span>
                            → <?php endif; ?>
                            <span class="badge badge--<?= $statusColors[$h['new_status']] ?? 'muted' ?>"><?= \App\Models\Order::statusLabel($h['new_status']) ?></span>
                        </div>
                        <?php if ($h['note']): ?><div class="text-muted text-sm"><?= e($h['note']) ?></div><?php endif; ?>
                        <div class="text-muted text-xs"><?= date('d/m/Y H:i', strtotime($h['created_at'])) ?> <?= $h['changed_by_name'] ? '· ' . e($h['changed_by_name']) : '' ?></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Right Column -->
    <div class="order-detail-sidebar">

        <!-- Update Status -->
        <div class="admin-table-card">
            <div class="admin-table-card__header"><h3 class="admin-table-card__title">Cập nhật trạng thái</h3></div>
            <form method="POST" action="<?= base_url('/admin/orders/'.$order['id'].'/status') ?>" class="admin-form">
                <?= csrf_field() ?>
                <div class="form-group">
                    <label class="form-label">Trạng thái mới</label>
                    <select name="status" class="form-control" id="status-select">
                        <?php foreach (['pending','confirmed','processing','shipping','delivered','cancelled','refunded'] as $s): ?>
                        <option value="<?= $s ?>" <?= $order['status'] === $s ? 'selected' : '' ?>><?= \App\Models\Order::statusLabel($s) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Ghi chú</label>
                    <textarea name="note" class="form-control" rows="3" placeholder="Lý do cập nhật..."></textarea>
                </div>
                <button type="submit" class="btn btn--primary btn--block">Cập nhật</button>
            </form>
        </div>

        <!-- Customer Info -->
        <div class="admin-table-card">
            <div class="admin-table-card__header"><h3 class="admin-table-card__title">Thông tin khách hàng</h3></div>
            <div class="info-list">
                <div class="info-list__item"><span class="info-list__label">Tên</span><span><?= e($order['customer_name'] ?? '—') ?></span></div>
                <div class="info-list__item"><span class="info-list__label">Email</span><span><?= e($order['customer_email'] ?? '—') ?></span></div>
                <div class="info-list__item"><span class="info-list__label">SĐT</span><span><?= e($order['customer_phone'] ?? '—') ?></span></div>
            </div>
        </div>

        <!-- Shipping Address -->
        <?php if ($order['shipping']): ?>
        <div class="admin-table-card">
            <div class="admin-table-card__header"><h3 class="admin-table-card__title">Địa chỉ giao hàng</h3></div>
            <div class="info-list">
                <div class="info-list__item"><span class="info-list__label">Người nhận</span><span><?= e($order['shipping']['recipient_name']) ?></span></div>
                <div class="info-list__item"><span class="info-list__label">SĐT</span><span><?= e($order['shipping']['phone']) ?></span></div>
                <div class="info-list__item"><span class="info-list__label">Địa chỉ</span><span><?= e($order['shipping']['address_line']) ?></span></div>
                <div class="info-list__item"><span class="info-list__label">Phường/Xã</span><span><?= e($order['shipping']['ward']) ?></span></div>
                <div class="info-list__item"><span class="info-list__label">Quận/Huyện</span><span><?= e($order['shipping']['district']) ?></span></div>
                <div class="info-list__item"><span class="info-list__label">Tỉnh/TP</span><span><?= e($order['shipping']['province']) ?></span></div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
