<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex">
    <title>404 — Không tìm thấy trang | SPORTZONE</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@700;900&family=Inter:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= asset('css/main.css') ?>">
    <style>
        body { display:flex; align-items:center; justify-content:center; min-height:100vh; margin:0; background:var(--color-bg-primary); }
        .error-page { text-align:center; padding:var(--space-8) var(--space-4); max-width:480px; }
        .error-page__code { font-family:var(--font-display); font-size:clamp(6rem,20vw,10rem); font-weight:900; line-height:1; color:var(--color-accent); margin:0; }
        .error-page__title { font-family:var(--font-body); font-size:var(--text-2xl); font-weight:700; color:var(--color-text-primary); margin:var(--space-4) 0 var(--space-2); }
        .error-page__desc { color:var(--color-text-secondary); margin-bottom:var(--space-8); }
        .error-page__actions { display:flex; gap:var(--space-3); justify-content:center; flex-wrap:wrap; }
    </style>
</head>
<body>
    <div class="error-page" role="main">
        <p class="error-page__code" aria-hidden="true">404</p>
        <h1 class="error-page__title">Trang không tồn tại</h1>
        <p class="error-page__desc">Trang bạn đang tìm kiếm đã bị xóa, đổi tên,<br>hoặc chưa từng tồn tại.</p>
        <div class="error-page__actions">
            <a href="<?= base_url('/') ?>" class="btn btn--primary">Về trang chủ</a>
            <a href="<?= base_url('/shop') ?>" class="btn btn--outline">Xem sản phẩm</a>
        </div>
    </div>
</body>
</html>
