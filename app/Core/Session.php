<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Session — Quản lý PHP Session an toàn
 *
 * Tính năng:
 *  - Cấu hình bảo mật cookie (HttpOnly, Secure, SameSite)
 *  - session_regenerate_id() sau khi đăng nhập
 *  - Flash messages (tự xóa sau lần đọc đầu tiên)
 *  - Ngăn session fixation
 */
final class Session
{
    private static bool $started = false;

    /**
     * Khởi động session với cấu hình bảo mật.
     * Gọi một lần duy nhất trong App::boot().
     */
    public static function start(): void
    {
        if (self::$started || session_status() === PHP_SESSION_ACTIVE) {
            self::$started = true;
            return;
        }

        $config = config('app.session');

        session_name($config['name']);

        session_set_cookie_params([
            'lifetime' => 0,                    // Session cookie (hết khi đóng browser)
            'path'     => '/',
            'domain'   => '',
            'secure'   => $config['secure'],    // true khi HTTPS
            'httponly' => $config['http_only'],  // Chặn JS truy cập cookie
            'samesite' => $config['same_site'], // Chặn CSRF cross-site
        ]);

        // Không lưu session_id trong URL
        ini_set('session.use_only_cookies', '1');
        ini_set('session.use_strict_mode', '1');

        session_start();
        self::$started = true;
    }

    // ─── Get / Set / Has / Delete ─────────────────────────────────────────────

    public static function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    public static function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    public static function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    public static function delete(string $key): void
    {
        unset($_SESSION[$key]);
    }

    public static function all(): array
    {
        return $_SESSION ?? [];
    }

    // ─── Auth Helpers ─────────────────────────────────────────────────────────

    /**
     * Lưu thông tin user sau khi đăng nhập thành công.
     * Bắt buộc gọi session_regenerate_id để chống session fixation.
     */
    public static function login(array $user): void
    {
        // Regenerate session ID để ngăn session fixation attack
        session_regenerate_id(true);

        $_SESSION['auth'] = [
            'id'    => $user['id'],
            'name'  => $user['name'],
            'email' => $user['email'],
            'role'  => $user['role'],
        ];
    }

    /**
     * Xóa session và cookie để đăng xuất.
     */
    public static function logout(): void
    {
        $_SESSION = [];

        // Xóa cookie session
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_destroy();
        self::$started = false;
    }

    public static function isLoggedIn(): bool
    {
        return isset($_SESSION['auth']['id']);
    }

    public static function user(): ?array
    {
        return $_SESSION['auth'] ?? null;
    }

    public static function userId(): ?int
    {
        return isset($_SESSION['auth']['id']) ? (int)$_SESSION['auth']['id'] : null;
    }

    public static function userRole(): ?string
    {
        return $_SESSION['auth']['role'] ?? null;
    }

    public static function isAdmin(): bool
    {
        return ($_SESSION['auth']['role'] ?? '') === 'admin';
    }

    // ─── Flash Messages ───────────────────────────────────────────────────────

    /**
     * Lưu flash message — tự xóa sau lần đọc đầu tiên.
     * type: success | error | warning | info
     */
    public static function flash(string $type, string $message): void
    {
        $_SESSION['_flash'][$type][] = $message;
    }

    /**
     * Lấy và xóa tất cả flash messages.
     */
    public static function getFlash(?string $type = null): mixed
    {
        if ($type !== null) {
            $value = $_SESSION['_flash'][$type] ?? null;
            unset($_SESSION['_flash'][$type]);
            return $value;
        }

        $flash = $_SESSION['_flash'] ?? [];
        unset($_SESSION['_flash']);
        return $flash;
    }
    public static function peekFlash(string $type): array
    {
        return $_SESSION['_flash'][$type] ?? [];
    }

    // ─── Previous URL (cho redirect after login) ──────────────────────────────

    public static function setPreviousUrl(string $url): void
    {
        $_SESSION['_previous_url'] = $url;
    }

    public static function getPreviousUrl(string $default = '/'): string
    {
        $url = $_SESSION['_previous_url'] ?? $default;
        unset($_SESSION['_previous_url']);
        return $url;
    }
}
