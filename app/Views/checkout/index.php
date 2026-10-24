<?php
/**
 * checkout/index.php
 * Variables: $cart, $addresses
 */
$items    = $cart['items']    ?? [];
$subtotal = (float)($cart['subtotal']        ?? 0);
$discount = (float)($cart['discount_amount'] ?? 0);
$shipping = $subtotal >= 500000 ? 0 : 30000;
$total    = $subtotal - $discount + $shipping;
?>
<div class="checkout-page">
  <div class="container">
    <nav class="breadcrumb">
      <a href="<?= base_url('/') ?>">Trang chủ</a>
      <span class="breadcrumb__sep">›</span>
      <a href="<?= base_url('/cart') ?>">Giỏ hàng</a>
      <span class="breadcrumb__sep">›</span>
      <span>Thanh toán</span>
    </nav>

    <div class="checkout-grid">
      <!-- LEFT: Form -->
      <div class="checkout-form-col">
        <form id="checkout-form" class="checkout-form">
          <?= csrf_field() ?>

          <div class="checkout-section">
            <h2 class="checkout-section__title">📦 Địa chỉ giao hàng</h2>

            <?php if (!empty($addresses)): ?>
            <div class="saved-addresses">
              <div class="form-group">
                <label class="form-label">Chọn địa chỉ đã lưu</label>
                <select id="saved-addr-select" class="form-control" onchange="fillAddress(this)">
                  <option value="">— Nhập địa chỉ mới —</option>
                  <?php foreach ($addresses as $addr): ?>
                  <option value="<?= htmlspecialchars(json_encode($addr), ENT_QUOTES) ?>">
                    <?= e($addr['recipient_name']) ?> — <?= e($addr['address_line']) ?>, <?= e($addr['district']) ?>, <?= e($addr['province']) ?>
                  </option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
            <?php endif; ?>

            <div class="form-grid form-grid--2" id="shipping-fields">
              <div class="form-group">
                <label class="form-label" for="recipient_name">Họ tên người nhận *</label>
                <input type="text" name="recipient_name" id="recipient_name" class="form-control" required>
              </div>
              <div class="form-group">
                <label class="form-label" for="phone">Số điện thoại *</label>
                <input type="tel" name="phone" id="phone" class="form-control" required pattern="[0-9]{10,11}">
              </div>
              <div class="form-group form-group--full">
                <label class="form-label" for="address_line">Địa chỉ chi tiết *</label>
                <input type="text" name="address_line" id="address_line" class="form-control" required placeholder="Số nhà, tên đường, phường/xã...">
              </div>
              <div class="form-group">
                <label class="form-label" for="district">Quận/Huyện *</label>
                <input type="text" name="district" id="district" class="form-control" required>
              </div>
              <div class="form-group">
                <label class="form-label" for="province">Tỉnh/Thành phố *</label>
                <input type="text" name="province" id="province" class="form-control" required>
              </div>
              <div class="form-group form-group--full">
                <label class="form-label" for="note">Ghi chú</label>
                <textarea name="note" id="note" class="form-control" rows="2" placeholder="Yêu cầu đặc biệt..."></textarea>
              </div>
            </div>
          </div>

          <div class="checkout-section">
            <h2 class="checkout-section__title">💳 Phương thức thanh toán</h2>
            <div class="payment-methods">
              <label class="payment-method is-selected" id="pm-cod">
                <input type="radio" name="payment_method" value="cod" checked>
                <span class="payment-method__icon">💵</span>
                <div>
                  <strong>COD — Tiền mặt khi nhận hàng</strong>
                  <p>Thanh toán khi nhận hàng</p>
                </div>
              </label>
              <label class="payment-method" id="pm-bank">
                <input type="radio" name="payment_method" value="bank_transfer">
                <span class="payment-method__icon">🏦</span>
                <div>
                  <strong>Chuyển khoản ngân hàng</strong>
                  <p>Chuyển khoản trước khi giao</p>
                </div>
              </label>
              <label class="payment-method" id="pm-momo">
                <input type="radio" name="payment_method" value="momo">
                <span class="payment-method__icon">📱</span>
                <div>
                  <strong>MoMo</strong>
                  <p>Thanh toán qua ví điện tử</p>
                </div>
              </label>
            </div>
          </div>
        </form>
      </div>

      <!-- RIGHT: Order Summary -->
      <div class="checkout-summary-col">
        <div class="order-summary-card">
          <h2 class="order-summary-card__title">Đơn hàng</h2>

          <div class="order-items">
            <?php foreach ($items as $item): ?>
            <div class="order-item">
              <img src="<?= e($item['image_url'] ?? '') ?: base_url('/assets/img/placeholder.svg') ?>"
                   alt="<?= e($item['product_name']) ?>" class="order-item__img" loading="lazy">
              <div class="order-item__info">
                <div class="order-item__name"><?= e($item['product_name']) ?></div>
                <?php if (!empty($item['variant_info'])): ?>
                <div class="order-item__variant"><?= e($item['variant_info']) ?></div>
                <?php endif; ?>
                <div class="order-item__price">
                  <?= format_price($item['price']) ?> × <?= $item['quantity'] ?>
                </div>
              </div>
              <div class="order-item__total"><?= format_price($item['price'] * $item['quantity']) ?></div>
            </div>
            <?php endforeach; ?>
          </div>

          <div class="order-totals">
            <div class="order-totals__row">
              <span>Tạm tính</span>
              <span><?= format_price($subtotal) ?></span>
            </div>
            <?php if ($discount > 0): ?>
            <div class="order-totals__row order-totals__row--discount">
              <span>Giảm giá (<?= $cart['coupon']['code'] ?? '' ?>)</span>
              <span>-<?= format_price($discount) ?></span>
            </div>
            <?php endif; ?>
            <div class="order-totals__row">
              <span>Phí vận chuyển</span>
              <span><?= $shipping === 0 ? '<span class="text-success">Miễn phí</span>' : format_price($shipping) ?></span>
            </div>
            <div class="order-totals__row order-totals__row--total">
              <span>Tổng cộng</span>
              <span id="checkout-total"><?= format_price($total) ?></span>
            </div>
          </div>

          <button type="button" id="btn-place-order" class="btn btn--primary btn--lg btn--full" onclick="placeOrder()">
            🛒 Đặt hàng ngay
          </button>

          <div class="checkout-trust">
            <span>🔒 Thanh toán an toàn</span>
            <span>🚀 Giao hàng toàn quốc</span>
            <span>↩️ Đổi trả 30 ngày</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
function fillAddress(select) {
  if (!select.value) return;
  const addr = JSON.parse(select.value);
  document.getElementById('recipient_name').value = addr.recipient_name || '';
  document.getElementById('phone').value           = addr.phone || '';
  document.getElementById('address_line').value    = addr.address_line || '';
  document.getElementById('district').value        = addr.district || '';
  document.getElementById('province').value        = addr.province || '';
}

document.querySelectorAll('.payment-method input').forEach(radio => {
  radio.addEventListener('change', () => {
    document.querySelectorAll('.payment-method').forEach(el => el.classList.remove('is-selected'));
    radio.closest('.payment-method').classList.add('is-selected');
  });
});

async function placeOrder() {
  const form = document.getElementById('checkout-form');
  if (!form.reportValidity()) return;

  const btn = document.getElementById('btn-place-order');
  btn.disabled = true;
  btn.textContent = 'Đang xử lý...';

  const data = {
    recipient_name:  form.recipient_name.value,
    phone:           form.phone.value,
    address_line:    form.address_line.value,
    district:        form.district.value,
    province:        form.province.value,
    note:            form.note?.value || '',
    payment_method:  form.querySelector('input[name="payment_method"]:checked').value,
  };

  try {
    const res = await Http.post('/api/v1/orders', data);
    if (res.ok && res.data?.data?.redirect) {
      Toast?.success?.('Đặt hàng thành công! 🎉');
      setTimeout(() => window.location.href = res.data.data.redirect, 800);
    } else {
      Toast?.error?.(res.data?.message || 'Đặt hàng thất bại. Vui lòng thử lại.');
      btn.disabled = false;
      btn.textContent = '🛒 Đặt hàng ngay';
    }
  } catch {
    Toast?.error?.('Có lỗi xảy ra. Vui lòng thử lại.');
    btn.disabled = false;
    btn.textContent = '🛒 Đặt hàng ngay';
  }
}
</script>