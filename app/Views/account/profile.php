<?php /** account/profile.php — Variables: $user */ 
$errors = \App\Core\Session::getFlash('errors') ?? [];
$old    = \App\Core\Session::getFlash('old')    ?? [];
?>
<div class="account-page">
  <div class="container">
    <div class="account-grid">
      <?php include __DIR__ . '/../partials/account-nav.php'; ?>
      <div class="account-content">
        <h1 class="account-content__title">Thông tin tài khoản</h1>

        <!-- Update Profile -->
        <div class="account-card">
          <h2 class="account-card__title">Thông tin cơ bản</h2>
          <?php if ($success = \App\Core\Session::getFlash('success')): ?>
          <div class="alert alert--success"><?= e($success) ?></div>
          <?php endif; ?>
          <form method="POST" action="<?= base_url('/account/profile') ?>" class="account-form">
            <?= csrf_field() ?>
            <div class="form-grid form-grid--2">
              <div class="form-group">
                <label class="form-label">Email</label>
                <input type="email" class="form-control" value="<?= e($user['email']) ?>" disabled>
                <span class="form-hint">Email không thể thay đổi.</span>
              </div>
              <div class="form-group">
                <label class="form-label" for="p-name">Họ tên</label>
                <input type="text" name="name" id="p-name" class="form-control"
                       value="<?= e($old['name'] ?? $user['name']) ?>" required>
                <?php if (!empty($errors['name'])): ?><span class="form-error"><?= e($errors['name'][0]) ?></span><?php endif; ?>
              </div>
              <div class="form-group">
                <label class="form-label" for="p-phone">Số điện thoại</label>
                <input type="tel" name="phone" id="p-phone" class="form-control"
                       value="<?= e($old['phone'] ?? $user['phone'] ?? '') ?>">
              </div>
            </div>
            <button type="submit" class="btn btn--primary">Lưu thay đổi</button>
          </form>
        </div>

        <!-- Change Password -->
        <div class="account-card">
          <h2 class="account-card__title">Đổi mật khẩu</h2>
          <form method="POST" action="<?= base_url('/account/password') ?>" class="account-form">
            <?= csrf_field() ?>
            <div class="form-group">
              <label class="form-label" for="pw-current">Mật khẩu hiện tại</label>
              <input type="password" name="current_password" id="pw-current" class="form-control" required>
            </div>
            <div class="form-grid form-grid--2">
              <div class="form-group">
                <label class="form-label" for="pw-new">Mật khẩu mới</label>
                <input type="password" name="password" id="pw-new" class="form-control" required minlength="8">
              </div>
              <div class="form-group">
                <label class="form-label" for="pw-confirm">Xác nhận mật khẩu</label>
                <input type="password" name="password_confirmation" id="pw-confirm" class="form-control" required>
              </div>
            </div>
            <button type="submit" class="btn btn--primary">Đổi mật khẩu</button>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>