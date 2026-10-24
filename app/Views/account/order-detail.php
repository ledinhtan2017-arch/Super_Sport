<?php
/**
 * account/order-detail.php — Variables: $order
 */
$statusColors  = ['pending'=>'warning','confirmed'=>'info','processing'=>'primary','shipping'=>'accent','delivered'=>'success','cancelled'=>'danger','refunded'=>'muted'];
$paymentColors = ['pending'=>'warning','paid'=>'success','failed'=>'danger','refunded'=>'muted'];
?>
<div class="account-page">
  <div class="container">
    <div class="account-grid">
      <?php include __DIR__ . '/../partials/account-nav.php'; ?>
      <div class="account-content">
        <div class="account-back"><a href="<?= base_url('/account/orders') ?>" class="btn btn--ghost btn--sm">← Đơn hàng của tôi</a></div>
        <h1 class="account-content__title">Đơn hàng #<?= e($order['order_number']) ?></h1>

        <div class="account-card">
          <div class="order-detail-meta">
            <div class="order-detail-meta__item">
              <span class="order-detail-meta__label">Ngày đặt</span>
              <span><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></span>
            </div>
            <div class="order-detail-meta__item">
              <span class="order-detail-meta__label">Trạng thái</span>
              <span class="badge badge--<?= $statusColors[$order['status']] ?? 'muted' ?>"><?= \App\Models\Order::statusLabel($order['status']) ?></span>
            </div>
            <div class="order-detail-meta__item">
              <span class="order-detail-meta__label">Thanh toán</span>
              <span class="badge badge--<?= $paymentColors[$order['payment_status']] ?? 'muted' ?>"><?= \App\Models\Order::paymentLabel($order['payment_status']) ?></span>
            </div>
          </div>
        </div>

        <!-- Items -->
        <div class="account-card account-card--no-pad">
          <div class="account-card__header"><h2 class="account-card__title">Sản phẩm</h2></div>
          <div class="table-wrap">
          <table class="account-table">
            <thead><tr><th>Sản phẩm</th><th>Đơn giá</th><th>SL</th><th>Thành tiền</th></tr></thead>
            <tbody>
            <?php foreach ($order['items'] as $item): ?>
            <tr>
              <td>
                <a href="<?= base_url('/product/'.e($item['product_slug'] ?? '')) ?>" class="table-link"><?= e($item['product_name']) ?></a>
                <?php if (!empty($item['variant_sku'])): ?><br><code class="text-sm"><?= e($item['variant_sku']) ?></code><?php endif; ?>
              </td>
              <td><?= format_price((float)$item['unit_price']) ?></td>
              <td><?= $item['quantity'] ?></td>
              <td><?= format_price((float)$item['subtotal']) ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
          </div>
          <div class="order-totals-sm">
            <div class="order-totals__row"><span>Tạm tính</span><span><?= format_price((float)$order['subtotal']) ?></span></div>
            <?php if ((float)$order['discount_amount'] > 0): ?>
            <div class="order-totals__row text-success"><span>Giảm giá</span><span>-<?= format_price((float)$order['discount_amount']) ?></span></div>
            <?php endif; ?>
            <div class="order-totals__row"><span>Phí vận chuyển</span><span><?= format_price((float)$order['shipping_fee']) ?></span></div>
            <div class="order-totals__row order-totals__row--total"><span>Tổng cộng</span><span><?= format_price((float)$order['total']) ?></span></div>
          </div>
        </div>

        <!-- Shipping -->
        <?php if ($order['shipping']): ?>
        <div class="account-card">
          <h2 class="account-card__title">Địa chỉ giao hàng</h2>
          <p>
            <?= e($order['shipping']['recipient_name']) ?> — <?= e($order['shipping']['phone']) ?><br>
            <?= e($order['shipping']['address_line']) ?>, <?= e($order['shipping']['ward']) ?>,
            <?= e($order['shipping']['district']) ?>, <?= e($order['shipping']['province']) ?>
          </p>
        </div>
        <?php endif; ?>

        <!-- Cancel -->
        <?php if (in_array($order['status'], ['pending','confirmed'])): ?>
        <div class="account-card">
          <button class="btn btn--danger" onclick="cancelOrder(<?= $order['id'] ?>)">🗑 Huỷ đơn hàng</button>
        </div>
        <script>
        async function cancelOrder(id) {
          if (!confirm('Bạn chắc chắn muốn huỷ đơn hàng này?')) return;
          const res = await Http.post(`/api/v1/orders/${id}/cancel`);
          if (res.ok) {
            Toast?.success?.('Huỷ đơn hàng thành công.');
            setTimeout(() => location.reload(), 1200);
          } else {
            Toast?.error?.(res.data?.message || 'Không thể huỷ đơn hàng.');
          }
        }
        </script>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>