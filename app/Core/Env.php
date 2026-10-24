<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Env — Đọc và parse file .env
 *
 * Hỗ trợ:
 *  - KEY=VALUE
 *  - KEY="quoted value"
 *  - KEY='single quoted'
 *  - # comment lines
 *  - Empty lines ignored
 *  - Không expose vào $_SERVER để tránh leak qua phpinfo()
 */
final class Env
{
    private static bool $loaded = false;

    /**
     * Load file .env vào $_ENV và putenv()
     * Chỉ load một lần, gọi nhiều lần là no-op.
     */
    public static function load(string $path): void
    {
        if (self::$loaded) {
            return;
        }

        if (!is_file($path) || !is_readable($path)) {
            // Cho phép chạy không có .env (dùng giá trị mặc định)
            // Nhưng trong production không nên thiếu .env
            return;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            $line = trim($line);

            // Bỏ qua comment và dòng trống
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            // Tách KEY=VALUE
            if (!str_contains($line, '=')) {
                continue;
            }

            [$key, $value] = explode('=', $line, 2);
            $key   = trim($key);
            $value = trim($value);

            // Bỏ qua key rỗng
            if ($key === '') {
                continue;
            }

            // Xử lý quoted value: "value" hoặc 'value'
            $value = self::parseValue($value);

            // Không ghi đè biến đã có trong environment (Docker/system env có priority)
            if (!isset($_ENV[$key]) && getenv($key) === false) {
                $_ENV[$key] = $value;
                putenv("{$key}={$value}");
            }
        }

        self::$loaded = true;
    }

    /**
     * Lấy giá trị env, trả về $default nếu không tồn tại.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        return $_ENV[$key] ?? getenv($key) ?: $default;
    }

    /**
     * Lấy boolean env value.
     */
    public static function bool(string $key, bool $default = false): bool
    {
        $value = self::get($key);
        if ($value === null) {
            return $default;
        }
        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Lấy integer env value.
     */
    public static function int(string $key, int $default = 0): int
    {
        return (int)(self::get($key) ?? $default);
    }

    // ─── Private ─────────────────────────────────────────────────────────────

    private static function parseValue(string $value): string
    {
        // Loại bỏ inline comment: KEY=value # comment
        if (str_contains($value, ' #')) {
            $value = trim(explode(' #', $value, 2)[0]);
        }

        // Xử lý quoted strings
        $len = strlen($value);
        if ($len >= 2) {
            $first = $value[0];
            $last  = $value[$len - 1];

            if (($first === '"' && $last === '"') || ($first === "'" && $last === "'")) {
                $value = substr($value, 1, $len - 2);

                // Xử lý escape sequences trong double-quoted
                if ($first === '"') {
                    $value = str_replace(['\\n', '\\r', '\\t', '\\"', '\\\\'], ["\n", "\r", "\t", '"', '\\'], $value);
                }
            }
        }

        return $value;
    }
}
