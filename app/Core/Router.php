<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Router — URL Dispatcher
 *
 * Hỗ trợ:
 *  - GET, POST, PUT, PATCH, DELETE
 *  - Named params: /product/{slug}, /admin/orders/{id:\d+}
 *  - Middleware per-route hoặc per-group
 *  - Route groups với prefix và middleware
 *  - Handler dạng [Controller::class, 'method'] hoặc Closure
 *  - Trả về 404 / 405 khi không khớp
 */
final class Router
{
    /** @var array<array{method:string, pattern:string, handler:mixed, middleware:string[]}> */
    private array $routes = [];

    /** Stack prefix + middleware của group hiện tại */
    private string $currentPrefix     = '';
    private array  $currentMiddleware = [];

    // ─── Route Registration ───────────────────────────────────────────────────

    public function get(string $path, mixed $handler, array $middleware = []): void
    {
        $this->addRoute('GET', $path, $handler, $middleware);
    }

    public function post(string $path, mixed $handler, array $middleware = []): void
    {
        $this->addRoute('POST', $path, $handler, $middleware);
    }

    public function put(string $path, mixed $handler, array $middleware = []): void
    {
        $this->addRoute('PUT', $path, $handler, $middleware);
    }

    public function patch(string $path, mixed $handler, array $middleware = []): void
    {
        $this->addRoute('PATCH', $path, $handler, $middleware);
    }

    public function delete(string $path, mixed $handler, array $middleware = []): void
    {
        $this->addRoute('DELETE', $path, $handler, $middleware);
    }

    /**
     * Đăng ký một route với nhiều HTTP methods.
     */
    public function match(array $methods, string $path, mixed $handler, array $middleware = []): void
    {
        foreach ($methods as $method) {
            $this->addRoute(strtoupper($method), $path, $handler, $middleware);
        }
    }

    /**
     * Nhóm route với prefix và/hoặc middleware chung.
     *
     * @example
     * $router->group(['prefix' => '/admin', 'middleware' => ['admin']], function ($r) {
     *     $r->get('/dashboard', [DashboardController::class, 'index']);
     * });
     */
    public function group(array $attributes, callable $callback): void
    {
        $previousPrefix     = $this->currentPrefix;
        $previousMiddleware = $this->currentMiddleware;

        $this->currentPrefix    .= ($attributes['prefix'] ?? '');
        $this->currentMiddleware = array_merge(
            $this->currentMiddleware,
            $attributes['middleware'] ?? []
        );

        $callback($this);

        // Restore sau khi group kết thúc
        $this->currentPrefix     = $previousPrefix;
        $this->currentMiddleware = $previousMiddleware;
    }

    // ─── Dispatch ─────────────────────────────────────────────────────────────

    /**
     * Tìm route khớp và dispatch đến handler.
     * Chạy middleware pipeline trước khi gọi controller.
     */
    public function dispatch(Request $request, Response $response): void
    {
        $method  = $request->method();
        $path    = $request->path();

        $matchedMethods = []; // Để trả 405 chính xác

        foreach ($this->routes as $route) {
            $params = [];
            if (!$this->matchPath($route['pattern'], $path, $params)) {
                continue;
            }

            $matchedMethods[] = $route['method'];

            if ($route['method'] !== $method) {
                continue;
            }

            // Khớp cả method lẫn path
            $request->setRouteParams($params);

            // Chạy middleware pipeline
            $this->runMiddleware(
                $route['middleware'],
                $request,
                $response,
                fn() => $this->callHandler($route['handler'], $request, $response)
            );

            return;
        }

        // 405 — Method Not Allowed
        if (!empty($matchedMethods)) {
            $response
                ->header('Allow', implode(', ', array_unique($matchedMethods)))
                ->error('Phương thức không được hỗ trợ.', [], 405)
                ->send();
            return;
        }

        // 404 — Not Found
        $this->notFound($request, $response);
    }

    // ─── Private: Core Logic ──────────────────────────────────────────────────

    private function addRoute(string $method, string $path, mixed $handler, array $middleware): void
    {
        $fullPath       = $this->currentPrefix . $path;
        $fullMiddleware = array_merge($this->currentMiddleware, $middleware);

        $this->routes[] = [
            'method'     => $method,
            'pattern'    => $this->pathToRegex($fullPath),
            'raw_path'   => $fullPath,
            'handler'    => $handler,
            'middleware' => $fullMiddleware,
        ];
    }

    /**
     * Convert /product/{slug} → named group regex.
     * Hỗ trợ pattern tùy chỉnh: {id:\d+}
     */
    private function pathToRegex(string $path): string
    {
        // Escape các ký tự regex đặc biệt trừ {}
        $escaped = preg_replace('/[.+*?^$[\]()|\\\\]/', '\\\\$0', $path);

        // Thay thế {name} hoặc {name:pattern}
        $regex = preg_replace_callback(
            '/\{(\w+)(?::([^}]+))?\}/',
            function (array $matches): string {
                $name    = $matches[1];
                $pattern = $matches[2] ?? '[^\/]+';
                return "(?P<{$name}>{$pattern})";
            },
            $escaped
        );

        return '#^' . $regex . '$#u';
    }

    /**
     * So khớp path với pattern, trích xuất named params.
     */
    private function matchPath(string $pattern, string $path, array &$params): bool
    {
        if (!preg_match($pattern, $path, $matches)) {
            return false;
        }

        // Chỉ lấy named captures (bỏ qua numeric keys)
        $params = array_filter(
            $matches,
            fn($key) => is_string($key),
            ARRAY_FILTER_USE_KEY
        );

        return true;
    }

    /**
     * Chạy middleware pipeline (nested callable chain).
     */
    private function runMiddleware(array $middleware, Request $req, Response $res, callable $final): void
    {
        $pipeline = array_reduce(
            array_reverse($middleware),
            function (callable $carry, string $middlewareClass) use ($req, $res): callable {
                return function () use ($middlewareClass, $req, $res, $carry): void {
                    $instance = new $middlewareClass();
                    $instance->handle($req, $res, $carry);
                };
            },
            $final
        );

        $pipeline();
    }

    /**
     * Gọi controller method hoặc Closure.
     */
    private function callHandler(mixed $handler, Request $req, Response $res): void
    {
        if ($handler instanceof \Closure) {
            $handler($req, $res);
            return;
        }

        if (is_array($handler) && count($handler) === 2) {
            [$class, $method] = $handler;
            $controller = new $class();
            $controller->$method($req, $res);
            return;
        }

        if (is_string($handler) && str_contains($handler, '@')) {
            [$class, $method] = explode('@', $handler, 2);
            $controller = new $class();
            $controller->$method($req, $res);
            return;
        }

        throw new \InvalidArgumentException('Handler không hợp lệ: ' . json_encode($handler));
    }

    private function notFound(Request $req, Response $res): void
    {
        if ($req->wantsJson()) {
            $res->error('Không tìm thấy trang yêu cầu.', [], 404)->send();
        } else {
            $res->status(404)->view('errors/404', [], 'none')->send();
        }
    }
}
