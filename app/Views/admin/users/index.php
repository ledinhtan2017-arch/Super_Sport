<?php
/**
 * admin/users/index.php
 * Variables: $users, $paginator, $search, $role
 */
?>
<div class="admin-toolbar">
    <form method="GET" action="<?= base_url('/admin/users') ?>" class="admin-search-form">
        <input type="text" name="search" value="<?= e($search) ?>" placeholder="Tên, email..." class="admin-input" id="user-search">
        <select name="role" class="admin-select" id="user-role-filter">
            <option value="">Tất cả vai trò</option>
            <option value="customer" <?= $role === 'customer' ? 'selected' : '' ?>>Khách hàng</option>
            <option value="admin"    <?= $role === 'admin'    ? 'selected' : '' ?>>Admin</option>
        </select>
        <button type="submit" class="btn btn--primary">Tìm</button>
        <a href="<?= base_url('/admin/users') ?>" class="btn btn--ghost">Reset</a>
    </form>
</div>
<div class="admin-table-card">
    <div class="admin-table-card__header">
        <h2 class="admin-table-card__title">Người dùng <span class="badge badge--muted"><?= number_format($paginator['total']) ?></span></h2>
    </div>
    <?php if (empty($users)): ?>
    <div class="admin-empty">Không tìm thấy người dùng nào.</div>
    <?php else: ?>
    <div class="table-wrap">
    <table class="admin-table">
        <thead><tr><th>ID</th><th>Tên</th><th>Email</th><th>SĐT</th><th>Vai trò</th><th>Trạng thái</th><th>Ngày tạo</th><th>Hành động</th></tr></thead>
        <tbody>
        <?php foreach ($users as $u): ?>
        <tr>
            <td class="text-muted">#<?= $u['id'] ?></td>
            <td><?= e($u['name']) ?></td>
            <td><?= e($u['email']) ?></td>
            <td><?= e($u['phone'] ?? '—') ?></td>
            <td><span class="badge badge--<?= $u['role'] === 'admin' ? 'purple' : 'info' ?>"><?= $u['role'] === 'admin' ? 'Admin' : 'Khách hàng' ?></span></td>
            <td><span class="badge badge--<?= $u['is_active'] ? 'success' : 'danger' ?>"><?= $u['is_active'] ? 'Hoạt động' : 'Bị khoá' ?></span></td>
            <td><?= date('d/m/Y', strtotime($u['created_at'])) ?></td>
            <td>
                <button class="btn btn--sm btn--outline" onclick='openEditUser(<?= htmlspecialchars(json_encode($u), ENT_QUOTES) ?>)'>✏️ Sửa</button>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
    <?php if ($paginator['last_page'] > 1): ?>
    <div class="admin-pagination">
        <?php if ($paginator['current_page'] > 1): ?>
        <a href="?page=<?= $paginator['current_page']-1 ?>&search=<?= urlencode($search) ?>&role=<?= urlencode($role) ?>" class="btn btn--ghost btn--sm">← Trước</a>
        <?php endif; ?>
        <span class="pagination-info">Trang <?= $paginator['current_page'] ?> / <?= $paginator['last_page'] ?></span>
        <?php if ($paginator['current_page'] < $paginator['last_page']): ?>
        <a href="?page=<?= $paginator['current_page']+1 ?>&search=<?= urlencode($search) ?>&role=<?= urlencode($role) ?>" class="btn btn--ghost btn--sm">Tiếp →</a>
        <?php endif; ?>
    </div>
    <?php endif; ?>
    <?php endif; ?>
</div>

<!-- Edit User Modal -->
<div class="admin-modal" id="modal-edit-user">
    <div class="admin-modal__backdrop" onclick="this.parentElement.classList.remove('is-open')"></div>
    <div class="admin-modal__dialog">
        <div class="admin-modal__header"><h3>Cập nhật người dùng</h3><button onclick="document.getElementById('modal-edit-user').classList.remove('is-open')" class="modal-close">✕</button></div>
        <form method="POST" id="form-edit-user" class="admin-form">
            <?= csrf_field() ?>
            <div class="form-group"><label class="form-label">Tên</label><input type="text" name="name" class="form-control" required id="edit-user-name"></div>
            <div class="form-group">
                <label class="form-label">Vai trò</label>
                <select name="role" class="form-control" id="edit-user-role">
                    <option value="customer">Khách hàng</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            <div class="form-group"><label class="toggle-switch"><input type="checkbox" name="is_active" value="1" id="edit-user-active"><span class="toggle-switch__slider"></span><span class="toggle-switch__label">Tài khoản hoạt động</span></label></div>
            <div class="form-actions">
                <button type="submit" class="btn btn--primary">Lưu</button>
                <button type="button" class="btn btn--ghost" onclick="document.getElementById('modal-edit-user').classList.remove('is-open')">Huỷ</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditUser(u) {
    document.getElementById('edit-user-name').value   = u.name;
    document.getElementById('edit-user-role').value   = u.role;
    document.getElementById('edit-user-active').checked = u.is_active == 1;
    document.getElementById('form-edit-user').action = `<?= base_url('/admin/users/') ?>${u.id}`;
    document.getElementById('modal-edit-user').classList.add('is-open');
}
</script>
