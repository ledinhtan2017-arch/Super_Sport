<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title><?= e($title ?? 'Admin — SPORTZONE') ?></title>

    <?= \App\Helpers\CsrfToken::meta() ?>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="<?= asset('css/main.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/admin.css') ?>">
    <link rel="icon" type="image/svg+xml" href="<?= asset('img/favicon.svg') ?>">
</head>
<body class="admin-body">

<div class="admin-layout">

    <!-- Sidebar -->
    <aside class="admin-sidebar" id="admin-sidebar" role="navigation" aria-label="Admin navigation">
        <div class="admin-sidebar__logo">
            <a href="<?= base_url('/admin') ?>">
                <span class="admin-sidebar__logo-text">SPORTZONE</span>
                <span class="admin-sidebar__logo-badge">Admin</span>
            </a>
        </div>

        <nav class="admin-nav">
            <a href="<?= base_url('/admin/dashboard') ?>" class="admin-nav__item <?= str_contains($_SERVER['REQUEST_URI'] ?? '', '/admin/dashboard') ? 'is-active' : '' ?>">
                <span class="admin-nav__icon">📊</span>
                <span class="admin-nav__label">Dashboard</span>
            </a>
            <a href="<?= base_url('/admin/products') ?>" class="admin-nav__item <?= str_contains($_SERVER['REQUEST_URI'] ?? '', '/admin/products') ? 'is-active' : '' ?>">
                <span class="admin-nav__icon">📦</span>
                <span class="admin-nav__label">Sản phẩm</span>
            </a>
            <a href="<?= base_url('/admin/categories') ?>" class="admin-nav__item <?= str_contains($_SERVER['REQUEST_URI'] ?? '', '/admin/categories') ? 'is-active' : '' ?>">
                <span class="admin-nav__icon">📂</span>
                <span class="admin-nav__label">Danh mục</span>
            </a>
            <a href="<?= base_url('/admin/brands') ?>" class="admin-nav__item <?= str_contains($_SERVER['REQUEST_URI'] ?? '', '/admin/brands') ? 'is-active' : '' ?>">
                <span class="admin-nav__icon">🏷️</span>
                <span class="admin-nav__label">Thương hiệu</span>
            </a>
            <a href="<?= base_url('/admin/orders') ?>" class="admin-nav__item <?= str_contains($_SERVER['REQUEST_URI'] ?? '', '/admin/orders') ? 'is-active' : '' ?>">
                <span class="admin-nav__icon">📋</span>
                <span class="admin-nav__label">Đơn hàng</span>
            </a>
            <a href="<?= base_url('/admin/users') ?>" class="admin-nav__item <?= str_contains($_SERVER['REQUEST_URI'] ?? '', '/admin/users') ? 'is-active' : '' ?>">
                <span class="admin-nav__icon">👥</span>
                <span class="admin-nav__label">Người dùng</span>
            </a>
            <a href="<?= base_url('/admin/discounts') ?>" class="admin-nav__item <?= str_contains($_SERVER['REQUEST_URI'] ?? '', '/admin/discounts') ? 'is-active' : '' ?>">
                <span class="admin-nav__icon">🎟️</span>
                <span class="admin-nav__label">Khuyến mãi</span>
            </a>
            <a href="<?= base_url('/admin/reports') ?>" class="admin-nav__item <?= str_contains($_SERVER['REQUEST_URI'] ?? '', '/admin/reports') ? 'is-active' : '' ?>">
                <span class="admin-nav__icon">📈</span>
                <span class="admin-nav__label">Báo cáo</span>
            </a>
        </nav>

        <div class="admin-sidebar__footer">
            <a href="<?= base_url('/') ?>" class="admin-nav__item" target="_blank">
                <span class="admin-nav__icon">🌐</span>
                <span class="admin-nav__label">Xem website</span>
            </a>
            <form method="POST" action="<?= base_url('/logout') ?>">
                <?= csrf_field() ?>
                <button type="submit" class="admin-nav__item admin-nav__item--logout">
                    <span class="admin-nav__icon">↩️</span>
                    <span class="admin-nav__label">Đăng xuất</span>
                </button>
            </form>
        </div>
    </aside>

    <!-- Main Area -->
    <div class="admin-main">

        <!-- Topbar -->
        <header class="admin-topbar">
            <button class="admin-topbar__toggle" id="sidebar-toggle" aria-label="Toggle sidebar">☰</button>
            <h1 class="admin-topbar__title"><?= e($pageTitle ?? 'Admin') ?></h1>
            <div class="admin-topbar__user">
                <span><?= e(\App\Core\Session::user()['name'] ?? 'Admin') ?></span>
            </div>
        </header>

        <!-- Flash Messages -->
        <?php $flashes = \App\Core\Session::getFlash(); ?>
        <?php if (!empty($flashes)): ?>
        <div class="flash-container" role="status" aria-live="polite">
            <?php foreach ($flashes as $type => $messages): ?>
                <?php foreach ($messages as $msg): ?>
                <div class="flash flash--<?= e($type) ?>">
                    <span><?= e($msg) ?></span>
                    <button class="flash__close" onclick="this.parentElement.remove()">✕</button>
                </div>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Page Content -->
        <div class="admin-content">
            <?= $content ?>
        </div>
    </div>
</div>

<!-- JS -->
<script src="<?= asset('js/core/http.js') ?>"></script>
<script src="<?= asset('js/core/utils.js') ?>"></script>
<script src="<?= asset('js/core/toast.js') ?>"></script>
<script src="<?= asset('js/admin/product-form.js') ?>"></script>

<script>
    window.SPORTZONE = {
        csrfToken: <?= json_encode(\App\Helpers\CsrfToken::get()) ?>,
        baseUrl:   <?= json_encode(config('app.url')) ?>,
        isAdmin:   true
    };

    // Sidebar toggle
    document.getElementById('sidebar-toggle')?.addEventListener('click', function () {
        document.getElementById('admin-sidebar')?.classList.toggle('is-open');
    });
</script>
</body>
</html>
