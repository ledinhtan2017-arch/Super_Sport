<?php
/**
 * product/show.php
 * Variables: $title, $description, $product, $images, $variants, $related,
 *            $colorOptions, $sizeOptions, $minPrice, $salePrice, $inStock,
 *            $thumbnail, $breadcrumb
 */

$isOnSale  = $salePrice && $salePrice < $minPrice;
$discount  = $isOnSale ? discount_percent($minPrice, $salePrice) : 0;
$mainImage = $thumbnail ?? 'https://picsum.photos/seed/default/800/1000';
?>

<!-- Breadcrumb -->
<div class="container" style="padding-top: var(--space-4)">
    <nav class="breadcrumb" aria-label="Breadcrumb">
        <?php foreach ($breadcrumb as $i => $crumb): ?>
        <?php if ($crumb['url']): ?>
        <a href="<?= e($crumb['url']) ?>" class="breadcrumb__item"><?= e($crumb['label']) ?></a>
        <span class="breadcrumb__sep">›</span>
        <?php else: ?>
        <span class="breadcrumb__item breadcrumb__item--active"><?= e($crumb['label']) ?></span>
        <?php endif; ?>
        <?php endforeach; ?>
    </nav>
</div>

<!-- Product Detail -->
<section class="container product-detail">

    <!-- Gallery -->
    <div class="product-gallery" id="product-gallery">
        <div class="product-gallery__main">
            <img src="<?= e($mainImage) ?>"
                 alt="<?= e($product['name']) ?>"
                 class="product-gallery__main-img"
                 id="gallery-main-img"
                 width="800" height="1000">

            <?php if ($isOnSale): ?>
            <span class="product-gallery__badge badge--sale">-<?= $discount ?>%</span>
            <?php endif; ?>

            <?php if (!$inStock): ?>
            <div class="product-gallery__soldout-overlay" aria-hidden="true">Hết hàng</div>
            <?php endif; ?>
        </div>

        <?php if (count($images) > 1): ?>
        <div class="product-gallery__thumbs" role="list">
            <?php foreach ($images as $i => $img): ?>
            <button class="product-gallery__thumb <?= $i === 0 ? 'product-gallery__thumb--active' : '' ?>"
                    data-src="<?= e($img['image_url']) ?>"
                    role="listitem"
                    aria-label="Ảnh <?= $i + 1 ?>">
                <img src="<?= e($img['image_url']) ?>"
                     alt="<?= e($product['name']) ?> ảnh <?= $i + 1 ?>"
                     loading="lazy" width="80" height="100">
            </button>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- Info -->
    <div class="product-info" id="product-info">

        <!-- Brand -->
        <a href="<?= base_url('/shop?brand=' . e($product['brand_slug'])) ?>"
           class="product-info__brand"><?= e($product['brand_name']) ?></a>

        <!-- Title -->
        <h1 class="product-info__title"><?= e($product['name']) ?></h1>

        <!-- Category -->
        <a href="<?= base_url('/shop?category=' . e($product['category_slug'])) ?>"
           class="product-info__category"><?= e($product['category_name']) ?></a>

        <!-- Price -->
        <div class="product-info__price-block">
            <?php if ($isOnSale): ?>
            <span class="product-info__price product-info__price--sale"><?= format_price((int)$salePrice) ?></span>
            <span class="product-info__price-original"><?= format_price((int)$minPrice) ?></span>
            <span class="product-info__discount-badge">Tiết kiệm <?= $discount ?>%</span>
            <?php else: ?>
            <span class="product-info__price"><?= format_price((int)$minPrice) ?></span>
            <?php endif; ?>
        </div>

        <!-- Stock status -->
        <div class="product-info__stock <?= $inStock ? 'product-info__stock--in' : 'product-info__stock--out' ?>">
            <?php if ($inStock): ?>
            <span class="stock-dot stock-dot--in"></span> Còn hàng
            <?php else: ?>
            <span class="stock-dot stock-dot--out"></span> Hết hàng
            <?php endif; ?>
        </div>

        <!-- Add to Cart Form -->
        <form class="product-add-form" id="add-to-cart-form"
              data-product-id="<?= e($product['id']) ?>"
              novalidate>

            <!-- Color Options -->
            <?php if (!empty($colorOptions)): ?>
            <div class="variant-group" id="color-group">
                <p class="variant-group__label">
                    Màu sắc: <span class="variant-group__selected" id="selected-color"></span>
                </p>
                <div class="variant-swatches">
                    <?php foreach ($colorOptions as $color): ?>
                    <button type="button"
                            class="color-swatch"
                            data-value="<?= e($color['value']) ?>"
                            data-label="<?= e($color['label'] ?? $color['value']) ?>"
                            style="--swatch-color: <?= e($color['hex_code'] ?? '#ccc') ?>"
                            title="<?= e($color['value']) ?>"
                            aria-label="Màu <?= e($color['value']) ?>">
                    </button>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Size Options -->
            <?php if (!empty($sizeOptions)): ?>
            <div class="variant-group" id="size-group">
                <p class="variant-group__label">
                    Size: <span class="variant-group__selected" id="selected-size"></span>
                </p>
                <div class="size-options">
                    <?php foreach ($sizeOptions as $size): ?>
                    <button type="button"
                            class="size-btn"
                            data-value="<?= e($size['value']) ?>">
                        <?= e($size['value']) ?>
                    </button>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Quantity -->
            <div class="qty-row">
                <label class="qty-row__label" for="qty-input">Số lượng</label>
                <div class="qty-control">
                    <button type="button" class="qty-control__btn" id="qty-minus" aria-label="Giảm">−</button>
                    <input type="number" id="qty-input" name="quantity"
                           class="qty-control__input" value="1" min="1" max="99" readonly>
                    <button type="button" class="qty-control__btn" id="qty-plus" aria-label="Tăng">+</button>
                </div>
            </div>

            <!-- Submit Buttons -->
            <div class="product-info__cta">
                <?php if ($inStock): ?>
                <button type="submit" class="btn btn--primary btn--lg btn--full" id="add-to-cart-btn"
                        data-product-id="<?= e($product['id']) ?>">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/>
                        <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
                    </svg>
                    Thêm vào giỏ hàng
                </button>
                <?php else: ?>
                <button type="button" class="btn btn--outline btn--lg btn--full" disabled>Hết hàng</button>
                <?php endif; ?>
            </div>
        </form>

        <!-- Product Features -->
        <div class="product-features">
            <div class="product-feature">
                <span class="product-feature__icon">🚚</span>
                <span>Miễn phí vận chuyển đơn từ <?= format_price(config('app.shipping.free_threshold', 500000)) ?></span>
            </div>
            <div class="product-feature">
                <span class="product-feature__icon">↩️</span>
                <span>Đổi trả miễn phí trong 30 ngày</span>
            </div>
            <div class="product-feature">
                <span class="product-feature__icon">✅</span>
                <span>Sản phẩm chính hãng 100%</span>
            </div>
        </div>

        <!-- Description Tabs -->
        <div class="product-tabs" id="product-tabs">
            <div class="product-tabs__nav" role="tablist">
                <button class="product-tabs__tab product-tabs__tab--active"
                        role="tab" aria-selected="true" aria-controls="tab-desc" id="tab-btn-desc">
                    Mô tả
                </button>
                <button class="product-tabs__tab"
                        role="tab" aria-selected="false" aria-controls="tab-spec" id="tab-btn-spec">
                    Thông số
                </button>
            </div>

            <div class="product-tabs__panel" id="tab-desc" role="tabpanel" aria-labelledby="tab-btn-desc">
                <?php if (!empty($product['description'])): ?>
                <div class="product-desc"><?= nl2br(e($product['description'])) ?></div>
                <?php else: ?>
                <p class="product-desc product-desc--empty">Chưa có mô tả cho sản phẩm này.</p>
                <?php endif; ?>
            </div>

            <div class="product-tabs__panel product-tabs__panel--hidden" id="tab-spec" role="tabpanel" aria-labelledby="tab-btn-spec">
                <table class="spec-table">
                    <tbody>
                        <tr><th>Thương hiệu</th><td><?= e($product['brand_name']) ?></td></tr>
                        <tr><th>Danh mục</th><td><?= e($product['category_name']) ?></td></tr>
                        <?php if (!empty($sizeOptions)): ?>
                        <tr><th>Size có sẵn</th><td><?= e(implode(', ', array_column($sizeOptions, 'value'))) ?></td></tr>
                        <?php endif; ?>
                        <?php if (!empty($colorOptions)): ?>
                        <tr><th>Màu sắc</th><td><?= e(implode(', ', array_column($colorOptions, 'value'))) ?></td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div><!-- /.product-info -->

</section>

<!-- Related Products -->
<?php if (!empty($related)): ?>
<section class="section" aria-labelledby="related-heading" style="margin-top: var(--space-16)">
    <div class="container">
        <div class="section__header">
            <h2 class="section__title" id="related-heading">Sản phẩm liên quan</h2>
            <a href="<?= base_url('/shop?category=' . e($product['category_slug'])) ?>"
               class="section__see-all">Xem thêm →</a>
        </div>
        <div class="product-grid product-grid--4">
            <?php foreach ($related as $product): ?>
            <?php include BASE_PATH . '/app/Views/partials/product-card.php'; ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<script>
(function () {
    // Gallery thumbnails
    const mainImg = document.getElementById('gallery-main-img');
    document.querySelectorAll('.product-gallery__thumb').forEach(thumb => {
        thumb.addEventListener('click', function () {
            if (mainImg) mainImg.src = this.dataset.src;
            document.querySelectorAll('.product-gallery__thumb').forEach(t =>
                t.classList.remove('product-gallery__thumb--active'));
            this.classList.add('product-gallery__thumb--active');
        });
    });

    // Color swatches
    document.querySelectorAll('.color-swatch').forEach(swatch => {
        swatch.addEventListener('click', function () {
            document.querySelectorAll('.color-swatch').forEach(s =>
                s.classList.remove('color-swatch--active'));
            this.classList.add('color-swatch--active');
            const label = document.getElementById('selected-color');
            if (label) label.textContent = this.dataset.label || this.dataset.value;
        });
    });

    // Size buttons
    document.querySelectorAll('.size-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.size-btn').forEach(b =>
                b.classList.remove('size-btn--active'));
            this.classList.add('size-btn--active');
            const label = document.getElementById('selected-size');
            if (label) label.textContent = this.dataset.value;
        });
    });

    // Quantity control
    const qtyInput = document.getElementById('qty-input');
    document.getElementById('qty-minus')?.addEventListener('click', () => {
        const v = parseInt(qtyInput.value, 10);
        if (v > 1) qtyInput.value = v - 1;
    });
    document.getElementById('qty-plus')?.addEventListener('click', () => {
        const v = parseInt(qtyInput.value, 10);
        if (v < 99) qtyInput.value = v + 1;
    });

    // Tabs
    document.querySelectorAll('.product-tabs__tab').forEach(tab => {
        tab.addEventListener('click', function () {
            document.querySelectorAll('.product-tabs__tab').forEach(t => {
                t.classList.remove('product-tabs__tab--active');
                t.setAttribute('aria-selected', 'false');
            });
            document.querySelectorAll('.product-tabs__panel').forEach(p =>
                p.classList.add('product-tabs__panel--hidden'));
            this.classList.add('product-tabs__tab--active');
            this.setAttribute('aria-selected', 'true');
            const panel = document.getElementById(this.getAttribute('aria-controls'));
            panel?.classList.remove('product-tabs__panel--hidden');
        });
    });

    // Add to cart
    document.getElementById('add-to-cart-form')?.addEventListener('submit', function (e) {
        e.preventDefault();
        const productId = this.dataset.productId;
        const qty       = parseInt(document.getElementById('qty-input')?.value || '1', 10);
        const color     = document.querySelector('.color-swatch--active')?.dataset.value || null;
        const size      = document.querySelector('.size-btn--active')?.dataset.value    || null;

        // Delegate to cart.js
        if (window.Cart) {
            window.Cart.add({ productId, qty, color, size });
        } else {
            // Fallback: show toast
            if (window.Toast) window.Toast.success('Đã thêm vào giỏ hàng!');
        }
    });
})();
</script>