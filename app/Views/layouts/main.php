<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= e($description ?? 'SPORTZONE — Gear For Every Champion') ?>">
    <meta name="robots" content="index, follow">
    <title><?= e($title ?? 'SPORTZONE') ?></title>

    <?= \App\Helpers\CsrfToken::meta() ?>

    <!-- Preconnect Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@400;700;900&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- CSS -->
    <link rel="stylesheet" href="<?= asset('css/main.css') ?>">

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="<?= asset('img/favicon.svg') ?>">
</head>
<body>

    <?php require BASE_PATH . '/app/Views/partials/header.php'; ?>

    <!-- Flash Messages -->
    <?php $flashes = \App\Core\Session::getFlash(); ?>
    <?php if (!empty($flashes)): ?>
    <div class="flash-container" id="flash-container" role="status" aria-live="polite">
        <?php foreach ($flashes as $type => $messages): ?>
            <?php foreach ($messages as $msg): ?>
            <div class="flash flash--<?= e($type) ?>">
                <span><?= e($msg) ?></span>
                <button class="flash__close" aria-label="Đóng" onclick="this.parentElement.remove()">✕</button>
            </div>
            <?php endforeach; ?>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- Main Content -->
    <main id="main-content">
        <?= $content ?>
    </main>

    <?php require BASE_PATH . '/app/Views/partials/footer.php'; ?>

    <!-- JS -->
    <script src="<?= asset('js/core/http.js') ?>"></script>
    <script src="<?= asset('js/core/utils.js') ?>"></script>
    <script src="<?= asset('js/core/toast.js') ?>"></script>
    <script src="<?= asset('js/cart.js') ?>"></script>

    <!-- CSRF token cho fetch() AJAX -->
    <script>
        window.SPORTZONE = {
            csrfToken: <?= \App\Helpers\CsrfToken::get() ? json_encode(\App\Helpers\CsrfToken::get()) : '""' ?>,
            baseUrl:   <?= json_encode(config('app.url')) ?>,
            isLoggedIn:<?= \App\Core\Session::isLoggedIn() ? 'true' : 'false' ?>
        };
    </script>
</body>
</html>
