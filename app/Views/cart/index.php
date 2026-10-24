<?php
/**
 * cart/index.php — Trang giỏ hàng
 * Variables: $title, $description, $cart (array từ Cart::getCart())
 */
$items    = $cart['items']           ?? [];
$subtotal = $cart['subtotal']        ?? 0;
$discount = $cart['discount_amount'] ?? 0;
$total    = $cart['total']           ?? 0;
$coupon   = $cart['coupon']          ?? null;
$count    = $cart['item_count']      ?? 0;
?>
<div class="cart-page">
    <div class="container">
        <!-- Breadcrumb -->
        <nav class="breadcrumb" aria-label="Breadcrumb">
            <a href="<?= base_url('/') ?>">Trang chủ</a>
            <span class="breadcrumb__sep">›</span>
            <span aria-current="page">Giỏ hàng</span>
        </nav>

        <h1 class="cart-page__title">
            Giỏ hàng
            <?php if ($count > 0): ?>
            <span class="cart-page__count"><?= $count ?> sản phẩm</span>
            <?php endif; ?>
        </h1>

        <?php if (empty($items)): ?>
        <!-- Empty state -->
        <div class="cart-empty">
            <div class="cart-empty__icon" aria-hidden="true">🛒</div>
            <h2 class="cart-empty__title">Giỏ hàng trống</h2>
            <p class="cart-empty__text">Bạn chưa thêm sản phẩm nào vào giỏ hàng.</p>
            <a href="<?= base_url('/shop') ?>" class="btn btn--primary btn--lg" id="cart-go-shop">
                Khám phá sản phẩm
            </a>
        </div>
        <?php else: ?>

        <div class="cart-layout">
            <!-- Items -->
            <div class="cart-items" id="cart-items-list">
                <?php foreach ($items as $item): ?>
                <div class="cart-item" id="cart-item-<?= $item['id'] ?>" data-item-id="<?= $item['id'] ?>">
                    <a href="<?= base_url('/product/' . e($item['product_slug'])) ?>" class="cart-item__img-link">
                        <?php if ($item['thumbnail']): ?>
                        <img src="<?= e($item['thumbnail']) ?>"
                             alt="<?= e($item['product_name']) ?>"
                             class="cart-item__img"
                             width="100" height="100" loading="lazy">
                        <?php else: ?>
                        <div class="cart-item__img-placeholder" aria-hidden="true">📦</div>
                        <?php endif; ?>
                    </a>

                    <div class="cart-item__info">
                        <a href="<?= base_url('/product/' . e($item['product_slug'])) ?>"
                           class="cart-item__name">
                            <?= e($item['product_name']) ?>
                        </a>
                        <?php if ($item['variant_info']): ?>
                        <p class="cart-item__variant"><?= e($item['variant_info']) ?></p>
                        <?php endif; ?>

                        <div class="cart-item__price-mob">
                            <?php if ($item['sale_price']): ?>
                            <span class="price-sale"><?= format_price($item['sale_price']) ?></span>
                            <span class="price-orig price-orig--sm"><?= format_price($item['price']) ?></span>
                            <?php else: ?>
                            <span class="price-sale"><?= format_price($item['price']) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Quantity -->
                    <div class="cart-item__qty">
                        <button class="qty-btn" aria-label="Giảm"
                                onclick="cartPage.changeQty(<?= $item['id'] ?>, -1)">−</button>
                        <input type="number"
                               class="qty-input"
                               id="qty-<?= $item['id'] ?>"
                               value="<?= $item['quantity'] ?>"
                               min="1" max="<?= $item['stock_quantity'] ?>"
                               onchange="cartPage.setQty(<?= $item['id'] ?>, this.value)"
                               aria-label="Số lượng">
                        <button class="qty-btn" aria-label="Tăng"
                                onclick="cartPage.changeQty(<?= $item['id'] ?>, 1)">+</button>
                    </div>

                    <!-- Price -->
                    <div class="cart-item__price">
                        <?php if ($item['sale_price']): ?>
                        <span class="price-sale"><?= format_price($item['sale_price']) ?></span>
                        <span class="price-orig"><?= format_price($item['price']) ?></span>
                        <?php else: ?>
                        <span class="price-sale"><?= format_price($item['price']) ?></span>
                        <?php endif; ?>
                    </div>

                    <!-- Subtotal -->
                    <div class="cart-item__subtotal" id="subtotal-<?= $item['id'] ?>">
                        <?= format_price((int)$item['effective_price'] * (int)$item['quantity']) ?>
                    </div>

                    <!-- Remove -->
                    <button class="cart-item__remove" aria-label="Xóa sản phẩm"
                            onclick="cartPage.remove(<?= $item['id'] ?>, this)">
                        ✕
                    </button>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Summary -->
            <aside class="cart-summary" id="cart-summary">
                <h2 class="cart-summary__title">Tóm tắt đơn hàng</h2>

                <!-- Coupon -->
                <div class="cart-coupon">
                    <?php if ($coupon): ?>
                    <div class="cart-coupon__applied" id="coupon-applied">
                        <span class="cart-coupon__code">🎟 <?= e($coupon['code']) ?></span>
                        <button class="cart-coupon__remove" id="remove-coupon" onclick="cartPage.removeCoupon()">
                            Xóa
                        </button>
                    </div>
                    <?php else: ?>
                    <div class="cart-coupon__input-row" id="coupon-input-row">
                        <input type="text" id="coupon-input" class="form-control"
                               placeholder="Nhập mã giảm giá" autocomplete="off">
                        <button class="btn btn--outline btn--sm" id="apply-coupon"
                                onclick="cartPage.applyCoupon()">
                            Áp dụng
                        </button>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Totals -->
                <div class="cart-totals">
                    <div class="cart-total-row">
                        <span>Tạm tính</span>
                        <span id="cart-subtotal"><?= format_price($subtotal) ?></span>
                    </div>
                    <?php if ($discount > 0): ?>
                    <div class="cart-total-row cart-total-row--discount" id="discount-row">
                        <span>Giảm giá</span>
                        <span id="cart-discount">−<?= format_price($discount) ?></span>
                    </div>
                    <?php endif; ?>
                    <div class="cart-total-row">
                        <span>Phí vận chuyển</span>
                        <span class="cart-total-ship">Tính khi thanh toán</span>
                    </div>
                    <div class="cart-total-row cart-total-row--final">
                        <span>Tổng cộng</span>
                        <span id="cart-total"><?= format_price($total) ?></span>
                    </div>
                </div>

                <a href="<?= base_url('/checkout') ?>" class="btn btn--primary btn--full btn--lg" id="btn-checkout">
                    Tiến hành thanh toán →
                </a>
                <a href="<?= base_url('/shop') ?>" class="cart-summary__continue" id="continue-shopping">
                    ← Tiếp tục mua sắm
                </a>
            </aside>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
const cartPage = {
    async changeQty(itemId, delta) {
        const input = document.getElementById('qty-' + itemId);
        if (!input) return;
        const newQty = parseInt(input.value) + delta;
        if (newQty < 1) {
            this.remove(itemId);
            return;
        }
        input.value = newQty;
        await this.updateItem(itemId, newQty);
    },

    async setQty(itemId, val) {
        const qty = parseInt(val);
        if (isNaN(qty) || qty < 1) {
            await this.remove(itemId);
        } else {
            await this.updateItem(itemId, qty);
        }
    },

    async updateItem(itemId, quantity) {
        try {
            await Cart.update(itemId, quantity);
            // Reload page để cập nhật totals
            window.location.reload();
        } catch(e) {}
    },

    async remove(itemId, btn) {
        const row = document.getElementById('cart-item-' + itemId);
        if (row) row.style.opacity = '.4';
        try {
            await Cart.remove(itemId, btn);
            window.location.reload();
        } catch(e) {
            if (row) row.style.opacity = '1';
        }
    },

    async applyCoupon() {
        const code = document.getElementById('coupon-input')?.value?.trim();
        if (!code) return;
        try {
            await Cart.applyCoupon(code);
            window.location.reload();
        } catch(e) {}
    },

    async removeCoupon() {
        try {
            await http.delete(window.SPORTZONE.baseUrl + '/api/v1/cart/coupon');
            window.location.reload();
        } catch(e) {
            Toast.error('Không thể xóa mã giảm giá.');
        }
    }
};
</script>