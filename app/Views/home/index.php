<?php
/**
 * View: home/index.php
 * Data được inject từ HomeController:
 *  $title, $description, $featured, $categories, $new_arrivals, $brands
 */
?>

<!-- Hero Banner -->
<section class="hero" aria-label="Banner chính">
    <div class="hero__bg-media">
        <div class="hero__bg-gradient"></div>
    </div>
    <div class="container hero__content">
        <div class="hero__eyebrow">Bộ sưu tập 2025</div>
        <h1 class="hero__title">
            Gear For<br>
            Every <span class="hero__title-accent">Champion</span>
        </h1>
        <p class="hero__subtitle">
            Đồ thể thao chính hãng từ những thương hiệu hàng đầu.<br>
            Hiệu suất. Phong cách. Đam mê.
        </p>
        <div class="hero__cta-group">
            <a href="<?= base_url('/shop') ?>" class="btn btn--primary btn--lg" id="hero-cta-shop">
                Mua sắm ngay
            </a>
            <a href="<?= base_url('/shop?sale=1') ?>" class="btn btn--outline btn--lg" id="hero-cta-sale">
                Xem sale
            </a>
        </div>
        <div class="hero__stats">
            <div class="hero__stat">
                <span class="hero__stat-number">100+</span>
                <span class="hero__stat-label">Thương hiệu</span>
            </div>
            <div class="hero__stat-divider"></div>
            <div class="hero__stat">
                <span class="hero__stat-number">10K+</span>
                <span class="hero__stat-label">Sản phẩm</span>
            </div>
            <div class="hero__stat-divider"></div>
            <div class="hero__stat">
                <span class="hero__stat-number">50K+</span>
                <span class="hero__stat-label">Khách hàng</span>
            </div>
        </div>
    </div>
    <div class="hero__scroll-indicator" aria-hidden="true">
        <span class="hero__scroll-line"></span>
    </div>
</section>

<!-- Categories -->
<section class="section" aria-labelledby="categories-heading">
    <div class="container">
        <div class="section__header">
            <h2 class="section__title" id="categories-heading">Danh mục</h2>
            <a href="<?= base_url('/shop') ?>" class="section__see-all">Xem tất cả →</a>
        </div>

        <?php if (empty($categories)): ?>
        <div class="category-grid">
            <!-- Skeleton placeholders (Phase 2: replace with real data) -->
            <?php $catItems = [
                ['name'=>'Giày thể thao', 'slug'=>'giay-the-thao',  'emoji'=>'👟'],
                ['name'=>'Áo thể thao',   'slug'=>'ao-the-thao',    'emoji'=>'👕'],
                ['name'=>'Quần thể thao', 'slug'=>'quan-the-thao',  'emoji'=>'🩳'],
                ['name'=>'Phụ kiện',      'slug'=>'phu-kien',       'emoji'=>'🎒'],
            ]; ?>
            <?php foreach ($catItems as $cat): ?>
            <a href="<?= base_url('/shop?category=' . e($cat['slug'])) ?>"
               class="category-card" id="cat-<?= e($cat['slug']) ?>">
                <div class="category-card__icon"><?= $cat['emoji'] ?></div>
                <span class="category-card__name"><?= e($cat['name']) ?></span>
                <span class="category-card__arrow">→</span>
            </a>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="category-grid">
            <?php foreach ($categories as $cat): ?>
            <a href="<?= base_url('/shop?category=' . e($cat['slug'])) ?>"
               class="category-card" id="cat-<?= e($cat['slug']) ?>">
                <?php if (!empty($cat['image'])): ?>
                <img src="<?= asset(e($cat['image'])) ?>" alt="<?= e($cat['name']) ?>"
                     class="category-card__img" loading="lazy">
                <?php endif; ?>
                <span class="category-card__name"><?= e($cat['name']) ?></span>
                <span class="category-card__arrow">→</span>
            </a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- Featured Products -->
<section class="section section--dark" aria-labelledby="featured-heading">
    <div class="container">
        <div class="section__header">
            <h2 class="section__title" id="featured-heading">Sản phẩm nổi bật</h2>
            <a href="<?= base_url('/shop?featured=1') ?>" class="section__see-all">Xem tất cả →</a>
        </div>

        <?php if (empty($featured)): ?>
        <!-- Empty state skeleton (Phase 2 replaces this) -->
        <div class="product-grid">
            <?php for ($i = 0; $i < 4; $i++): ?>
            <div class="product-card product-card--skeleton" aria-hidden="true">
                <div class="product-card__img-wrap product-card__img-wrap--skeleton"></div>
                <div class="product-card__body">
                    <div class="skeleton-line skeleton-line--short"></div>
                    <div class="skeleton-line"></div>
                    <div class="skeleton-line skeleton-line--price"></div>
                </div>
            </div>
            <?php endfor; ?>
        </div>
        <?php else: ?>
        <div class="product-grid">
            <?php foreach ($featured as $product): ?>
            <?php include BASE_PATH . '/app/Views/partials/product-card.php'; ?>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- Brand Logos -->
<section class="section brands-section" aria-labelledby="brands-heading">
    <div class="container">
        <h2 class="section__title section__title--center" id="brands-heading">Thương hiệu</h2>
        <div class="brands-strip">
            <?php $brandNames = ['Nike', 'Adidas', 'Puma', 'Under Armour', 'New Balance', 'Asics']; ?>
            <?php foreach ($brandNames as $brand): ?>
            <a href="<?= base_url('/shop?brand=' . strtolower(str_replace(' ', '-', $brand))) ?>"
               class="brands-strip__item" title="<?= e($brand) ?>">
                <span class="brands-strip__name"><?= e($brand) ?></span>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- New Arrivals -->
<section class="section" aria-labelledby="new-arrivals-heading">
    <div class="container">
        <div class="section__header">
            <h2 class="section__title" id="new-arrivals-heading">Hàng mới về</h2>
            <a href="<?= base_url('/shop?sort=newest') ?>" class="section__see-all">Xem tất cả →</a>
        </div>
        <?php if (empty($new_arrivals)): ?>
        <div class="product-grid">
            <?php for ($i = 0; $i < 4; $i++): ?>
            <div class="product-card product-card--skeleton" aria-hidden="true">
                <div class="product-card__img-wrap product-card__img-wrap--skeleton"></div>
                <div class="product-card__body">
                    <div class="skeleton-line skeleton-line--short"></div>
                    <div class="skeleton-line"></div>
                    <div class="skeleton-line skeleton-line--price"></div>
                </div>
            </div>
            <?php endfor; ?>
        </div>
        <?php else: ?>
        <div class="product-grid">
            <?php foreach ($new_arrivals as $product): ?>
            <?php include BASE_PATH . '/app/Views/partials/product-card.php'; ?>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- Promo Banner -->
<section class="promo-banner" aria-label="Ưu đãi">
    <div class="container">
        <div class="promo-banner__inner">
            <div class="promo-banner__text">
                <div class="promo-banner__eyebrow">Ưu đãi đặc biệt</div>
                <h2 class="promo-banner__title">Miễn phí vận chuyển<br>cho đơn từ <?= format_price(config('app.shipping.free_threshold', 500000)) ?></h2>
                <p class="promo-banner__subtitle">Áp dụng toàn quốc. Giao hàng nhanh 2-4 ngày.</p>
                <a href="<?= base_url('/shop') ?>" class="btn btn--primary">Mua sắm ngay</a>
            </div>
            <div class="promo-banner__badge" aria-hidden="true">
                <span class="promo-banner__badge-text">FREE<br>SHIP</span>
            </div>
        </div>
    </div>
</section>
