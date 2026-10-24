<?php
/**
 * shop/search.php
 * Variables: $title, $description, $keyword, $products, $paginator
 */
?>
<section class="shop-header">
    <div class="container">
        <nav class="breadcrumb" aria-label="Breadcrumb">
            <a href="<?= base_url('/') ?>" class="breadcrumb__item">Trang chủ</a>
            <span class="breadcrumb__sep">›</span>
            <span class="breadcrumb__item breadcrumb__item--active">Tìm kiếm</span>
        </nav>
        <h1 class="shop-header__title">
            <?php if ($keyword !== ''): ?>
            Kết quả cho &ldquo;<span class="shop-header__keyword"><?= e($keyword) ?></span>&rdquo;
            <?php else: ?>
            Tìm kiếm sản phẩm
            <?php endif; ?>
        </h1>
        <?php if ($keyword !== '' && $paginator['total'] > 0): ?>
        <p class="shop-header__count"><?= number_format($paginator['total']) ?> sản phẩm được tìm thấy</p>
        <?php endif; ?>
    </div>
</section>

<div class="container" style="padding-top: var(--space-6); padding-bottom: var(--space-16);">

    <!-- Search bar -->
    <form method="get" action="<?= base_url('/search') ?>" class="search-bar-large" role="search">
        <div class="search-bar-large__wrap">
            <svg class="search-bar-large__icon" width="20" height="20" viewBox="0 0 24 24"
                 fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
            <input type="search" name="q" id="search-q"
                   class="search-bar-large__input"
                   placeholder="Tìm giày, áo, quần thể thao..."
                   value="<?= e($keyword) ?>"
                   autofocus
                   autocomplete="off">
            <button type="submit" class="btn btn--primary search-bar-large__btn">Tìm kiếm</button>
        </div>
    </form>

    <!-- Results -->
    <?php if ($keyword === ''): ?>
    <div class="shop-empty" style="margin-top: var(--space-16)">
        <div class="shop-empty__icon">🔍</div>
        <p class="shop-empty__desc">Nhập từ khóa để tìm kiếm sản phẩm.</p>
    </div>

    <?php elseif (empty($products)): ?>
    <div class="shop-empty" style="margin-top: var(--space-12)">
        <div class="shop-empty__icon">😔</div>
        <h2 class="shop-empty__title">Không tìm thấy sản phẩm</h2>
        <p class="shop-empty__desc">Không có kết quả cho &ldquo;<?= e($keyword) ?>&rdquo;. Thử từ khóa khác?</p>
        <a href="<?= base_url('/shop') ?>" class="btn btn--primary">Xem tất cả sản phẩm</a>
    </div>

    <?php else: ?>
    <div class="product-grid" style="margin-top: var(--space-8)">
        <?php foreach ($products as $product): ?>
        <?php include BASE_PATH . '/app/Views/partials/product-card.php'; ?>
        <?php endforeach; ?>
    </div>

    <!-- Pagination -->
    <?php if ($paginator['last_page'] > 1): ?>
    <nav class="pagination" aria-label="Phân trang">
        <?php $page = $paginator['current_page']; $last = $paginator['last_page']; ?>
        <?php if ($page > 1): ?>
        <a href="<?= base_url('/search?q=' . urlencode($keyword) . '&page=' . ($page - 1)) ?>"
           class="pagination__btn">‹ Trước</a>
        <?php endif; ?>

        <?php for ($i = max(1, $page - 2); $i <= min($last, $page + 2); $i++): ?>
        <a href="<?= base_url('/search?q=' . urlencode($keyword) . '&page=' . $i) ?>"
           class="pagination__btn <?= $i === $page ? 'pagination__btn--active' : '' ?>"><?= $i ?></a>
        <?php endfor; ?>

        <?php if ($page < $last): ?>
        <a href="<?= base_url('/search?q=' . urlencode($keyword) . '&page=' . ($page + 1)) ?>"
           class="pagination__btn">Sau ›</a>
        <?php endif; ?>
    </nav>
    <?php endif; ?>
    <?php endif; ?>

</div>