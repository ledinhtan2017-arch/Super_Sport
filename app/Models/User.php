<?php

declare(strict_types=1);

namespace App\Models;

/**
 * User Model
 *
 * Quản lý tài khoản người dùng: đăng ký, đăng nhập, profile, địa chỉ,
 * đặt lại mật khẩu qua bảng `password_resets`.
 *
 * Schema notes:
 *   - Cột mật khẩu: `password` (VARCHAR 255, bcrypt)
 *   - Bảng reset: `password_resets` (user_id, token, expires_at, used_at)
 */
final class User extends BaseModel
{
    protected string $table    = 'users';
    protected array  $fillable = [
        'name', 'email', 'phone', 'password',
        'role', 'is_active', 'email_verified_at',
    ];

    // ── Authentication ────────────────────────────────────────────────────────

    /** Tìm user theo email. */
    public function findByEmail(string $email): ?array
    {
        return $this->rawOne(
            "SELECT * FROM `users` WHERE `email` = ? LIMIT 1",
            [$email]
        );
    }

    /** Tạo tài khoản mới, trả về user_id vừa tạo. */
    public function register(string $name, string $email, string $password, string $phone = ''): int
    {
        return $this->create([
            'name'     => $name,
            'email'    => $email,
            'password' => password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]),
            'phone'    => $phone,
            'role'     => 'customer',
            'is_active' => 1,
        ]);
    }

    /** Xác thực email + mật khẩu. Trả về user array hoặc null. */
    public function authenticate(string $email, string $password): ?array
    {
        $user = $this->findByEmail($email);
        if (!$user || !$user['is_active']) {
            return null;
        }
        if (!password_verify($password, $user['password'])) {
            return null;
        }
        return $user;
    }

    /** Kiểm tra email đã tồn tại chưa. */
    public function emailExists(string $email): bool
    {
        return $this->exists(['email' => $email]);
    }

    // ── Profile ───────────────────────────────────────────────────────────────

    /** Lấy thông tin profile (không trả về password). */
    public function getProfile(int $userId): ?array
    {
        return $this->rawOne(
            "SELECT id, name, email, phone, avatar, role, is_active, created_at
             FROM `users` WHERE id = ? LIMIT 1",
            [$userId]
        );
    }

    /** Cập nhật tên và số điện thoại. */
    public function updateProfile(int $userId, string $name, string $phone): int
    {
        return $this->db->execute(
            "UPDATE `users` SET `name` = ?, `phone` = ?, `updated_at` = NOW()
             WHERE `id` = ?",
            [$name, $phone, $userId]
        );
    }

    /** Đổi mật khẩu sau khi xác thực mật khẩu cũ. */
    public function changePassword(int $userId, string $currentPassword, string $newPassword): bool
    {
        $user = $this->find($userId);
        if (!$user || !password_verify($currentPassword, $user['password'])) {
            return false;
        }
        $this->db->execute(
            "UPDATE `users` SET `password` = ?, `updated_at` = NOW() WHERE `id` = ?",
            [password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 12]), $userId]
        );
        return true;
    }

    // ── Password Reset (bảng password_resets) ────────────────────────────────

    /**
     * Tạo token reset mật khẩu trong bảng password_resets.
     * Trả về plain token (gửi email), null nếu email không tồn tại.
     */
    public function createResetToken(string $email): ?string
    {
        $user = $this->findByEmail($email);
        if (!$user) {
            return null;
        }

        $token  = bin2hex(random_bytes(32));
        $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

        // Vô hiệu hoá token cũ chưa dùng của user này
        $this->db->execute(
            "DELETE FROM `password_resets` WHERE `user_id` = ? AND `used_at` IS NULL",
            [$user['id']]
        );

        $this->db->execute(
            "INSERT INTO `password_resets` (`user_id`, `token`, `expires_at`)
             VALUES (?, ?, ?)",
            [$user['id'], hash('sha256', $token), $expiry]
        );

        return $token;
    }

    /**
     * Xác thực token và đặt lại mật khẩu.
     * Đánh dấu token đã dùng (used_at).
     */
    public function resetPassword(string $token, string $newPassword): bool
    {
        $hashed = hash('sha256', $token);

        $row = $this->rawOne(
            "SELECT * FROM `password_resets`
             WHERE `token` = ?
               AND `expires_at` > NOW()
               AND `used_at` IS NULL
             LIMIT 1",
            [$hashed]
        );

        if (!$row) {
            return false;
        }

        // Đặt lại mật khẩu
        $this->db->execute(
            "UPDATE `users` SET `password` = ?, `updated_at` = NOW() WHERE `id` = ?",
            [password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 12]), $row['user_id']]
        );

        // Đánh dấu token đã dùng
        $this->db->execute(
            "UPDATE `password_resets` SET `used_at` = NOW() WHERE `id` = ?",
            [$row['id']]
        );

        return true;
    }

    // ── Addresses ─────────────────────────────────────────────────────────────

    /** Lấy tất cả địa chỉ của user. */
    public function getAddresses(int $userId): array
    {
        return $this->raw(
            "SELECT * FROM `user_addresses`
             WHERE `user_id` = ? ORDER BY `is_default` DESC, `id` DESC",
            [$userId]
        );
    }

    /** Lấy địa chỉ mặc định. */
    public function getDefaultAddress(int $userId): ?array
    {
        return $this->rawOne(
            "SELECT * FROM `user_addresses`
             WHERE `user_id` = ? AND `is_default` = 1 LIMIT 1",
            [$userId]
        );
    }

    /** Thêm địa chỉ mới. */
    public function addAddress(int $userId, array $data): int
    {
        if (!empty($data['is_default'])) {
            $this->db->execute(
                "UPDATE `user_addresses` SET `is_default` = 0 WHERE `user_id` = ?",
                [$userId]
            );
        }
        $this->db->execute(
            "INSERT INTO `user_addresses`
                (`user_id`, `recipient_name`, `phone`, `address_line`,
                 `ward`, `district`, `province`, `is_default`)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
            [
                $userId,
                $data['recipient_name'] ?? '',
                $data['phone']          ?? '',
                $data['address_line']   ?? '',
                $data['ward']           ?? '',
                $data['district']       ?? '',
                $data['province']       ?? '',
                empty($data['is_default']) ? 0 : 1,
            ]
        );
        return (int)$this->db->lastInsertId();
    }

    /** Cập nhật địa chỉ (chỉ của user đó). */
    public function updateAddress(int $userId, int $addressId, array $data): bool
    {
        $addr = $this->rawOne(
            "SELECT id FROM `user_addresses` WHERE `id` = ? AND `user_id` = ?",
            [$addressId, $userId]
        );
        if (!$addr) {
            return false;
        }
        if (!empty($data['is_default'])) {
            $this->db->execute(
                "UPDATE `user_addresses` SET `is_default` = 0 WHERE `user_id` = ?",
                [$userId]
            );
        }
        $this->db->execute(
            "UPDATE `user_addresses`
             SET `recipient_name` = ?, `phone` = ?, `address_line` = ?,
                 `ward` = ?, `district` = ?, `province` = ?, `is_default` = ?
             WHERE `id` = ?",
            [
                $data['recipient_name'] ?? '',
                $data['phone']          ?? '',
                $data['address_line']   ?? '',
                $data['ward']           ?? '',
                $data['district']       ?? '',
                $data['province']       ?? '',
                empty($data['is_default']) ? 0 : 1,
                $addressId,
            ]
        );
        return true;
    }

    /** Xóa địa chỉ (chỉ của user đó). */
    public function deleteAddress(int $userId, int $addressId): bool
    {
        return $this->db->execute(
            "DELETE FROM `user_addresses` WHERE `id` = ? AND `user_id` = ?",
            [$addressId, $userId]
        ) > 0;
    }

    // ── Admin ─────────────────────────────────────────────────────────────────

    /** Danh sách users cho admin (phân trang + tìm kiếm). */
    public function adminList(int $page = 1, int $perPage = 20, string $search = ''): array
    {
        $page   = max(1, $page);
        $offset = ($page - 1) * $perPage;

        if ($search !== '') {
            $like  = '%' . $search . '%';
            $total = (int)$this->rawColumn(
                "SELECT COUNT(*) FROM `users`
                 WHERE `name` LIKE ? OR `email` LIKE ? OR `phone` LIKE ?",
                [$like, $like, $like]
            );
            $data = $this->raw(
                "SELECT id, name, email, phone, role, is_active, created_at
                 FROM `users`
                 WHERE `name` LIKE ? OR `email` LIKE ? OR `phone` LIKE ?
                 ORDER BY `id` DESC LIMIT ? OFFSET ?",
                [$like, $like, $like, $perPage, $offset]
            );
        } else {
            $total = (int)$this->rawColumn("SELECT COUNT(*) FROM `users`");
            $data  = $this->raw(
                "SELECT id, name, email, phone, role, is_active, created_at
                 FROM `users` ORDER BY `id` DESC LIMIT ? OFFSET ?",
                [$perPage, $offset]
            );
        }

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

    /** Đếm khách hàng mới trong tháng hiện tại. */
    public function countNewThisMonth(): int
    {
        return (int)$this->rawColumn(
            "SELECT COUNT(*) FROM `users`
             WHERE MONTH(created_at) = MONTH(NOW())
               AND YEAR(created_at)  = YEAR(NOW())"
        );
    }

    // Admin helpers
    public function rawColumn(string $sql, array $params = []): mixed { return $this->db->fetchColumn($sql, $params); }

    public function raw(string $sql, array $params = []): array { return $this->db->fetchAll($sql, $params); }
}
