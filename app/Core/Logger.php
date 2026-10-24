<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Logger — Ghi log file đơn giản
 *
 * Log được lưu vào storage/logs/app-YYYY-MM-DD.log
 * Không dùng error_log() mặc định để tránh ghi vào log của web server
 */
final class Logger
{
    private const LOG_DIR = BASE_PATH . '/storage/logs';

    public static function info(string $message, array $context = []): void
    {
        self::write('INFO', $message, $context);
    }

    public static function warning(string $message, array $context = []): void
    {
        self::write('WARNING', $message, $context);
    }

    public static function error(string $message, array $context = []): void
    {
        self::write('ERROR', $message, $context);
    }

    public static function debug(string $message, array $context = []): void
    {
        if (config('app.debug')) {
            self::write('DEBUG', $message, $context);
        }
    }

    // ─── Private ─────────────────────────────────────────────────────────────

    private static function write(string $level, string $message, array $context): void
    {
        $logFile = self::LOG_DIR . '/app-' . date('Y-m-d') . '.log';
        $timestamp = date('Y-m-d H:i:s');
        $contextStr = empty($context) ? '' : ' ' . json_encode($context, JSON_UNESCAPED_UNICODE);
        $line = "[{$timestamp}] [{$level}] {$message}{$contextStr}" . PHP_EOL;

        // Tạo thư mục nếu chưa có
        if (!is_dir(self::LOG_DIR)) {
            mkdir(self::LOG_DIR, 0755, true);
        }

        file_put_contents($logFile, $line, FILE_APPEND | LOCK_EX);
    }
}
