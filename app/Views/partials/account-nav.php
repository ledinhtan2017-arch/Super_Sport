<?php
$currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
function navActive(string $path, string $current): string {
    return str_starts_with($current, $path) ? 'is-active' : '';
}
?>
<aside class="account-sidebar">
  <div class="account-sidebar__user">
    <div class="account-sidebar__avatar"><?= strtoupper(substr(\App\Core\Session::get('user_name', 'U'), 0, 1)) ?></div>
    <div>
      <div class="account-sidebar__name"><?= e(\App\Core\Session::get('user_name', '')) ?></div>
      <div class="account-sidebar__email text-muted text-sm"><?= e(\App\Core\Session::get('user_email', '')) ?></div>
    </div>
  </div>
  <nav class="account-nav">
    <a href="<?= base_url('/account/profile') ?>"   class="account-nav__item <?= navActive(base_url('/account/profile'), $currentPath) ?>">👤 Thông tin tài khoản</a>
    <a href="<?= base_url('/account/orders') ?>"    class="account-nav__item <?= navActive(base_url('/account/orders'),  $currentPath) ?>">📋 Đơn hàng của tôi</a>
    <a href="<?= base_url('/account/addresses') ?>" class="account-nav__item <?= navActive(base_url('/account/addresses'), $currentPath) ?>">📍 Địa chỉ của tôi</a>
    <form method="POST" action="<?= base_url('/logout') ?>">
      <?= csrf_field() ?>
      <button type="submit" class="account-nav__item account-nav__item--logout">🚪 Đăng xuất</button>
    </form>
  </nav>
</aside>