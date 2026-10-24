<?php
/**
 * admin/categories/index.php
 * Variables: $categories
 */
?>
<div class="admin-toolbar">
    <button class="btn btn--primary" id="btn-add-cat" onclick="document.getElementById('modal-add-cat').classList.add('is-open')">+ Thêm danh mục</button>
</div>

<div class="admin-table-card">
    <div class="admin-table-card__header">
        <h2 class="admin-table-card__title">Danh mục <span class="badge badge--muted"><?= count($categories) ?></span></h2>
    </div>
    <?php if (empty($categories)): ?>
    <div class="admin-empty">Chưa có danh mục nào.</div>
    <?php else: ?>
    <div class="table-wrap">
    <table class="admin-table">
        <thead>
            <tr><th>ID</th><th>Tên</th><th>Slug</th><th>Sản phẩm</th><th>Trạng thái</th><th>Hành động</th></tr>
        </thead>
        <tbody>
        <?php foreach ($categories as $cat): ?>
        <tr>
            <td class="text-muted">#<?= $cat['id'] ?></td>
            <td><?= e($cat['name']) ?></td>
            <td><code><?= e($cat['slug']) ?></code></td>
            <td><?= $cat['product_count'] ?? 0 ?></td>
            <td><span class="badge badge--<?= $cat['is_active'] ? 'success' : 'muted' ?>"><?= $cat['is_active'] ? 'Hoạt động' : 'Ẩn' ?></span></td>
            <td>
                <div class="action-group">
                    <button class="btn btn--sm btn--outline"
                        onclick="openEditCat(<?= htmlspecialchars(json_encode($cat), ENT_QUOTES) ?>)">✏️ Sửa</button>
                    <form method="POST" action="<?= base_url('/admin/categories/'.$cat['id']) ?>" class="form-inline"
                          onsubmit="return confirm('Xóa danh mục này?')">
                        <?= csrf_field() ?>
                        <input type="hidden" name="_method" value="DELETE">
                        <button type="submit" class="btn btn--sm btn--danger">🗑</button>
                    </form>
                </div>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
    <?php endif; ?>
</div>

<!-- Add Modal -->
<div class="admin-modal" id="modal-add-cat">
    <div class="admin-modal__backdrop" onclick="this.parentElement.classList.remove('is-open')"></div>
    <div class="admin-modal__dialog">
        <div class="admin-modal__header">
            <h3>Thêm danh mục mới</h3>
            <button onclick="document.getElementById('modal-add-cat').classList.remove('is-open')" class="modal-close">✕</button>
        </div>
        <form method="POST" action="<?= base_url('/admin/categories') ?>" class="admin-form">
            <?= csrf_field() ?>
            <div class="form-group">
                <label class="form-label">Tên danh mục *</label>
                <input type="text" name="name" class="form-control" required id="add-cat-name" oninput="catSlug(this.value,'add-cat-slug')">
            </div>
            <div class="form-group">
                <label class="form-label">Slug *</label>
                <input type="text" name="slug" class="form-control" required id="add-cat-slug">
            </div>
            <div class="form-group">
                <label class="form-label">Thứ tự</label>
                <input type="number" name="sort_order" class="form-control" value="0" min="0">
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn--primary">Thêm</button>
                <button type="button" class="btn btn--ghost" onclick="document.getElementById('modal-add-cat').classList.remove('is-open')">Huỷ</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Modal -->
<div class="admin-modal" id="modal-edit-cat">
    <div class="admin-modal__backdrop" onclick="this.parentElement.classList.remove('is-open')"></div>
    <div class="admin-modal__dialog">
        <div class="admin-modal__header">
            <h3>Sửa danh mục</h3>
            <button onclick="document.getElementById('modal-edit-cat').classList.remove('is-open')" class="modal-close">✕</button>
        </div>
        <form method="POST" id="form-edit-cat" class="admin-form">
            <?= csrf_field() ?>
            <div class="form-group">
                <label class="form-label">Tên danh mục *</label>
                <input type="text" name="name" class="form-control" required id="edit-cat-name">
            </div>
            <div class="form-group">
                <label class="form-label">Slug *</label>
                <input type="text" name="slug" class="form-control" required id="edit-cat-slug">
            </div>
            <div class="form-group">
                <label class="form-label">Thứ tự</label>
                <input type="number" name="sort_order" class="form-control" id="edit-cat-sort" min="0">
            </div>
            <div class="form-group">
                <label class="toggle-switch">
                    <input type="checkbox" name="is_active" value="1" id="edit-cat-active">
                    <span class="toggle-switch__slider"></span>
                    <span class="toggle-switch__label">Hoạt động</span>
                </label>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn--primary">Lưu thay đổi</button>
                <button type="button" class="btn btn--ghost" onclick="document.getElementById('modal-edit-cat').classList.remove('is-open')">Huỷ</button>
            </div>
        </form>
    </div>
</div>

<script>
function catSlug(name, targetId) {
    let s = name.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g,'')
        .replace(/đ/g,'d').replace(/[^a-z0-9\s-]/g,'').trim().replace(/\s+/g,'-');
    document.getElementById(targetId).value = s;
}
function openEditCat(cat) {
    document.getElementById('edit-cat-name').value   = cat.name;
    document.getElementById('edit-cat-slug').value   = cat.slug;
    document.getElementById('edit-cat-sort').value   = cat.sort_order;
    document.getElementById('edit-cat-active').checked = cat.is_active == 1;
    document.getElementById('form-edit-cat').action = `<?= base_url('/admin/categories/') ?>${cat.id}`;
    document.getElementById('modal-edit-cat').classList.add('is-open');
}
</script>
