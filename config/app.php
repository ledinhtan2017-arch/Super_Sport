<?php
/**
 * App Configuration
 * Đọc từ $_ENV (đã load bởi Env::load() trong index.php)
 */

return [
    'name'    => $_ENV['APP_NAME']  ?? 'SPORTZONE',
    'env'     => $_ENV['APP_ENV']   ?? 'production',
    'debug'   => filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN),
    'url'     => $_ENV['APP_URL']   ?? 'http://localhost',
    'key'     => $_ENV['APP_KEY']   ?? '',

    // Session
    'session' => [
        'name'      => $_ENV['SESSION_NAME']      ?? 'sz_session',
        'lifetime'  => (int)($_ENV['SESSION_LIFETIME'] ?? 7200),
        'secure'    => filter_var($_ENV['SESSION_SECURE'] ?? false, FILTER_VALIDATE_BOOLEAN),
        'http_only' => filter_var($_ENV['SESSION_HTTP_ONLY'] ?? true,  FILTER_VALIDATE_BOOLEAN),
        'same_site' => $_ENV['SESSION_SAME_SITE'] ?? 'Lax',
    ],

    // Upload
    'upload' => [
        'max_size'      => (int)($_ENV['UPLOAD_MAX_SIZE'] ?? 5242880),
        'allowed_types' => explode(',', $_ENV['UPLOAD_ALLOWED_TYPES'] ?? 'jpg,jpeg,png,webp'),
        'path'          => BASE_PATH . '/storage/uploads',
    ],

    // Rate Limit
    'rate_limit' => [
        'login'          => (int)($_ENV['RATE_LIMIT_LOGIN']        ?? 5),
        'login_decay'    => (int)($_ENV['RATE_LIMIT_LOGIN_DECAY']  ?? 300),
        'checkout'       => (int)($_ENV['RATE_LIMIT_CHECKOUT']     ?? 10),
        'window'         => (int)($_ENV['RATE_LIMIT_WINDOW']       ?? 60),
    ],

    // Shipping
    'shipping' => [
        'fee_default'      => (int)($_ENV['SHIPPING_FEE_DEFAULT']       ?? 30000),
        'free_threshold'   => (int)($_ENV['SHIPPING_FREE_THRESHOLD']    ?? 500000),
    ],

    // Mail
    'mail' => [
        'host'      => $_ENV['MAIL_HOST']      ?? '',
        'port'      => (int)($_ENV['MAIL_PORT'] ?? 587),
        'username'  => $_ENV['MAIL_USERNAME']  ?? '',
        'password'  => $_ENV['MAIL_PASSWORD']  ?? '',
        'from'      => $_ENV['MAIL_FROM']      ?? 'noreply@sportzone.vn',
        'from_name' => $_ENV['MAIL_FROM_NAME'] ?? 'SPORTZONE',
        'encryption'=> $_ENV['MAIL_ENCRYPTION']?? 'tls',
    ],

    // Pagination
    'pagination' => [
        'per_page' => 16,
    ],

    // Admin pagination
    'admin_pagination' => [
        'per_page' => 20,
    ],
];
