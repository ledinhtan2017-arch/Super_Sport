<?php

declare(strict_types=1);

/**
 * SPORTZONE — Front Controller
 * Tất cả HTTP request đều đi qua đây.
 * Document root phải trỏ vào thư mục public/ này.
 */

// ── Constants ─────────────────────────────────────────────────────────────────
define('BASE_PATH',    dirname(__DIR__));   // /path/to/sportzone
define('START_TIME',   microtime(true));
define('START_MEMORY', memory_get_usage(true));

// ── Load .env ────────────────────────────────────────────────────────────────
require_once BASE_PATH . '/app/Core/Env.php';
\App\Core\Env::load(BASE_PATH . '/.env');

// ── Autoloader ───────────────────────────────────────────────────────────────
$autoloader = BASE_PATH . '/vendor/autoload.php';
if (!file_exists($autoloader)) {
    http_response_code(503);
    header('Content-Type: text/plain; charset=utf-8');
    echo "Dependencies chưa được cài đặt.\nChạy lệnh: composer install";
    exit(1);
}
require_once $autoloader;

// ── Boot ─────────────────────────────────────────────────────────────────────
$app = new \App\Core\App();
$app->boot();
