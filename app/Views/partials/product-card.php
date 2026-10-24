<?php
/**
 * partials/product-card.php
 * Dùng trong: home/index.php, shop/index.php, shop/search.php, product/show.php
 *
 * Yêu cầu biến $product với các key:
 *  id, name, slug, thumbnail, brand_name, brand_slug,
 *  category_name, category_slug, min_price, base_price, sale_price, total_stock
 */

$isOnSale   = !empty($product['sale_price']) && $product['sale_price'] < $product['base_price'];
$isInStock  = ($product['total_stock'] ?? 0) > 0;
$discount   = $isOnSale ? discount_percent($product['base_price'], $product['sale_price']) : 0;
$displayPrice = $isOnSale ? $product['sale_price'] : ($product['min_price'] ?? $product['base_price'] ?? 0);
$thumbnail  = $product['thumbnail'] ?? null;
?>
<article class="product-card" id="product-<?= e($product['id']) ?>">
    <a href="<?= base_url('/product/' . e($product['slug'])) ?>" class="product-card__link" tabindex="0">

        <!-- Image -->
        <div class="product-card__img-wrap">
            <?php if ($thumbnail): ?>
            <img src="<?= e($thumbnail) ?>"
                 alt="<?= e($product['name']) ?>"
                 class="product-card__img"
                 loading="lazy"
                 width="400" height="500">
            <?php else: ?>
            <div class="product-card__img-placeholder" aria-hidden="true">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
                    <rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/>
                    <polyline points="21 15 16 10 5 21"/>
                </svg>
            </div>
            <?php endif; ?>

            <!-- Badges -->
            <div class="product-card__badges">
                <?php if ($isOnSale): ?>
                <span class="badge badge--sale">-<?= $discount ?>%</span>
                <?php endif; ?>
                <?php if (!$isInStock): ?>
                <span class="badge badge--soldout">Hết hàng</span>
                <?php endif; ?>
            </div>

            <!-- Quick Add (shows on hover) -->
            <?php if ($isInStock): ?>
            <button class="product-card__quick-add"
                    data-product-id="<?= e($product['id']) ?>"
                    data-product-name="<?= e($product['name']) ?>"
                    aria-label="Thêm vào giỏ">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/>
                    <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
                </svg>
                Thêm vào giỏ
            </button>
            <?php endif; ?>
        </div>

        <!-- Body -->
        <div class="product-card__body">
            <p class="product-card__brand"><?= e($product['brand_name'] ?? '') ?></p>
            <h3 class="product-card__name"><?= e($product['name']) ?></h3>

            <div class="product-card__price-row">
                <span class="product-card__price <?= $isOnSale ? 'product-card__price--sale' : '' ?>">
                    <?= format_price((int)$displayPrice) ?>
                </span>
                <?php if ($isOnSale): ?>
                <span class="product-card__price-original"><?= format_price((int)$product['base_price']) ?></span>
                <?php endif; ?>
            </div>
        </div>
    </a>
</article>