<?php

declare(strict_types=1);

namespace App\Core;

use App\Core\Middleware\AdminMiddleware;
use App\Core\Middleware\CsrfMiddleware;
use App\Core\Middleware\AuthMiddleware;
use Throwable;

/**
 * App — Application Bootstrap
 *
 * Thứ tự boot:
 *  1. Đăng ký error/exception handlers
 *  2. Khởi động session
 *  3. Tạo Request + Response
 *  4. Load routes
 *  5. Dispatch
 */
final class App
{
    private Router   $router;
    private Request  $request;
    private Response $response;

    public function __construct()
    {
        $this->router   = new Router();
        $this->request  = new Request();
        $this->response = new Response();
    }

    public function boot(): void
    {
        $this->registerErrorHandlers();
        Session::start();
        $this->loadRoutes();
        $this->router->dispatch($this->request, $this->response);
    }

    // ─── Error Handling ───────────────────────────────────────────────────────

    private function registerErrorHandlers(): void
    {
        $debug = config('app.debug', false);

        // PHP errors → exception
        set_error_handler(function (int $errno, string $errstr, string $errfile, int $errline): bool {
            if (!(error_reporting() & $errno)) {
                return false;
            }
            throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
        });

        // Uncaught exception handler
        set_exception_handler(function (Throwable $e) use ($debug): void {
            $code = method_exists($e, 'getCode') ? (int)$e->getCode() : 500;
            $status = ($code >= 400 && $code < 600) ? $code : 500;

            Logger::error($e->getMessage(), [
                'file'  => $e->getFile(),
                'line'  => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            if ($this->request->wantsJson()) {
                $message = $debug
                    ? $e->getMessage()
                    : ($status === 404 ? 'Không tìm thấy tài nguyên.' : 'Đã có lỗi xảy ra.');

                $this->response
                    ->error($message, [], $status)
                    ->send();
            } else {
                $this->response
                    ->status($status)
                    ->view('errors/' . ($status === 404 ? '404' : '500'), [
                        'debug'     => $debug,
                        'message'   => $debug ? $e->getMessage() : 'Đã có lỗi xảy ra. Vui lòng thử lại.',
                        'exception' => $debug ? $e : null,
                    ], 'none')
                    ->send();
            }
        });

        // Fatal error handler
        register_shutdown_function(function () use ($debug): void {
            $error = error_get_last();
            if ($error !== null && in_array($error['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE], true)) {
                Logger::error('Fatal error: ' . $error['message'], [
                    'file' => $error['file'],
                    'line' => $error['line'],
                ]);

                if (!headers_sent()) {
                    http_response_code(500);
                }

                if ($debug) {
                    echo '<pre>Fatal: ' . htmlspecialchars($error['message']) . '</pre>';
                } else {
                    echo 'Đã có lỗi nghiêm trọng. Vui lòng liên hệ hỗ trợ.';
                }
            }
        });
    }

    // ─── Route Loading ────────────────────────────────────────────────────────

    private function loadRoutes(): void
    {
        $router = $this->router;

        // Web routes
        require BASE_PATH . '/routes/web.php';

        // API routes
        require BASE_PATH . '/routes/api.php';
    }
}
