<header class="header" id="site-header" role="banner">
    <div class="container">
        <div class="header__inner">

            <!-- Hamburger (mobile/tablet) -->
            <button class="header__hamburger" id="hamburger-btn"
                    aria-label="Mở menu" aria-expanded="false" aria-controls="mobile-drawer">
                <span class="header__hamburger-line"></span>
                <span class="header__hamburger-line"></span>
                <span class="header__hamburger-line"></span>
            </button>

            <!-- Logo -->
            <a href="<?= base_url('/') ?>" class="header__logo" aria-label="SPORTZONE - Trang chủ">
                <span class="header__logo-text">SPORT<span class="header__logo-accent">ZONE</span></span>
            </a>

            <!-- Desktop Navigation -->
            <nav class="header__nav" role="navigation" aria-label="Điều hướng chính">
                <a href="<?= base_url('/shop?gender=nam') ?>"  class="header__nav-link">Nam</a>
                <a href="<?= base_url('/shop?gender=nu') ?>"   class="header__nav-link">Nữ</a>
                <a href="<?= base_url('/shop?gender=tre-em') ?>"class="header__nav-link">Trẻ em</a>
                <a href="<?= base_url('/shop?brand') ?>"       class="header__nav-link">Thương hiệu</a>
                <a href="<?= base_url('/shop?sale=1') ?>"      class="header__nav-link header__nav-link--sale">Sale</a>
            </nav>

            <!-- Actions -->
            <div class="header__actions">
                <!-- Search trigger -->
                <button class="header__action-btn" id="search-trigger"
                        aria-label="Tìm kiếm" aria-expanded="false" aria-controls="search-overlay">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
                    </svg>
                </button>

                <!-- Account -->
                <?php if (\App\Core\Session::isLoggedIn()): ?>
                <div class="header__user-menu" id="user-menu">
                    <button class="header__action-btn" aria-label="Tài khoản" aria-expanded="false">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
                        </svg>
                    </button>
                    <div class="header__dropdown" role="menu">
                        <a href="<?= base_url('/account/profile') ?>" class="header__dropdown-item" role="menuitem">Hồ sơ</a>
                        <a href="<?= base_url('/account/orders') ?>"  class="header__dropdown-item" role="menuitem">Đơn hàng</a>
                        <?php if (\App\Core\Session::isAdmin()): ?>
                        <a href="<?= base_url('/admin') ?>"           class="header__dropdown-item" role="menuitem">Admin Panel</a>
                        <?php endif; ?>
                        <div class="header__dropdown-divider"></div>
                        <form method="POST" action="<?= base_url('/logout') ?>">
                            <?= csrf_field() ?>
                            <button type="submit" class="header__dropdown-item header__dropdown-item--danger" role="menuitem">
                                Đăng xuất
                            </button>
                        </form>
                    </div>
                </div>
                <?php else: ?>
                <a href="<?= base_url('/login') ?>" class="header__action-btn" aria-label="Đăng nhập">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
                    </svg>
                </a>
                <?php endif; ?>

                <!-- Cart -->
                <a href="<?= base_url('/cart') ?>" class="header__action-btn header__cart-btn"
                   aria-label="Giỏ hàng" id="cart-btn">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/>
                        <line x1="3" y1="6" x2="21" y2="6"/>
                        <path d="M16 10a4 4 0 0 1-8 0"/>
                    </svg>
                    <span class="header__cart-badge" id="cart-count" aria-label="Số sản phẩm trong giỏ">0</span>
                </a>
            </div>
        </div>
    </div>
</header>

<!-- Search Overlay -->
<div class="search-overlay" id="search-overlay" role="dialog"
     aria-modal="true" aria-label="Tìm kiếm sản phẩm" hidden>
    <div class="search-overlay__inner">
        <button class="search-overlay__close" id="search-close" aria-label="Đóng tìm kiếm">✕</button>
        <form class="search-overlay__form" action="<?= base_url('/search') ?>" method="GET" role="search">
            <div class="search-overlay__input-wrap">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
                </svg>
                <input type="search" name="q" id="search-input"
                       class="search-overlay__input"
                       placeholder="Tìm giày, áo, phụ kiện..."
                       autocomplete="off" autocorrect="off" spellcheck="false">
            </div>
        </form>
        <div class="search-overlay__suggestions" id="search-suggestions" role="listbox" aria-label="Gợi ý tìm kiếm"></div>
        <div class="search-overlay__popular">
            <span class="search-overlay__popular-label">Tìm kiếm phổ biến:</span>
            <a href="<?= base_url('/shop?category=giay-chay-bo') ?>" class="search-overlay__tag">Giày chạy bộ</a>
            <a href="<?= base_url('/shop?category=ao-the-thao') ?>"  class="search-overlay__tag">Áo thể thao</a>
            <a href="<?= base_url('/shop?brand=nike') ?>"            class="search-overlay__tag">Nike</a>
            <a href="<?= base_url('/shop?brand=adidas') ?>"          class="search-overlay__tag">Adidas</a>
        </div>
    </div>
</div>

<!-- Mobile Drawer -->
<div class="mobile-drawer" id="mobile-drawer" role="dialog"
     aria-modal="true" aria-label="Menu điều hướng" hidden>
    <div class="mobile-drawer__backdrop" id="drawer-backdrop"></div>
    <nav class="mobile-drawer__nav">
        <div class="mobile-drawer__header">
            <span class="header__logo-text">SPORT<span class="header__logo-accent">ZONE</span></span>
            <button class="mobile-drawer__close" id="drawer-close" aria-label="Đóng menu">✕</button>
        </div>
        <a href="<?= base_url('/shop?gender=nam') ?>"   class="mobile-drawer__link">Nam</a>
        <a href="<?= base_url('/shop?gender=nu') ?>"    class="mobile-drawer__link">Nữ</a>
        <a href="<?= base_url('/shop?gender=tre-em') ?>"class="mobile-drawer__link">Trẻ em</a>
        <a href="<?= base_url('/shop?brand') ?>"        class="mobile-drawer__link">Thương hiệu</a>
        <a href="<?= base_url('/shop?sale=1') ?>"       class="mobile-drawer__link mobile-drawer__link--sale">Sale 🔥</a>
        <div class="mobile-drawer__divider"></div>
        <?php if (\App\Core\Session::isLoggedIn()): ?>
        <a href="<?= base_url('/account/profile') ?>"  class="mobile-drawer__link">Hồ sơ</a>
        <a href="<?= base_url('/account/orders') ?>"   class="mobile-drawer__link">Đơn hàng của tôi</a>
        <?php else: ?>
        <a href="<?= base_url('/login') ?>"    class="mobile-drawer__link">Đăng nhập</a>
        <a href="<?= base_url('/register') ?>" class="mobile-drawer__link">Đăng ký</a>
        <?php endif; ?>
    </nav>
</div>

<script>
// Header scroll behavior — thêm class khi scroll xuống
(function () {
    const header = document.getElementById('site-header');
    if (!header) return;
    let lastY = 0;
    window.addEventListener('scroll', function () {
        const y = window.scrollY;
        if (y > 64) {
            header.classList.add('header--scrolled');
            header.classList.toggle('header--hidden', y > lastY && y > 200);
        } else {
            header.classList.remove('header--scrolled', 'header--hidden');
        }
        lastY = y;
    }, { passive: true });

    // Search overlay
    const searchTrigger = document.getElementById('search-trigger');
    const searchOverlay  = document.getElementById('search-overlay');
    const searchClose    = document.getElementById('search-close');
    const searchInput    = document.getElementById('search-input');

    searchTrigger?.addEventListener('click', function () {
        searchOverlay?.removeAttribute('hidden');
        searchTrigger.setAttribute('aria-expanded', 'true');
        setTimeout(() => searchInput?.focus(), 50);
    });
    searchClose?.addEventListener('click', closeSearch);
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeSearch(); });
    function closeSearch() {
        searchOverlay?.setAttribute('hidden', '');
        searchTrigger?.setAttribute('aria-expanded', 'false');
    }

    // Mobile drawer
    const hamburger     = document.getElementById('hamburger-btn');
    const mobileDrawer  = document.getElementById('mobile-drawer');
    const drawerClose   = document.getElementById('drawer-close');
    const drawerBackdrop= document.getElementById('drawer-backdrop');

    hamburger?.addEventListener('click', function () {
        mobileDrawer?.removeAttribute('hidden');
        hamburger.setAttribute('aria-expanded', 'true');
    });
    [drawerClose, drawerBackdrop].forEach(el => el?.addEventListener('click', closeDrawer));
    function closeDrawer() {
        mobileDrawer?.setAttribute('hidden', '');
        hamburger?.setAttribute('aria-expanded', 'false');
    }
})();
</script>
