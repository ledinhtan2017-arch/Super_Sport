<?php
/**
 * auth/register.php
 * Variables: $title, $description, $old (array)
 */
?>
<div class="auth-page">
    <div class="auth-card auth-card--wide">
        <div class="auth-card__brand">
            <a href="<?= base_url('/') ?>" class="auth-card__logo">
                <span class="auth-card__logo-text">SPORTZONE</span>
            </a>
            <h1 class="auth-card__title">Tạo tài khoản</h1>
            <p class="auth-card__subtitle">Đăng ký để nhận ưu đãi độc quyền</p>
        </div>

        <form method="POST" action="<?= base_url('/register') ?>" class="auth-form" id="register-form" novalidate>
            <?= csrf_field() ?>

            <div class="form-row">
                <div class="form-group">
                    <label for="name" class="form-label">Họ và tên <span class="required">*</span></label>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        class="form-control"
                        value="<?= e($old['name'] ?? '') ?>"
                        placeholder="Nguyễn Văn A"
                        autocomplete="name"
                        required
                    >
                </div>
                <div class="form-group">
                    <label for="phone" class="form-label">Số điện thoại</label>
                    <input
                        type="tel"
                        id="phone"
                        name="phone"
                        class="form-control"
                        value="<?= e($old['phone'] ?? '') ?>"
                        placeholder="0912 345 678"
                        autocomplete="tel"
                    >
                </div>
            </div>

            <div class="form-group">
                <label for="email" class="form-label">Email <span class="required">*</span></label>
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

            <div class="form-row">
                <div class="form-group">
                    <label for="password" class="form-label">Mật khẩu <span class="required">*</span></label>
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
                        <button type="button" class="input-password-toggle" aria-label="Hiện/ẩn" id="toggle-pass">👁</button>
                    </div>
                </div>
                <div class="form-group">
                    <label for="password_confirmation" class="form-label">Xác nhận mật khẩu <span class="required">*</span></label>
                    <input
                        type="password"
                        id="password_confirmation"
                        name="password_confirmation"
                        class="form-control"
                        placeholder="Nhập lại mật khẩu"
                        autocomplete="new-password"
                        required
                    >
                </div>
            </div>

            <div class="auth-terms">
                <p>Bằng cách đăng ký, bạn đồng ý với
                    <a href="<?= base_url('/privacy') ?>">Chính sách bảo mật</a>
                    và <a href="<?= base_url('/return-policy') ?>">Điều khoản sử dụng</a>.
                </p>
            </div>

            <button type="submit" class="btn btn--primary btn--full btn--lg" id="register-submit">
                Tạo tài khoản
            </button>
        </form>

        <div class="auth-card__footer">
            <p>Đã có tài khoản?
                <a href="<?= base_url('/login') ?>" id="go-login">Đăng nhập</a>
            </p>
        </div>
    </div>

    <div class="auth-bg" aria-hidden="true">
        <div class="auth-bg__shape auth-bg__shape--1"></div>
        <div class="auth-bg__shape auth-bg__shape--2"></div>
        <div class="auth-bg__shape auth-bg__shape--3"></div>
    </div>
</div>

<script>
document.getElementById('toggle-pass')?.addEventListener('click', function () {
    const input = document.getElementById('password');
    input.type = input.type === 'password' ? 'text' : 'password';
    this.textContent = input.type === 'password' ? '👁' : '🙈';
});
</script>