<?php

declare(strict_types=1);

namespace App\Core;



/**
 * Request — HTTP Request Wrapper
 *
 * Bọc $_SERVER, $_GET, $_POST, $_FILES thành object thuần.
 * Cung cấp helper an toàn để đọc input, không expose superglobals trực tiếp.
 */
final class Request
{
    private string $method;
    private string $uri;
    private string $path;
    private array  $queryParams;
    private array  $bodyParams;
    private array  $files;
    private array  $headers;
    private array  $routeParams = [];

    public function __construct()
    {
        // Hỗ trợ method override qua _method (PUT, DELETE qua HTML form)
        $rawMethod = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        if ($rawMethod === 'POST' && isset($_POST['_method'])) {
            $override = strtoupper(trim($_POST['_method']));
            if (in_array($override, ['PUT', 'PATCH', 'DELETE'], true)) {
                $rawMethod = $override;
            }
        }

        $this->method      = $rawMethod;
        $this->uri         = $_SERVER['REQUEST_URI'] ?? '/';
        $this->path        = $this->parsePath();
        $this->bodyParams  = $_POST  ?? [];
        $this->files       = $_FILES ?? [];
        $this->headers     = $this->parseHeaders();

        // Parse query params từ $_GET; fallback tự parse URI (CLI / test env)
        if (!empty($_GET)) {
            $this->queryParams = $_GET;
        } else {
            $qs = parse_url($this->uri, PHP_URL_QUERY) ?? '';
            parse_str((string)$qs, $parsed);
            $this->queryParams = $parsed;
        }
    }

    // ─── Method & URI ─────────────────────────────────────────────────────────

    public function method(): string
    {
        return $this->method;
    }

    public function uri(): string
    {
        return $this->uri;
    }

    public function path(): string
    {
        return $this->path;
    }

    public function isMethod(string $method): bool
    {
        return $this->method === strtoupper($method);
    }

    public function isGet(): bool    { return $this->method === 'GET'; }
    public function isPost(): bool   { return $this->method === 'POST'; }
    public function isPut(): bool    { return $this->method === 'PUT'; }
    public function isDelete(): bool { return $this->method === 'DELETE'; }

    public function isAjax(): bool
    {
        return ($this->headers['X-Requested-With'] ?? '') === 'XMLHttpRequest';
    }

    public function isJson(): bool
    {
        return str_contains($this->headers['Content-Type'] ?? '', 'application/json');
    }

    public function wantsJson(): bool
    {
        return $this->isAjax() || $this->isJson() ||
               str_contains($this->headers['Accept'] ?? '', 'application/json');
    }

    // ─── Route Params (set by Router) ────────────────────────────────────────

    public function setRouteParams(array $params): void
    {
        $this->routeParams = $params;
    }

    public function param(string $key, mixed $default = null): mixed
    {
        return $this->routeParams[$key] ?? $default;
    }

    public function params(): array
    {
        return $this->routeParams;
    }

    // ─── Query String ($_GET) ─────────────────────────────────────────────────

    public function query(string $key, mixed $default = null): mixed
    {
        return $this->queryParams[$key] ?? $default;
    }

    public function queryAll(): array
    {
        return $this->queryParams;
    }

    // ─── Body ($_POST / JSON) ─────────────────────────────────────────────────

    public function post(string $key, mixed $default = null): mixed
    {
        return $this->bodyParams[$key] ?? $default;
    }

    /**
     * Lấy tất cả body params (POST hoặc JSON).
     */
    public function body(): array
    {
        if ($this->isJson()) {
            $raw = file_get_contents('php://input');
            return json_decode($raw ?: '{}', true) ?? [];
        }
        return $this->bodyParams;
    }

    /**
     * Lấy giá trị từ body + query params gộp lại.
     */
    public function input(string $key, mixed $default = null): mixed
    {
        return $this->bodyParams[$key] ?? $this->queryParams[$key] ?? $default;
    }

    /**
     * Lấy tất cả inputs (body + query), body có priority.
     */
    public function all(): array
    {
        return array_merge($this->queryParams, $this->bodyParams);
    }

    /**
     * Lấy subset của inputs theo danh sách key.
     */
    public function only(array $keys): array
    {
        $all = $this->all();
        return array_intersect_key($all, array_flip($keys));
    }

    /**
     * Lấy tất cả inputs trừ các key trong blacklist.
     */
    public function except(array $keys): array
    {
        $all = $this->all();
        foreach ($keys as $key) {
            unset($all[$key]);
        }
        return $all;
    }

    // ─── Files ($_FILES) ─────────────────────────────────────────────────────

    public function file(string $key): ?array
    {
        $file = $this->files[$key] ?? null;
        if ($file === null || $file['error'] === UPLOAD_ERR_NO_FILE) {
            return null;
        }
        return $file;
    }

    public function hasFile(string $key): bool
    {
        return isset($this->files[$key]) && $this->files[$key]['error'] !== UPLOAD_ERR_NO_FILE;
    }

    // ─── Headers ─────────────────────────────────────────────────────────────

    public function header(string $name, ?string $default = null): ?string
    {
        return $this->headers[$name] ?? $default;
    }

    public function headers(): array
    {
        return $this->headers;
    }

    // ─── IP Address ──────────────────────────────────────────────────────────

    public function ip(): string
    {
        // Chỉ tin tưởng REMOTE_ADDR, không dùng X-Forwarded-For trực tiếp
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }

    // ─── CSRF Token ──────────────────────────────────────────────────────────

    public function csrfToken(): ?string
    {
        return $this->post('_csrf_token')
            ?? $this->header('X-CSRF-Token');
    }

    // ─── Private ─────────────────────────────────────────────────────────────

    private function parsePath(): string
    {
        $uri  = parse_url($this->uri, PHP_URL_PATH) ?? '/';
        $base = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/');
        $path = '/' . ltrim(substr($uri, strlen($base)), '/');
        return $path === '' ? '/' : $path;
    }

    private function parseHeaders(): array
    {
        $headers = [];

        if (function_exists('getallheaders')) {
            foreach (getallheaders() as $name => $value) {
                $headers[$name] = $value;
            }
            return $headers;
        }

        // Fallback nếu không có getallheaders()
        foreach ($_SERVER as $key => $value) {
            if (str_starts_with($key, 'HTTP_')) {
                $name = str_replace('_', '-', ucwords(strtolower(substr($key, 5)), '_'));
                $headers[$name] = $value;
            } elseif (in_array($key, ['CONTENT_TYPE', 'CONTENT_LENGTH'], true)) {
                $name = str_replace('_', '-', ucwords(strtolower($key), '_'));
                $headers[$name] = $value;
            }
        }

        return $headers;
    }

    /**
     * Đọc body dạng JSON (cho API endpoints).
     * Nếu Content-Type: application/json thì parse raw input.
     * Fallback về body() nếu không phải JSON.
     */
    public function json(): array
    {
        $contentType = $this->headers['Content-Type'] ?? $this->headers['content-type'] ?? '';
        if (str_contains($contentType, 'application/json')) {
            $raw = file_get_contents('php://input');
            if ($raw) {
                $decoded = json_decode($raw, true);
                return is_array($decoded) ? $decoded : [];
            }
        }
        // Fallback: form-submitted data
        return $this->body();
    }
}
