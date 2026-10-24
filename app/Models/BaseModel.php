<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

/**
 * BaseModel — Lớp model cơ sở
 *
 * Cung cấp các phương thức CRUD dùng chung qua PDO.
 * Mọi Model cụ thể kế thừa lớp này và khai báo $table + $fillable.
 *
 * Không implement Active Record đầy đủ — giữ đơn giản, tránh magic.
 */
abstract class BaseModel
{
    /** Tên bảng trong database — khai báo ở subclass */
    protected string $table = '';

    /** Các cột được phép gán hàng loạt */
    protected array $fillable = [];

    /** Cột primary key */
    protected string $primaryKey = 'id';

    protected Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // ─── Basic Finders ────────────────────────────────────────────────────────

    /**
     * Lấy tất cả bản ghi.
     */
    public function all(string $orderBy = 'id', string $direction = 'ASC'): array
    {
        $direction = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';
        return $this->db->fetchAll(
            "SELECT * FROM `{$this->table}` ORDER BY `{$orderBy}` {$direction}"
        );
    }

    /**
     * Lấy bản ghi theo primary key.
     */
    public function find(int $id): ?array
    {
        return $this->db->fetchOne(
            "SELECT * FROM `{$this->table}` WHERE `{$this->primaryKey}` = ? LIMIT 1",
            [$id]
        );
    }

    /**
     * Lấy bản ghi hoặc throw exception nếu không có.
     *
     * @throws \RuntimeException
     */
    public function findOrFail(int $id): array
    {
        $record = $this->find($id);
        if ($record === null) {
            throw new \RuntimeException("Không tìm thấy bản ghi #{$id} trong bảng {$this->table}.", 404);
        }
        return $record;
    }

    /**
     * Lấy bản ghi đầu tiên theo điều kiện.
     * $conditions: ['email' => 'x@y.com', 'is_active' => 1]
     */
    public function findWhere(array $conditions): ?array
    {
        [$where, $params] = $this->buildWhereClause($conditions);
        return $this->db->fetchOne(
            "SELECT * FROM `{$this->table}` WHERE {$where} LIMIT 1",
            $params
        );
    }

    /**
     * Lấy tất cả bản ghi theo điều kiện.
     */
    public function where(array $conditions, string $orderBy = 'id', string $direction = 'ASC'): array
    {
        [$where, $params] = $this->buildWhereClause($conditions);
        $direction = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';
        return $this->db->fetchAll(
            "SELECT * FROM `{$this->table}` WHERE {$where} ORDER BY `{$orderBy}` {$direction}",
            $params
        );
    }

    /**
     * Đếm bản ghi theo điều kiện.
     */
    public function count(array $conditions = []): int
    {
        if (empty($conditions)) {
            return (int)$this->db->fetchColumn("SELECT COUNT(*) FROM `{$this->table}`");
        }
        [$where, $params] = $this->buildWhereClause($conditions);
        return (int)$this->db->fetchColumn(
            "SELECT COUNT(*) FROM `{$this->table}` WHERE {$where}",
            $params
        );
    }

    /**
     * Kiểm tra bản ghi có tồn tại.
     */
    public function exists(array $conditions): bool
    {
        return $this->count($conditions) > 0;
    }

    // ─── Pagination ───────────────────────────────────────────────────────────

    /**
     * Lấy bản ghi có phân trang.
     *
     * @return array{data: array, total: int, per_page: int, current_page: int, last_page: int}
     */
    public function paginate(
        int    $page    = 1,
        int    $perPage = 16,
        array  $conditions = [],
        string $orderBy = 'id',
        string $direction = 'DESC'
    ): array {
        $page      = max(1, $page);
        $offset    = ($page - 1) * $perPage;
        $direction = strtoupper($direction) === 'ASC' ? 'ASC' : 'DESC';

        if (empty($conditions)) {
            $where  = '1=1';
            $params = [];
        } else {
            [$where, $params] = $this->buildWhereClause($conditions);
        }

        $total = (int)$this->db->fetchColumn(
            "SELECT COUNT(*) FROM `{$this->table}` WHERE {$where}",
            $params
        );

        $data = $this->db->fetchAll(
            "SELECT * FROM `{$this->table}` WHERE {$where} ORDER BY `{$orderBy}` {$direction} LIMIT ? OFFSET ?",
            array_merge($params, [$perPage, $offset])
        );

        return [
            'data'         => $data,
            'total'        => $total,
            'per_page'     => $perPage,
            'current_page' => $page,
            'last_page'    => (int)ceil($total / $perPage),
            'from'         => $total > 0 ? $offset + 1 : 0,
            'to'           => min($offset + $perPage, $total),
        ];
    }

    // ─── Write Operations ─────────────────────────────────────────────────────

    /**
     * Tạo bản ghi mới.
     * Chỉ insert các cột trong $fillable.
     *
     * @return int ID bản ghi vừa tạo
     */
    public function create(array $data): int
    {
        $data    = $this->filterFillable($data);
        $columns = array_keys($data);
        $placeholders = array_map(fn($col) => ":{$col}", $columns);

        $sql = sprintf(
            "INSERT INTO `%s` (%s) VALUES (%s)",
            $this->table,
            implode(', ', array_map(fn($c) => "`{$c}`", $columns)),
            implode(', ', $placeholders)
        );

        $this->db->execute($sql, $data);
        return $this->db->lastInsertId();
    }

    /**
     * Cập nhật bản ghi theo primary key.
     *
     * @return int Số hàng bị ảnh hưởng
     */
    public function update(int $id, array $data): int
    {
        $data    = $this->filterFillable($data);
        $columns = array_keys($data);

        $setParts = array_map(fn($col) => "`{$col}` = :{$col}", $columns);
        $sql = sprintf(
            "UPDATE `%s` SET %s WHERE `%s` = :__pk__",
            $this->table,
            implode(', ', $setParts),
            $this->primaryKey
        );

        $data['__pk__'] = $id;
        return $this->db->execute($sql, $data);
    }

    /**
     * Xóa bản ghi theo primary key.
     */
    public function delete(int $id): int
    {
        return $this->db->execute(
            "DELETE FROM `{$this->table}` WHERE `{$this->primaryKey}` = ?",
            [$id]
        );
    }

    // ─── Direct DB Access ─────────────────────────────────────────────────────

    /**
     * Thực thi query tùy chỉnh (dùng khi cần JOIN, subquery...).
     */
    protected function raw(string $sql, array $params = []): array
    {
        return $this->db->fetchAll($sql, $params);
    }

    protected function rawOne(string $sql, array $params = []): ?array
    {
        return $this->db->fetchOne($sql, $params);
    }

    protected function rawColumn(string $sql, array $params = []): mixed
    {
        return $this->db->fetchColumn($sql, $params);
    }

    // ─── Private Utilities ────────────────────────────────────────────────────

    /**
     * Lọc $data chỉ giữ các key có trong $fillable.
     */
    private function filterFillable(array $data): array
    {
        if (empty($this->fillable)) {
            return $data; // Không giới hạn nếu $fillable rỗng
        }
        return array_intersect_key($data, array_flip($this->fillable));
    }

    /**
     * Build WHERE clause từ mảng điều kiện.
     * ['email' => 'x@y.com', 'is_active' => 1] → "email = ? AND is_active = ?"
     */
    private function buildWhereClause(array $conditions): array
    {
        $parts  = [];
        $params = [];

        foreach ($conditions as $column => $value) {
            if ($value === null) {
                $parts[] = "`{$column}` IS NULL";
            } else {
                $parts[] = "`{$column}` = ?";
                $params[] = $value;
            }
        }

        return [implode(' AND ', $parts), $params];
    }

    /** Alias for create() — returns last insert ID */
    public function insert(array $data): int
    {
        return $this->create($data);
    }
}
