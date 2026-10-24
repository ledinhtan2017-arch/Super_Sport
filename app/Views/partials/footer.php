<footer class="footer" role="contentinfo">
    <div class="container">
        <div class="footer__grid">

            <!-- Brand -->
            <div class="footer__brand">
                <a href="<?= base_url('/') ?>" class="footer__logo" aria-label="SPORTZONE">
                    SPORT<span class="footer__logo-accent">ZONE</span>
                </a>
                <p class="footer__tagline">Gear For Every Champion.</p>
                <p class="footer__desc">Chuyên cung cấp đồ thể thao chính hãng từ các thương hiệu hàng đầu thế giới.</p>

                <!-- Social -->
                <div class="footer__social">
                    <a href="#" class="footer__social-link" aria-label="Facebook" rel="noopener noreferrer" target="_blank">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg>
                    </a>
                    <a href="#" class="footer__social-link" aria-label="Instagram" rel="noopener noreferrer" target="_blank">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/></svg>
                    </a>
                    <a href="#" class="footer__social-link" aria-label="TikTok" rel="noopener noreferrer" target="_blank">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M19.59 6.69a4.83 4.83 0 0 1-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 0 1-2.88 2.5 2.89 2.89 0 0 1-2.89-2.89 2.89 2.89 0 0 1 2.89-2.89c.28 0 .54.04.79.1V9.01a6.32 6.32 0 0 0-.79-.05 6.34 6.34 0 0 0-6.34 6.34 6.34 6.34 0 0 0 6.34 6.34 6.34 6.34 0 0 0 6.33-6.34V8.69a8.25 8.25 0 0 0 4.83 1.54V6.78a4.85 4.85 0 0 1-1.06-.09z"/></svg>
                    </a>
                </div>
            </div>

            <!-- Links -->
            <div class="footer__col">
                <h3 class="footer__heading">Sản phẩm</h3>
                <ul class="footer__links">
                    <li><a href="<?= base_url('/shop?category=giay-chay-bo') ?>"  class="footer__link">Giày chạy bộ</a></li>
                    <li><a href="<?= base_url('/shop?category=giay-tap-gym') ?>"  class="footer__link">Giày tập gym</a></li>
                    <li><a href="<?= base_url('/shop?category=ao-the-thao') ?>"   class="footer__link">Áo thể thao</a></li>
                    <li><a href="<?= base_url('/shop?category=quan-the-thao') ?>" class="footer__link">Quần thể thao</a></li>
                    <li><a href="<?= base_url('/shop?sale=1') ?>"                  class="footer__link footer__link--sale">Sale 🔥</a></li>
                </ul>
            </div>

            <div class="footer__col">
                <h3 class="footer__heading">Thương hiệu</h3>
                <ul class="footer__links">
                    <li><a href="<?= base_url('/shop?brand=nike') ?>"          class="footer__link">Nike</a></li>
                    <li><a href="<?= base_url('/shop?brand=adidas') ?>"        class="footer__link">Adidas</a></li>
                    <li><a href="<?= base_url('/shop?brand=puma') ?>"          class="footer__link">Puma</a></li>
                    <li><a href="<?= base_url('/shop?brand=under-armour') ?>"  class="footer__link">Under Armour</a></li>
                    <li><a href="<?= base_url('/shop?brand=new-balance') ?>"   class="footer__link">New Balance</a></li>
                </ul>
            </div>

            <div class="footer__col">
                <h3 class="footer__heading">Hỗ trợ</h3>
                <ul class="footer__links">
                    <li><a href="<?= base_url('/contact') ?>"       class="footer__link">Liên hệ</a></li>
                    <li><a href="<?= base_url('/about') ?>"         class="footer__link">Về chúng tôi</a></li>
                    <li><a href="<?= base_url('/return-policy') ?>" class="footer__link">Chính sách đổi trả</a></li>
                    <li><a href="<?= base_url('/privacy') ?>"       class="footer__link">Bảo mật</a></li>
                    <li><a href="<?= base_url('/account/orders') ?>"class="footer__link">Theo dõi đơn hàng</a></li>
                </ul>
            </div>

            <!-- Newsletter -->
            <div class="footer__col footer__col--newsletter">
                <h3 class="footer__heading">Nhận ưu đãi</h3>
                <p class="footer__newsletter-desc">Đăng ký nhận thông báo về sản phẩm mới và khuyến mãi độc quyền.</p>
                <form class="footer__newsletter-form" id="newsletter-form" novalidate>
                    <div class="footer__newsletter-input-wrap">
                        <input type="email" name="email" id="newsletter-email"
                               class="footer__newsletter-input"
                               placeholder="email@của-bạn.com"
                               autocomplete="email" required>
                        <button type="submit" class="footer__newsletter-btn" aria-label="Đăng ký">
                            →
                        </button>
                    </div>
                </form>
            </div>

        </div>

        <!-- Bottom Bar -->
        <div class="footer__bottom">
            <p class="footer__copyright">
                &copy; <?= date('Y') ?> SPORTZONE. All rights reserved.
            </p>
            <div class="footer__payment">
                <span class="footer__payment-label">Thanh toán:</span>
                <span class="footer__payment-icon" title="COD">💵 COD</span>
                <span class="footer__payment-icon" title="Bank Transfer">🏦 Chuyển khoản</span>
            </div>
        </div>
    </div>
</footer>
