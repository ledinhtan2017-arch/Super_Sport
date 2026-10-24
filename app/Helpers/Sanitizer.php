<?php

declare(strict_types=1);

namespace App\Helpers;

/**
 * Sanitizer — Làm sạch output để chống XSS
 *
 * Quy tắc: KHÔNG bao giờ echo biến user-input trực tiếp.
 * Luôn dùng e() hoặc Sanitizer::html() trước khi render.
 */
final class Sanitizer
{
    /**
     * Escape HTML entities — dùng cho mọi output trong HTML context.
     */
    public static function html(mixed $value): string
    {
        return htmlspecialchars((string)$value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    /**
     * Escape cho JSON context (đặt trong <script>).
     */
    public static function json(mixed $value): string
    {
        return json_encode($value, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE);
    }

    /**
     * Làm sạch URL — chỉ cho phép http/https.
     */
    public static function url(string $url): string
    {
        $clean = filter_var(trim($url), FILTER_SANITIZE_URL);
        if ($clean === false) return '#';
        $scheme = parse_url($clean, PHP_URL_SCHEME);
        if (!in_array($scheme, ['http', 'https', null], true)) return '#';
        return $clean;
    }

    /**
     * Loại bỏ HTML tags, chỉ giữ plain text.
     */
    public static function strip(mixed $value): string
    {
        return strip_tags((string)$value);
    }

    /**
     * Trim + strip + escape — cho input một dòng.
     */
    public static function input(mixed $value): string
    {
        return self::html(trim(self::strip((string)$value)));
    }

    /**
     * Làm sạch số nguyên.
     */
    public static function int(mixed $value, int $default = 0): int
    {
        $filtered = filter_var($value, FILTER_VALIDATE_INT);
        return $filtered !== false ? $filtered : $default;
    }

    /**
     * Làm sạch số thực.
     */
    public static function float(mixed $value, float $default = 0.0): float
    {
        $filtered = filter_var($value, FILTER_VALIDATE_FLOAT);
        return $filtered !== false ? $filtered : $default;
    }

    /**
     * Slug: chỉ cho phép a-z, 0-9, dấu gạch.
     */
    public static function slug(string $value): string
    {
        $value = mb_strtolower(trim($value));
        // Chuyển ký tự có dấu sang không dấu
        $value = self::removeAccents($value);
        $value = preg_replace('/[^a-z0-9\s-]/', '', $value);
        $value = preg_replace('/[\s-]+/', '-', $value);
        return trim($value, '-');
    }

    /**
     * Loại bỏ dấu tiếng Việt.
     */
    public static function removeAccents(string $text): string
    {
        $map = [
            'à'=>'a','á'=>'a','â'=>'a','ã'=>'a','ä'=>'a','å'=>'a','æ'=>'ae',
            'ç'=>'c','è'=>'e','é'=>'e','ê'=>'e','ë'=>'e','ì'=>'i','í'=>'i',
            'î'=>'i','ï'=>'i','ð'=>'d','ñ'=>'n','ò'=>'o','ó'=>'o','ô'=>'o',
            'õ'=>'o','ö'=>'o','ø'=>'o','ù'=>'u','ú'=>'u','û'=>'u','ü'=>'u',
            'ý'=>'y','þ'=>'b','ÿ'=>'y',
            // Tiếng Việt
            'à'=>'a','á'=>'a','â'=>'a','ã'=>'a','ă'=>'a','ắ'=>'a','ặ'=>'a',
            'ầ'=>'a','ấ'=>'a','ậ'=>'a','ẩ'=>'a','ẫ'=>'a','ằ'=>'a','ẳ'=>'a',
            'ẵ'=>'a','ả'=>'a','ạ'=>'a','đ'=>'d','è'=>'e','é'=>'e','ê'=>'e',
            'ề'=>'e','ế'=>'e','ệ'=>'e','ể'=>'e','ễ'=>'e','ẹ'=>'e','ẻ'=>'e',
            'ẽ'=>'e','ì'=>'i','í'=>'i','ị'=>'i','ỉ'=>'i','ĩ'=>'i','ò'=>'o',
            'ó'=>'o','ô'=>'o','ồ'=>'o','ố'=>'o','ộ'=>'o','ổ'=>'o','ỗ'=>'o',
            'ơ'=>'o','ờ'=>'o','ớ'=>'o','ợ'=>'o','ở'=>'o','ỡ'=>'o','ọ'=>'o',
            'ỏ'=>'o','õ'=>'o','ù'=>'u','ú'=>'u','ư'=>'u','ừ'=>'u','ứ'=>'u',
            'ự'=>'u','ử'=>'u','ữ'=>'u','ụ'=>'u','ủ'=>'u','ũ'=>'u','ỳ'=>'y',
            'ý'=>'y','ỵ'=>'y','ỷ'=>'y','ỹ'=>'y',
        ];
        return mb_strtolower(str_replace(array_keys($map), array_values($map), $text));
    }
}
