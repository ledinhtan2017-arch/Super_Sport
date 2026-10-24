<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex">
    <title>500 — Lỗi máy chủ | SPORTZONE</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@700;900&family=Inter:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= asset('css/main.css') ?>">
    <style>
        body { display:flex; align-items:center; justify-content:center; min-height:100vh; margin:0; background:var(--color-bg-primary); }
        .error-page { text-align:center; padding:var(--space-8) var(--space-4); max-width:600px; width:100%; }
        .error-page__code { font-family:var(--font-display); font-size:clamp(5rem,15vw,8rem); font-weight:900; line-height:1; color:var(--color-error); margin:0; }
        .error-page__title { font-size:var(--text-2xl); font-weight:700; color:var(--color-text-primary); margin:var(--space-4) 0 var(--space-2); }
        .error-page__desc { color:var(--color-text-secondary); margin-bottom:var(--space-6); }
        .error-page__actions { display:flex; gap:var(--space-3); justify-content:center; flex-wrap:wrap; margin-bottom:var(--space-8); }
        .error-page__debug { text-align:left; background:var(--color-bg-tertiary); border:1px solid var(--color-border); border-radius:var(--radius-lg); padding:var(--space-4); font-family:monospace; font-size:0.75rem; color:var(--color-text-secondary); overflow-x:auto; max-height:300px; overflow-y:auto; }
        .error-page__debug summary { cursor:pointer; color:var(--color-accent); margin-bottom:var(--space-2); }
    </style>
</head>
<body>
    <div class="error-page" role="main">
        <p class="error-page__code" aria-hidden="true">500</p>
        <h1 class="error-page__title">Đã có lỗi xảy ra</h1>
        <p class="error-page__desc"><?= e($message ?? 'Máy chủ gặp sự cố. Vui lòng thử lại sau hoặc liên hệ hỗ trợ.') ?></p>
        <div class="error-page__actions">
            <a href="<?= base_url('/') ?>" class="btn btn--primary">Về trang chủ</a>
            <button onclick="location.reload()" class="btn btn--outline">Thử lại</button>
        </div>

        <?php if (!empty($debug) && isset($exception) && $exception instanceof \Throwable): ?>
        <details class="error-page__debug">
            <summary>🐛 Debug info (chỉ hiển thị khi APP_DEBUG=true)</summary>
            <strong><?= e(get_class($exception)) ?></strong><br>
            <em><?= e($exception->getMessage()) ?></em><br><br>
            File: <?= e($exception->getFile()) ?>:<?= (int)$exception->getLine() ?><br><br>
            <pre><?= e($exception->getTraceAsString()) ?></pre>
        </details>
        <?php endif; ?>
    </div>
</body>
</html>
