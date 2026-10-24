<?php

declare(strict_types=1);

/**
 * Global Helper Functions
 * Autoloaded qua composer (files[]) — available toàn ứng dụng.
 */

use App\Core\Logger;
use App\Helpers\CsrfToken;
use App\Helpers\Sanitizer;

// ─── Config ───────────────────────────────────────────────────────────────────

/**
 * Đọc config theo dot notation.
 * @example config('app.debug'), config('database.host')
 */
function config(string $key, mixed $default = null): mixed
{
    static $configs = [];

    [$file, $rest] = array_pad(explode('.', $key, 2), 2, null);

    if (!isset($configs[$file])) {
        $path = BASE_PATH . '/config/' . $file . '.php';
        if (!file_exists($path)) {
            return $default;
        }
        $configs[$file] = require $path;
    }

    if ($rest === null) {
        return $configs[$file] ?? $default;
    }

    // Hỗ trợ nested key: 'app.session.name'
    $data = $configs[$file];
    foreach (explode('.', $rest) as $segment) {
        if (!is_array($data) || !array_key_exists($segment, $data)) {
            return $default;
        }
        $data = $data[$segment];
    }
    return $data;
}

// ─── URL Helpers ──────────────────────────────────────────────────────────────

/**
 * Tạo URL tuyệt đối từ path.
 * @example base_url('/shop') → http://localhost/sportzone/public/shop
 */
function base_url(string $path = ''): string
{
    $base = rtrim(config('app.url', ''), '/');
    $path = '/' . ltrim($path, '/');
    return $base . $path;
}

/**
 * Tạo URL asset (CSS, JS, ảnh).
 * @example asset('css/main.css') → http://.../assets/css/main.css
 */
function asset(string $path): string
{
    return base_url('/assets/' . ltrim($path, '/'));
}

// ─── Output / Security ────────────────────────────────────────────────────────

/**
 * Escape HTML entities — shorthand cho Sanitizer::html().
 * Dùng trong view: <?= e($variable) ?>
 */
function e(mixed $value): string
{
    return Sanitizer::html($value);
}

/**
 * Tạo CSRF hidden input.
 */
function csrf_field(): string
{
    return CsrfToken::field();
}

/**
 * Lấy CSRF token string.
 */
function csrf_token(): string
{
    return CsrfToken::get();
}

// ─── Flash Messages ───────────────────────────────────────────────────────────

/**
 * Lưu flash message.
 */
function flash(string $type, string $message): void
{
    \App\Core\Session::flash($type, $message);
}

// ─── Format Helpers ───────────────────────────────────────────────────────────

/**
 * Format số tiền theo định dạng VND.
 * @example format_price(2450000) → "2.450.000đ"
 */
function format_price(int|float $amount): string
{
    return number_format((int)$amount, 0, ',', '.') . 'đ';
}

/**
 * Tính phần trăm giảm giá.
 * @example discount_percent(3500000, 2800000) → 20
 */
function discount_percent(int|float $original, int|float $sale): int
{
    if ($original <= 0) return 0;
    return (int)round((($original - $sale) / $original) * 100);
}

/**
 * Rút gọn text theo số ký tự.
 */
function truncate(string $text, int $length = 100, string $suffix = '...'): string
{
    $text = strip_tags($text);
    if (mb_strlen($text) <= $length) return $text;
    return mb_substr($text, 0, $length) . $suffix;
}

/**
 * Format ngày giờ theo locale VN.
 */
function format_date(string $datetime, string $format = 'd/m/Y'): string
{
    try {
        $dt = new DateTimeImmutable($datetime);
        return $dt->format($format);
    } catch (\Throwable) {
        return $datetime;
    }
}

/**
 * Format ngày giờ đầy đủ.
 */
function format_datetime(string $datetime): string
{
    return format_date($datetime, 'd/m/Y H:i');
}

// ─── Misc ─────────────────────────────────────────────────────────────────────

/**
 * dd() — Dump and Die (chỉ dùng khi debug).
 */
function dd(mixed ...$vars): never
{
    header('Content-Type: text/html; charset=utf-8');
    echo '<pre style="background:#1a1a2e;color:#e2e2e2;padding:16px;font-size:13px;border-radius:8px;">';
    foreach ($vars as $var) {
        var_dump($var);
        echo PHP_EOL;
    }
    echo '</pre>';
    exit(1);
}

/**
 * Tạo slug từ string.
 */
function str_slug(string $text): string
{
    return Sanitizer::slug($text);
}

/**
 * Kiểm tra request đến từ CLI.
 */
function is_cli(): bool
{
    return PHP_SAPI === 'cli';
}
