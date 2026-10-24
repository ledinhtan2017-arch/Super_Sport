<?php
/**
 * auth/reset-password.php
 * Variables: $title, $description, $token
 */
?>
<div class="auth-page">
    <div class="auth-card">
        <div class="auth-card__brand">
            <a href="<?= base_url('/') ?>" class="auth-card__logo">
                <span class="auth-card__logo-text">SPORTZONE</span>
            </a>
            <h1 class="auth-card__title">Đặt lại mật khẩu</h1>
            <p class="auth-card__subtitle">Nhập mật khẩu mới cho tài khoản của bạn</p>
        </div>

        <form method="POST" action="<?= base_url('/reset-password') ?>" class="auth-form" id="reset-form" novalidate>
            <?= csrf_field() ?>
            <input type="hidden" name="token" value="<?= e($token) ?>">

            <div class="form-group">
                <label for="password" class="form-label">Mật khẩu mới <span class="required">*</span></label>
                <div class="input-password-wrap">
                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="form-control"
                        placeholder="Tối thiểu 8 ký tự"
                        autocomplete="new-password"
                        required
                    >
                    <button type="button" class="input-password-toggle" id="toggle-new">👁</button>
                </div>
            </div>

            <div class="form-group">
                <label for="password_confirmation" class="form-label">Xác nhận mật khẩu <span class="required">*</span></label>
                <input
                    type="password"
                    id="password_confirmation"
                    name="password_confirmation"
                    class="form-control"
                    placeholder="Nhập lại mật khẩu mới"
                    autocomplete="new-password"
                    required
                >
            </div>

            <button type="submit" class="btn btn--primary btn--full btn--lg" id="reset-submit">
                Xác nhận đổi mật khẩu
            </button>
        </form>

        <div class="auth-card__footer">
            <p><a href="<?= base_url('/login') ?>" id="back-login">← Quay lại đăng nhập</a></p>
        </div>
    </div>

    <div class="auth-bg" aria-hidden="true">
        <div class="auth-bg__shape auth-bg__shape--1"></div>
        <div class="auth-bg__shape auth-bg__shape--2"></div>
    </div>
</div>

<script>
document.getElementById('toggle-new')?.addEventListener('click', function () {
    const input = document.getElementById('password');
    input.type = input.type === 'password' ? 'text' : 'password';
    this.textContent = input.type === 'password' ? '👁' : '🙈';
});
</script>