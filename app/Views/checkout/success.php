<?php /** checkout/success.php — Variables: $order */ ?>
<div class="checkout-success">
  <div class="container">
    <div class="success-card">
      <div class="success-card__icon">🎉</div>
      <h1 class="success-card__title">Đặt hàng thành công!</h1>
      <p class="success-card__subtitle">Cảm ơn bạn đã mua sắm tại SPORTZONE.<br>Chúng tôi sẽ xử lý và giao hàng trong thời gian sớm nhất.</p>

      <div class="success-order-info">
        <div class="success-info-row">
          <span class="success-info-label">Mã đơn hàng</span>
          <span class="success-info-value"><strong><?= e($order['order_number']) ?></strong></span>
        </div>
        <div class="success-info-row">
          <span class="success-info-label">Tổng tiền</span>
          <span class="success-info-value"><?= format_price((float)$order['total']) ?></span>
        </div>
        <div class="success-info-row">
          <span class="success-info-label">Thanh toán</span>
          <span class="success-info-value">
            <?= $order['payment_method'] === 'cod' ? 'COD — Tiền mặt khi nhận' : e(strtoupper($order['payment_method'])) ?>
          </span>
        </div>
        <div class="success-info-row">
          <span class="success-info-label">Trạng thái</span>
          <span class="success-info-value"><span class="badge badge--warning"><?= \App\Models\Order::statusLabel($order['status']) ?></span></span>
        </div>
      </div>

      <div class="success-actions">
        <a href="<?= base_url('/account/orders') ?>" class="btn btn--primary">📋 Theo dõi đơn hàng</a>
        <a href="<?= base_url('/shop') ?>" class="btn btn--outline">🛍️ Tiếp tục mua sắm</a>
      </div>
    </div>
  </div>
</div>