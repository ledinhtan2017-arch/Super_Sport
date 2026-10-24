<?php
/**
 * admin/products/form.php
 * Variables: $product (null=create), $categories, $brands, $old
 */
$isEdit  = $product !== null;
$val     = fn($field) => e($old[$field] ?? $product[$field] ?? '');
?>

<div class="admin-form-card">
    <form method="POST"
          action="<?= $isEdit ? base_url('/admin/products/'.$product['id']) : base_url('/admin/products') ?>"
          class="admin-form" id="product-form">
        <?= csrf_field() ?>
        <?php if ($isEdit): ?><input type="hidden" name="_method" value="POST"><?php endif; ?>

        <div class="form-grid form-grid--2">
            <!-- Name -->
            <div class="form-group form-group--full">
                <label class="form-label" for="f-name">Tên sản phẩm <span class="required">*</span></label>
                <input type="text" name="name" id="f-name" class="form-control"
                       value="<?= $val('name') ?>" required maxlength="200"
                       oninput="autoSlug(this.value)">
            </div>

            <!-- Slug -->
            <div class="form-group form-group--full">
                <label class="form-label" for="f-slug">Slug <span class="required">*</span></label>
                <input type="text" name="slug" id="f-slug" class="form-control"
                       value="<?= $val('slug') ?>" required maxlength="220">
                <span class="form-hint">URL-friendly, chỉ chứa chữ thường, số và gạch ngang.</span>
            </div>

            <!-- Category -->
            <div class="form-group">
                <label class="form-label" for="f-category">Danh mục <span class="required">*</span></label>
                <select name="category_id" id="f-category" class="form-control" required>
                    <option value="">-- Chọn danh mục --</option>
                    <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>"
                        <?= (int)($old['category_id'] ?? $product['category_id'] ?? 0) === (int)$cat['id'] ? 'selected' : '' ?>>
                        <?= e($cat['name']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Brand -->
            <div class="form-group">
                <label class="form-label" for="f-brand">Thương hiệu <span class="required">*</span></label>
                <select name="brand_id" id="f-brand" class="form-control" required>
                    <option value="">-- Chọn thương hiệu --</option>
                    <?php foreach ($brands as $b): ?>
                    <option value="<?= $b['id'] ?>"
                        <?= (int)($old['brand_id'] ?? $product['brand_id'] ?? 0) === (int)$b['id'] ? 'selected' : '' ?>>
                        <?= e($b['name']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Status -->
            <div class="form-group">
                <label class="form-label" for="f-status">Trạng thái</label>
                <select name="status" id="f-status" class="form-control">
                    <?php foreach (['draft'=>'Nháp','active'=>'Đang bán','inactive'=>'Ẩn'] as $val_s => $lbl): ?>
                    <option value="<?= $val_s ?>"
                        <?= ($old['status'] ?? $product['status'] ?? 'draft') === $val_s ? 'selected' : '' ?>>
                        <?= $lbl ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Featured -->
            <div class="form-group form-group--center">
                <label class="form-label">Nổi bật</label>
                <label class="toggle-switch" for="f-featured">
                    <input type="checkbox" name="is_featured" id="f-featured" value="1"
                           <?= !empty($old['is_featured'] ?? $product['is_featured'] ?? 0) ? 'checked' : '' ?>>
                    <span class="toggle-switch__slider"></span>
                    <span class="toggle-switch__label">Hiển thị ở trang chủ</span>
                </label>
            </div>

            <!-- Description -->
            <div class="form-group form-group--full">
                <label class="form-label" for="f-desc">Mô tả sản phẩm</label>
                <textarea name="description" id="f-desc" class="form-control form-control--textarea" rows="6"><?= e($old['description'] ?? $product['description'] ?? '') ?></textarea>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn--primary" id="btn-save-product">
                <?= $isEdit ? '💾 Lưu thay đổi' : '✅ Tạo sản phẩm' ?>
            </button>
            <a href="<?= base_url('/admin/products') ?>" class="btn btn--ghost">Huỷ</a>
            <?php if ($isEdit): ?>
            <a href="<?= base_url('/admin/products/'.$product['id'].'/variants') ?>" class="btn btn--outline" style="margin-left:auto">🔧 Quản lý biến thể</a>
            <?php endif; ?>
        </div>
    </form>
</div>

<script>
function autoSlug(name) {
    const slugField = document.getElementById('f-slug');
    if (!slugField || (slugField.dataset.manual === '1')) return;
    let slug = name.toLowerCase()
        .normalize('NFD').replace(/[\u0300-\u036f]/g,'')
        .replace(/đ/g,'d').replace(/[^a-z0-9\s-]/g,'')
        .trim().replace(/\s+/g,'-');
    slugField.value = slug;
}
document.getElementById('f-slug')?.addEventListener('input', function() {
    this.dataset.manual = '1';
});
</script>
