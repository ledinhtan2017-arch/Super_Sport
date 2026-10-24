<?php

declare(strict_types=1);

namespace App\Helpers;

use App\Core\Session;

/**
 * CsrfToken — Tạo và xác thực CSRF token
 *
 * Token được lưu trong session và so sánh bằng hash_equals()
 * để chống timing attack.
 */
final class CsrfToken
{
    private const SESSION_KEY = '_csrf_token';
    private const TOKEN_LENGTH = 32; // bytes → 64 hex chars

    /**
     * Lấy token hiện tại (tạo mới nếu chưa có).
     */
    public static function get(): string
    {
        if (!Session::has(self::SESSION_KEY)) {
            self::regenerate();
        }
        return Session::get(self::SESSION_KEY);
    }

    /**
     * Tạo token mới và lưu vào session.
     */
    public static function regenerate(): string
    {
        $token = bin2hex(random_bytes(self::TOKEN_LENGTH));
        Session::set(self::SESSION_KEY, $token);
        return $token;
    }

    /**
     * Kiểm tra token hợp lệ (timing-safe comparison).
     */
    public static function validate(?string $token): bool
    {
        if ($token === null || $token === '') {
            return false;
        }

        $stored = Session::get(self::SESSION_KEY);

        if ($stored === null || $stored === '') {
            return false;
        }

        return hash_equals($stored, $token);
    }

    /**
     * Trả về HTML hidden input chứa CSRF token.
     * Dùng trong mọi form POST.
     */
    public static function field(): string
    {
        $token = self::get();
        return '<input type="hidden" name="_csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }

    /**
     * Trả về token dưới dạng meta tag (cho AJAX).
     */
    public static function meta(): string
    {
        $token = self::get();
        return '<meta name="csrf-token" content="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }
}
