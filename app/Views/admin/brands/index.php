<?php /** admin/brands/index.php — Variables: $brands */ ?>
<div class="admin-toolbar">
    <button class="btn btn--primary" onclick="document.getElementById('modal-add-brand').classList.add('is-open')">+ Thêm thương hiệu</button>
</div>
<div class="admin-table-card">
    <div class="admin-table-card__header">
        <h2 class="admin-table-card__title">Thương hiệu <span class="badge badge--muted"><?= count($brands) ?></span></h2>
    </div>
    <?php if (empty($brands)): ?>
    <div class="admin-empty">Chưa có thương hiệu nào.</div>
    <?php else: ?>
    <div class="table-wrap">
    <table class="admin-table">
        <thead><tr><th>ID</th><th>Tên</th><th>Slug</th><th>Sản phẩm</th><th>Trạng thái</th><th>Hành động</th></tr></thead>
        <tbody>
        <?php foreach ($brands as $b): ?>
        <tr>
            <td class="text-muted">#<?= $b['id'] ?></td>
            <td><?= e($b['name']) ?></td>
            <td><code><?= e($b['slug']) ?></code></td>
            <td><?= $b['product_count'] ?? 0 ?></td>
            <td><span class="badge badge--<?= $b['is_active'] ? 'success' : 'muted' ?>"><?= $b['is_active'] ? 'Hoạt động' : 'Ẩn' ?></span></td>
            <td>
                <div class="action-group">
                    <button class="btn btn--sm btn--outline" onclick='openEditBrand(<?= htmlspecialchars(json_encode($b), ENT_QUOTES) ?>)'>✏️ Sửa</button>
                    <form method="POST" action="<?= base_url('/admin/brands/'.$b['id']) ?>" class="form-inline" onsubmit="return confirm('Xóa thương hiệu này?')">
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
<div class="admin-modal" id="modal-add-brand">
    <div class="admin-modal__backdrop" onclick="this.parentElement.classList.remove('is-open')"></div>
    <div class="admin-modal__dialog">
        <div class="admin-modal__header"><h3>Thêm thương hiệu</h3><button onclick="document.getElementById('modal-add-brand').classList.remove('is-open')" class="modal-close">✕</button></div>
        <form method="POST" action="<?= base_url('/admin/brands') ?>" class="admin-form">
            <?= csrf_field() ?>
            <div class="form-group"><label class="form-label">Tên *</label><input type="text" name="name" class="form-control" required id="add-brand-name" oninput="brandSlug(this.value,'add-brand-slug')"></div>
            <div class="form-group"><label class="form-label">Slug *</label><input type="text" name="slug" class="form-control" required id="add-brand-slug"></div>
            <div class="form-group"><label class="form-label">Logo URL</label><input type="url" name="logo_url" class="form-control" placeholder="https://..."></div>
            <div class="form-group"><label class="form-label">Mô tả</label><textarea name="description" class="form-control" rows="3"></textarea></div>
            <div class="form-group"><label class="form-label">Thứ tự</label><input type="number" name="sort_order" class="form-control" value="0" min="0"></div>
            <div class="form-actions">
                <button type="submit" class="btn btn--primary">Thêm</button>
                <button type="button" class="btn btn--ghost" onclick="document.getElementById('modal-add-brand').classList.remove('is-open')">Huỷ</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Modal -->
<div class="admin-modal" id="modal-edit-brand">
    <div class="admin-modal__backdrop" onclick="this.parentElement.classList.remove('is-open')"></div>
    <div class="admin-modal__dialog">
        <div class="admin-modal__header"><h3>Sửa thương hiệu</h3><button onclick="document.getElementById('modal-edit-brand').classList.remove('is-open')" class="modal-close">✕</button></div>
        <form method="POST" id="form-edit-brand" class="admin-form">
            <?= csrf_field() ?>
            <div class="form-group"><label class="form-label">Tên *</label><input type="text" name="name" class="form-control" required id="edit-brand-name"></div>
            <div class="form-group"><label class="form-label">Slug *</label><input type="text" name="slug" class="form-control" required id="edit-brand-slug"></div>
            <div class="form-group"><label class="form-label">Logo URL</label><input type="url" name="logo_url" class="form-control" id="edit-brand-logo"></div>
            <div class="form-group"><label class="form-label">Mô tả</label><textarea name="description" class="form-control" rows="3" id="edit-brand-desc"></textarea></div>
            <div class="form-group"><label class="toggle-switch"><input type="checkbox" name="is_active" value="1" id="edit-brand-active"><span class="toggle-switch__slider"></span><span class="toggle-switch__label">Hoạt động</span></label></div>
            <div class="form-actions">
                <button type="submit" class="btn btn--primary">Lưu</button>
                <button type="button" class="btn btn--ghost" onclick="document.getElementById('modal-edit-brand').classList.remove('is-open')">Huỷ</button>
            </div>
        </form>
    </div>
</div>

<script>
function brandSlug(name, targetId) {
    let s = name.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g,'').replace(/đ/g,'d').replace(/[^a-z0-9\s-]/g,'').trim().replace(/\s+/g,'-');
    document.getElementById(targetId).value = s;
}
function openEditBrand(b) {
    document.getElementById('edit-brand-name').value   = b.name;
    document.getElementById('edit-brand-slug').value   = b.slug;
    document.getElementById('edit-brand-logo').value   = b.logo_url ?? '';
    document.getElementById('edit-brand-desc').value   = b.description ?? '';
    document.getElementById('edit-brand-active').checked = b.is_active == 1;
    document.getElementById('form-edit-brand').action = `<?= base_url('/admin/brands/') ?>${b.id}`;
    document.getElementById('modal-edit-brand').classList.add('is-open');
}
</script>
