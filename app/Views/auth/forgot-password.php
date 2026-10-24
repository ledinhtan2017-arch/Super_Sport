<?php
/**
 * auth/forgot-password.php
 * Variables: $title, $description
 */
?>
<div class="auth-page">
    <div class="auth-card">
        <div class="auth-card__brand">
            <a href="<?= base_url('/') ?>" class="auth-card__logo">
                <span class="auth-card__logo-text">SPORTZONE</span>
            </a>
            <h1 class="auth-card__title">Quên mật khẩu?</h1>
            <p class="auth-card__subtitle">Nhập email để nhận hướng dẫn đặt lại mật khẩu</p>
        </div>

        <form method="POST" action="<?= base_url('/forgot-password') ?>" class="auth-form" id="forgot-form" novalidate>
            <?= csrf_field() ?>

            <div class="form-group">
                <label for="email" class="form-label">Địa chỉ Email</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    class="form-control"
                    placeholder="ban@email.com"
                    autocomplete="email"
                    required
                >
            </div>

            <button type="submit" class="btn btn--primary btn--full btn--lg" id="forgot-submit">
                Gửi hướng dẫn
            </button>
        </form>

        <div class="auth-card__footer">
            <p><a href="<?= base_url('/login') ?>" class="auth-back-link" id="back-to-login">
                ← Quay lại đăng nhập
            </a></p>
        </div>
    </div>

    <div class="auth-bg" aria-hidden="true">
        <div class="auth-bg__shape auth-bg__shape--1"></div>
        <div class="auth-bg__shape auth-bg__shape--2"></div>
    </div>
</div>