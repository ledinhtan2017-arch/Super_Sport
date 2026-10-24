<?php
/**
 * shop/index.php
 * Variables: $title, $description, $products, $paginator, $categories,
 *            $brands, $filters, $sort, $activeCategory, $activeBrand, $pageTitle
 */

/** Build query string helper (preserves existing params, overrides given ones) */
$shopUrl = function (array $overrides = [], array $remove = []) use ($filters, $sort): string
{
    $params = array_merge($filters, ['sort' => $sort]);
    foreach ($remove as $key) unset($params[$key]);
    foreach ($overrides as $k => $v) $params[$k] = $v;
    if (!array_key_exists('page', $overrides)) unset($params['page']);
    return base_url('/shop?' . http_build_query(array_filter($params, fn($v) => $v !== '' && $v !== null)));
};
?>

<!-- Page Header -->
<section class="shop-header">
    <div class="container">
        <nav class="breadcrumb" aria-label="Breadcrumb">
            <a href="<?= base_url('/') ?>" class="breadcrumb__item">Trang chủ</a>
            <span class="breadcrumb__sep">›</span>
            <span class="breadcrumb__item breadcrumb__item--active">Cửa hàng</span>
            <?php if ($activeCategory || $activeBrand): ?>
            <span class="breadcrumb__sep">›</span>
            <span class="breadcrumb__item breadcrumb__item--active">
                <?= e($activeCategory['name'] ?? $activeBrand['name'] ?? '') ?>
            </span>
            <?php endif; ?>
        </nav>
        <h1 class="shop-header__title"><?= e($pageTitle) ?></h1>
        <?php if (!empty($paginator['total'])): ?>
        <p class="shop-header__count"><?= number_format($paginator['total']) ?> sản phẩm</p>
        <?php endif; ?>
    </div>
</section>

<div class="container shop-layout">

    <!-- ── Sidebar Filters ── -->
    <aside class="shop-sidebar" id="shop-sidebar" aria-label="Bộ lọc sản phẩm">

        <!-- Mobile close -->
        <div class="shop-sidebar__header">
            <span class="shop-sidebar__heading">Bộ lọc</span>
            <button class="shop-sidebar__close" id="sidebar-close" aria-label="Đóng bộ lọc">✕</button>
        </div>

        <!-- Active filters -->
        <?php $hasFilters = !empty($filters); ?>
        <?php if ($hasFilters): ?>
        <div class="shop-sidebar__active-filters">
            <?php if (!empty($filters['category'])): ?>
            <a href="<?= $shopUrl([], ['category']) ?>" class="active-filter-tag">
                <?= e($activeCategory['name'] ?? $filters['category']) ?> ✕
            </a>
            <?php endif; ?>
            <?php if (!empty($filters['brand'])): ?>
            <a href="<?= $shopUrl([], ['brand']) ?>" class="active-filter-tag">
                <?= e($activeBrand['name'] ?? $filters['brand']) ?> ✕
            </a>
            <?php endif; ?>
            <?php if (!empty($filters['sale'])): ?>
            <a href="<?= $shopUrl([], ['sale']) ?>" class="active-filter-tag">Sale ✕</a>
            <?php endif; ?>
            <a href="<?= base_url('/shop') ?>" class="active-filter-tag active-filter-tag--clear">Xóa tất cả</a>
        </div>
        <?php endif; ?>

        <!-- Sale filter -->
        <div class="filter-group">
            <h3 class="filter-group__title">Khuyến mãi</h3>
            <a href="<?= !empty($filters['sale']) ? $shopUrl([], ['sale']) : $shopUrl(['sale' => 1]) ?>"
               class="filter-item <?= !empty($filters['sale']) ? 'filter-item--active' : '' ?>">
                <span class="filter-item__icon">🔥</span> Sale
            </a>
        </div>

        <!-- Categories -->
        <div class="filter-group">
            <h3 class="filter-group__title">Danh mục</h3>
            <?php foreach ($categories as $cat): ?>
            <a href="<?= ($filters['category'] ?? '') === $cat['slug']
                    ? $shopUrl([], ['category'])
                    : $shopUrl(['category' => $cat['slug']]) ?>"
               class="filter-item <?= ($filters['category'] ?? '') === $cat['slug'] ? 'filter-item--active' : '' ?>"
               id="filter-cat-<?= e($cat['slug']) ?>">
                <?= e($cat['name']) ?>
                <?php if (!empty($cat['product_count'])): ?>
                <span class="filter-item__count"><?= (int)$cat['product_count'] ?></span>
                <?php endif; ?>
            </a>
            <?php endforeach; ?>
        </div>

        <!-- Brands -->
        <div class="filter-group">
            <h3 class="filter-group__title">Thương hiệu</h3>
            <?php foreach ($brands as $brand): ?>
            <a href="<?= ($filters['brand'] ?? '') === $brand['slug']
                    ? $shopUrl([], ['brand'])
                    : $shopUrl(['brand' => $brand['slug']]) ?>"
               class="filter-item <?= ($filters['brand'] ?? '') === $brand['slug'] ? 'filter-item--active' : '' ?>"
               id="filter-brand-<?= e($brand['slug']) ?>">
                <?= e($brand['name']) ?>
                <?php if (!empty($brand['product_count'])): ?>
                <span class="filter-item__count"><?= (int)$brand['product_count'] ?></span>
                <?php endif; ?>
            </a>
            <?php endforeach; ?>
        </div>

        <!-- Price Range -->
        <div class="filter-group">
            <h3 class="filter-group__title">Giá</h3>
            <form id="price-filter-form" class="price-range-form" method="get" action="<?= base_url('/shop') ?>">
                <?php foreach (array_filter($filters, fn($k) => !in_array($k, ['min_price','max_price']), ARRAY_FILTER_USE_KEY) as $k => $v): ?>
                <input type="hidden" name="<?= e($k) ?>" value="<?= e($v) ?>">
                <?php endforeach; ?>
                <input type="hidden" name="sort" value="<?= e($sort) ?>">
                <div class="price-range-inputs">
                    <input type="number" name="min_price" id="min-price"
                           class="price-range-input" placeholder="Từ"
                           value="<?= e($filters['min_price'] ?? '') ?>" min="0" step="50000">
                    <span>—</span>
                    <input type="number" name="max_price" id="max-price"
                           class="price-range-input" placeholder="Đến"
                           value="<?= e($filters['max_price'] ?? '') ?>" min="0" step="50000">
                </div>
                <button type="submit" class="btn btn--sm btn--outline" style="width:100%;margin-top:var(--space-3)">
                    Áp dụng
                </button>
            </form>
        </div>

    </aside>

    <!-- ── Main Product Area ── -->
    <div class="shop-main">

        <!-- Toolbar -->
        <div class="shop-toolbar">
            <button class="shop-toolbar__filter-btn" id="sidebar-open" aria-expanded="false" aria-controls="shop-sidebar">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="4" y1="6" x2="20" y2="6"/><line x1="8" y1="12" x2="20" y2="12"/>
                    <line x1="12" y1="18" x2="20" y2="18"/>
                </svg>
                Bộ lọc
            </button>

            <div class="shop-toolbar__sort">
                <label for="sort-select" class="shop-toolbar__sort-label">Sắp xếp:</label>
                <select id="sort-select" class="shop-toolbar__sort-select"
                        onchange="location.href='<?= $shopUrl() ?>&sort='+this.value+'<?= !empty($filters) ? '' : '' ?>'">
                    <option value="newest"    <?= $sort === 'newest'    ? 'selected' : '' ?>>Mới nhất</option>
                    <option value="popular"   <?= $sort === 'popular'   ? 'selected' : '' ?>>Phổ biến</option>
                    <option value="price_asc" <?= $sort === 'price_asc' ? 'selected' : '' ?>>Giá tăng dần</option>
                    <option value="price_desc"<?= $sort === 'price_desc'? 'selected' : '' ?>>Giá giảm dần</option>
                </select>
            </div>
        </div>

        <!-- Product Grid -->
        <?php if (empty($products)): ?>
        <div class="shop-empty">
            <div class="shop-empty__icon">🔍</div>
            <h2 class="shop-empty__title">Không tìm thấy sản phẩm</h2>
            <p class="shop-empty__desc">Thử thay đổi bộ lọc hoặc tìm kiếm với từ khóa khác.</p>
            <a href="<?= base_url('/shop') ?>" class="btn btn--primary">Xem tất cả sản phẩm</a>
        </div>
        <?php else: ?>
        <div class="product-grid" id="product-grid">
            <?php foreach ($products as $product): ?>
            <?php include BASE_PATH . '/app/Views/partials/product-card.php'; ?>
            <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <?php if ($paginator['last_page'] > 1): ?>
        <nav class="pagination" aria-label="Phân trang">
            <?php
            $currentPage = $paginator['current_page'];
            $lastPage    = $paginator['last_page'];

            // Prev
            if ($currentPage > 1): ?>
            <a href="<?= $shopUrl(['page' => $currentPage - 1]) ?>"
               class="pagination__btn" aria-label="Trang trước">
                ‹ Trước
            </a>
            <?php endif; ?>

            <?php
            // Pages
            $start = max(1, $currentPage - 2);
            $end   = min($lastPage, $currentPage + 2);
            if ($start > 1): ?>
            <a href="<?= $shopUrl(['page' => 1]) ?>" class="pagination__btn">1</a>
            <?php if ($start > 2): ?><span class="pagination__ellipsis">…</span><?php endif; ?>
            <?php endif; ?>

            <?php for ($i = $start; $i <= $end; $i++): ?>
            <a href="<?= $shopUrl(['page' => $i]) ?>"
               class="pagination__btn <?= $i === $currentPage ? 'pagination__btn--active' : '' ?>"
               aria-current="<?= $i === $currentPage ? 'page' : 'false' ?>">
                <?= $i ?>
            </a>
            <?php endfor; ?>

            <?php if ($end < $lastPage): ?>
            <?php if ($end < $lastPage - 1): ?><span class="pagination__ellipsis">…</span><?php endif; ?>
            <a href="<?= $shopUrl(['page' => $lastPage]) ?>"
               class="pagination__btn"><?= $lastPage ?></a>
            <?php endif; ?>

            <?php if ($currentPage < $lastPage): ?>
            <a href="<?= $shopUrl(['page' => $currentPage + 1]) ?>"
               class="pagination__btn" aria-label="Trang sau">
                Sau ›
            </a>
            <?php endif; ?>
        </nav>
        <?php endif; ?>
        <?php endif; ?>

    </div><!-- /.shop-main -->
</div><!-- /.shop-layout -->

<!-- Mobile sidebar backdrop -->
<div class="sidebar-backdrop" id="sidebar-backdrop" hidden aria-hidden="true"></div>

<script>
(function () {
    const open    = document.getElementById('sidebar-open');
    const close   = document.getElementById('sidebar-close');
    const sidebar = document.getElementById('shop-sidebar');
    const backdrop= document.getElementById('sidebar-backdrop');

    function openSidebar() {
        sidebar.classList.add('shop-sidebar--open');
        backdrop.removeAttribute('hidden');
        open?.setAttribute('aria-expanded', 'true');
        document.body.style.overflow = 'hidden';
    }
    function closeSidebar() {
        sidebar.classList.remove('shop-sidebar--open');
        backdrop.setAttribute('hidden', '');
        open?.setAttribute('aria-expanded', 'false');
        document.body.style.overflow = '';
    }

    open?.addEventListener('click', openSidebar);
    close?.addEventListener('click', closeSidebar);
    backdrop?.addEventListener('click', closeSidebar);
    document.addEventListener('keydown', e => { if (e.key === 'Escape') closeSidebar(); });
})();
</script>