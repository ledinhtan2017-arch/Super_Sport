<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;
use PDOStatement;
use RuntimeException;

/**
 * Database — PDO Singleton
 *
 * - Kết nối MySQL qua PDO với prepared statements bắt buộc
 * - Singleton: chỉ một kết nối trên toàn request lifecycle
 * - Hỗ trợ transaction (beginTransaction / commit / rollback)
 * - Hỗ trợ named placeholders (:name) và positional (?)
 */
final class Database
{
    private static ?Database $instance = null;
    private PDO $pdo;

    private function __construct()
    {
        $config = require BASE_PATH . '/config/database.php';

        $dsn = sprintf(
            '%s:host=%s;port=%s;dbname=%s;charset=%s',
            $config['driver'],
            $config['host'],
            $config['port'],
            $config['database'],
            $config['charset']
        );

        try {
            $this->pdo = new PDO(
                $dsn,
                $config['username'],
                $config['password'],
                $config['options']
            );
        } catch (PDOException $e) {
            // Không expose chi tiết kết nối ra response
            Logger::error('Database connection failed: ' . $e->getMessage());
            throw new RuntimeException('Không thể kết nối cơ sở dữ liệu.', 500, $e);
        }
    }

    /**
     * Lấy instance duy nhất (Singleton).
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Trả về PDO raw (dùng khi cần tính năng nâng cao).
     */
    public function getPdo(): PDO
    {
        return $this->pdo;
    }

    // ─── Query Helpers ────────────────────────────────────────────────────────

    /**
     * Thực thi câu lệnh SQL với prepared statement.
     * Trả về PDOStatement để caller xử lý tiếp nếu cần.
     */
    public function query(string $sql, array $params = []): PDOStatement
    {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            Logger::error("Query failed: {$e->getMessage()} | SQL: {$sql}");
            throw new RuntimeException('Lỗi truy vấn cơ sở dữ liệu.', 500, $e);
        }
    }

    /**
     * Lấy tất cả hàng kết quả dưới dạng mảng kết hợp.
     */
    public function fetchAll(string $sql, array $params = []): array
    {
        return $this->query($sql, $params)->fetchAll();
    }

    /**
     * Lấy một hàng kết quả đầu tiên (hoặc null nếu không có).
     */
    public function fetchOne(string $sql, array $params = []): ?array
    {
        $result = $this->query($sql, $params)->fetch();
        return $result !== false ? $result : null;
    }

    /**
     * Lấy giá trị một cột của hàng đầu tiên.
     */
    public function fetchColumn(string $sql, array $params = [], int $column = 0): mixed
    {
        $result = $this->query($sql, $params)->fetchColumn($column);
        return $result !== false ? $result : null;
    }

    /**
     * Thực thi INSERT/UPDATE/DELETE.
     * Trả về số hàng bị ảnh hưởng.
     */
    public function execute(string $sql, array $params = []): int
    {
        return $this->query($sql, $params)->rowCount();
    }

    /**
     * Trả về ID của hàng vừa INSERT.
     */
    public function lastInsertId(): int
    {
        return (int)$this->pdo->lastInsertId();
    }

    // ─── Transaction ─────────────────────────────────────────────────────────

    public function beginTransaction(): void
    {
        $this->pdo->beginTransaction();
    }

    public function commit(): void
    {
        $this->pdo->commit();
    }

    public function rollback(): void
    {
        if ($this->pdo->inTransaction()) {
            $this->pdo->rollBack();
        }
    }

    public function inTransaction(): bool
    {
        return $this->pdo->inTransaction();
    }

    /**
     * Chạy callback trong transaction.
     * Tự động commit nếu thành công, rollback nếu exception.
     *
     * @throws \Throwable
     */
    public function transaction(callable $callback): mixed
    {
        $this->beginTransaction();
        try {
            $result = $callback($this);
            $this->commit();
            return $result;
        } catch (\Throwable $e) {
            $this->rollback();
            throw $e;
        }
    }

    // ─── Prevent cloning / serialization ─────────────────────────────────────

    private function __clone() {}
    public function __wakeup(): void
    {
        throw new RuntimeException('Database singleton cannot be unserialized.');
    }
}
