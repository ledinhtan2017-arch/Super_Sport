<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Response — HTTP Response Builder
 *
 * Xây dựng response (HTML view hoặc JSON API) và gửi đi.
 * Tất cả output phải đi qua đây — không dùng echo trực tiếp trong controller.
 */
final class Response
{
    private int    $statusCode = 200;
    private array  $headers    = [];
    private string $body       = '';
    private bool   $sent       = false;

    // ─── Status ───────────────────────────────────────────────────────────────

    public function status(int $code): static
    {
        $this->statusCode = $code;
        return $this;
    }

    public function getStatus(): int
    {
        return $this->statusCode;
    }

    // ─── Headers ──────────────────────────────────────────────────────────────

    public function header(string $name, string $value): static
    {
        $this->headers[$name] = $value;
        return $this;
    }

    public function noCache(): static
    {
        return $this
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate')
            ->header('Pragma', 'no-cache');
    }

    // ─── JSON Response ────────────────────────────────────────────────────────

    /**
     * Trả về JSON response chuẩn.
     *
     * @param array|object $data
     */
    public function json(mixed $data, int $status = 200): static
    {
        $this->statusCode = $status;
        $this->header('Content-Type', 'application/json; charset=utf-8');
        $this->body = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        return $this;
    }

    /**
     * Chuẩn JSON thành công: {success: true, data: ...}
     */
    public function success(mixed $data = null, string $message = '', int $status = 200): static
    {
        return $this->json([
            'success' => true,
            'message' => $message,
            'data'    => $data,
        ], $status);
    }

    /**
     * Chuẩn JSON lỗi: {success: false, message: ..., errors: ...}
     */
    public function error(string $message, array $errors = [], int $status = 400): static
    {
        return $this->json([
            'success' => false,
            'message' => $message,
            'errors'  => $errors,
        ], $status);
    }

    // ─── HTML / View Response ─────────────────────────────────────────────────

    public function html(string $content, int $status = 200): static
    {
        $this->statusCode = $status;
        $this->header('Content-Type', 'text/html; charset=utf-8');
        $this->body = $content;
        return $this;
    }

    /**
     * Render view PHP vào layout.
     *
     * @param string $view  Đường dẫn tương đối từ app/Views/, vd: 'home/index'
     * @param array  $data  Biến truyền vào view
     * @param string $layout 'main' | 'admin' | 'none'
     */
    public function view(string $view, array $data = [], string $layout = 'main'): static
    {
        $this->header('Content-Type', 'text/html; charset=utf-8');
        $this->body = $this->renderView($view, $data, $layout);
        return $this;
    }

    // ─── Redirect ─────────────────────────────────────────────────────────────

    public function redirect(string $url, int $status = 302): static
    {
        $this->statusCode = $status;
        $this->header('Location', $url);
        $this->body = '';
        return $this;
    }

    public function redirectBack(string $fallback = '/'): static
    {
        $url = $_SERVER['HTTP_REFERER'] ?? $fallback;
        return $this->redirect($url);
    }

    // ─── Send ─────────────────────────────────────────────────────────────────

    /**
     * Gửi response: headers + body.
     * Chỉ gọi một lần, lần thứ hai là no-op.
     */
    public function send(): void
    {
        if ($this->sent) {
            return;
        }
        $this->sent = true;

        http_response_code($this->statusCode);

        foreach ($this->headers as $name => $value) {
            header("{$name}: {$value}");
        }

        echo $this->body;
    }

    // ─── Private: View Rendering ──────────────────────────────────────────────

    private function renderView(string $view, array $data, string $layout): string
    {
        $viewPath = BASE_PATH . '/app/Views/' . str_replace('.', '/', $view) . '.php';

        if (!file_exists($viewPath)) {
            throw new \RuntimeException("View không tồn tại: {$view}");
        }

        // Extract data vào scope của view
        extract($data, EXTR_SKIP);

        // Buffer view content
        ob_start();
        try {
            require $viewPath;
        } catch (\Throwable $e) {
            ob_end_clean();
            throw $e;
        }
        $content = ob_get_clean();

        if ($layout === 'none') {
            return $content;
        }

        // Wrap trong layout
        $layoutPath = BASE_PATH . '/app/Views/layouts/' . $layout . '.php';
        if (!file_exists($layoutPath)) {
            throw new \RuntimeException("Layout không tồn tại: {$layout}");
        }

        ob_start();
        try {
            require $layoutPath;
        } catch (\Throwable $e) {
            ob_end_clean();
            throw $e;
        }
        return ob_get_clean();
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    public function getBody(): string
    {
        return $this->body;
    }

    public function isSent(): bool
    {
        return $this->sent;
    }
}
