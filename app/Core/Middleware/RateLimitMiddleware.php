<?php

declare(strict_types=1);

namespace App\Core\Middleware;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;

/**
 * RateLimitMiddleware — Giới hạn số request theo IP
 *
 * Dùng session để đếm request (không cần Redis/Memcached).
 * Phù hợp cho: login, forgot-password, checkout.
 *
 * Cách dùng:
 *  $router->post('/login', [...], [RateLimitMiddleware::login()]);
 *
 * Vì PHP không cho truyền constructor args qua middleware class name,
 * ta dùng factory method tạo anonymous class.
 */
final class RateLimitMiddleware
{
    private int    $maxAttempts;
    private int    $decaySeconds;
    private string $key;

    public function __construct(int $maxAttempts = 10, int $decaySeconds = 60, string $key = 'global')
    {
        $this->maxAttempts  = $maxAttempts;
        $this->decaySeconds = $decaySeconds;
        $this->key          = 'rate_limit_' . $key . '_' . request_ip_hash();
    }

    public function handle(Request $request, Response $response, callable $next): void
    {
        $now      = time();
        $data     = Session::get($this->key, ['count' => 0, 'reset_at' => $now + $this->decaySeconds]);

        // Reset nếu window đã hết
        if ($now >= $data['reset_at']) {
            $data = ['count' => 0, 'reset_at' => $now + $this->decaySeconds];
        }

        if ($data['count'] >= $this->maxAttempts) {
            $retryAfter = $data['reset_at'] - $now;

            if ($request->wantsJson()) {
                $response
                    ->header('Retry-After', (string)$retryAfter)
                    ->error(
                        "Quá nhiều yêu cầu. Vui lòng thử lại sau {$retryAfter} giây.",
                        [],
                        429
                    )
                    ->send();
            } else {
                Session::flash('error', "Quá nhiều lần thử. Vui lòng đợi {$retryAfter} giây.");
                $response->redirect($request->header('Referer', base_url('/')))->send();
            }
            return;
        }

        $data['count']++;
        Session::set($this->key, $data);

        $next();
    }

    // ─── Factory Methods ──────────────────────────────────────────────────────

    /**
     * Tạo middleware cho login (5 lần / 5 phút).
     */
    public static function forLogin(): self
    {
        $cfg = config('app.rate_limit');
        return new self($cfg['login'], $cfg['login_decay'], 'login');
    }

    /**
     * Tạo middleware cho checkout (10 lần / 1 phút).
     */
    public static function forCheckout(): self
    {
        $cfg = config('app.rate_limit');
        return new self($cfg['checkout'], $cfg['window'], 'checkout');
    }
}

// ─── Helper: hash IP để dùng làm session key ─────────────────────────────────
function request_ip_hash(): string
{
    return substr(hash('sha256', $_SERVER['REMOTE_ADDR'] ?? ''), 0, 16);
}
