<?php
/**
 * auth/login.php
 * Variables: $title, $description, $old (array: email)
 */
?>
<div class="auth-page">
    <div class="auth-card">
        <!-- Logo / Brand -->
        <div class="auth-card__brand">
            <a href="<?= base_url('/') ?>" class="auth-card__logo">
                <span class="auth-card__logo-text">SPORTZONE</span>
            </a>
            <h1 class="auth-card__title">Chào mừng trở lại</h1>
            <p class="auth-card__subtitle">Đăng nhập để tiếp tục mua sắm</p>
        </div>

        <form method="POST" action="<?= base_url('/login') ?>" class="auth-form" id="login-form" novalidate>
            <?= csrf_field() ?>

            <div class="form-group">
                <label for="email" class="form-label">Email</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    class="form-control"
                    value="<?= e($old['email'] ?? '') ?>"
                    placeholder="ban@email.com"
                    autocomplete="email"
                    required
                >
            </div>

            <div class="form-group">
                <div class="form-label-row">
                    <label for="password" class="form-label">Mật khẩu</label>
                    <a href="<?= base_url('/forgot-password') ?>" class="form-label-link" id="forgot-link">
                        Quên mật khẩu?
                    </a>
                </div>
                <div class="input-password-wrap">
                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="form-control"
                        placeholder="Tối thiểu 8 ký tự"
                        autocomplete="current-password"
                        required
                    >
                    <button type="button" class="input-password-toggle" aria-label="Hiện/ẩn mật khẩu" id="toggle-password">
                        <span class="eye-icon eye-icon--show">👁</span>
                        <span class="eye-icon eye-icon--hide" hidden>🙈</span>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn btn--primary btn--full btn--lg" id="login-submit">
                Đăng nhập
            </button>
        </form>

        <div class="auth-card__footer">
            <p>Chưa có tài khoản?
                <a href="<?= base_url('/register') ?>" id="go-register">Đăng ký ngay</a>
            </p>
        </div>
    </div>

    <!-- Decorative background -->
    <div class="auth-bg" aria-hidden="true">
        <div class="auth-bg__shape auth-bg__shape--1"></div>
        <div class="auth-bg__shape auth-bg__shape--2"></div>
        <div class="auth-bg__shape auth-bg__shape--3"></div>
    </div>
</div>

<script>
document.getElementById('toggle-password')?.addEventListener('click', function () {
    const input = document.getElementById('password');
    const isHidden = input.type === 'password';
    input.type = isHidden ? 'text' : 'password';
    this.querySelector('.eye-icon--show').hidden = isHidden;
    this.querySelector('.eye-icon--hide').hidden = !isHidden;
});
</script>