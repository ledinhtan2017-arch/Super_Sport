<?php
/**
 * admin/discounts/index.php
 * Variables: $coupons
 */
function couponStatus(array $c): string {
    if (!$c['is_active']) return 'inactive';
    $now = date('Y-m-d H:i:s');
    if ($c['ends_at'] && $now > $c['ends_at']) return 'expired';
    if ($c['starts_at'] && $now < $c['starts_at']) return 'upcoming';
    if ($c['max_uses'] && $c['used_count'] >= $c['max_uses']) return 'maxed';
    return 'active';
}
$statusMeta = [
    'active'   => ['Đang chạy',  'success'],
    'inactive' => ['Tắt',        'muted'],
    'expired'  => ['Hết hạn',    'danger'],
    'upcoming' => ['Chưa bắt đầu','warning'],
    'maxed'    => ['Hết lượt',   'danger'],
];
?>
<div class="admin-toolbar">
    <button class="btn btn--primary" onclick="document.getElementById('modal-add-coupon').classList.add('is-open')">+ Thêm coupon</button>
</div>

<div class="admin-table-card">
    <div class="admin-table-card__header">
        <h2 class="admin-table-card__title">Mã khuyến mãi <span class="badge badge--muted"><?= count($coupons) ?></span></h2>
    </div>
    <?php if (empty($coupons)): ?>
    <div class="admin-empty">Chưa có coupon nào.</div>
    <?php else: ?>
    <div class="table-wrap">
    <table class="admin-table">
        <thead>
            <tr><th>Mã</th><th>Tên</th><th>Loại</th><th>Giá trị</th><th>Đã dùng</th><th>Hết hạn</th><th>Trạng thái</th><th>Hành động</th></tr>
        </thead>
        <tbody>
        <?php foreach ($coupons as $c): ?>
        <?php $st = couponStatus($c); [$stl, $stc] = $statusMeta[$st]; ?>
        <tr>
            <td><code class="coupon-code"><?= e($c['code']) ?></code></td>
            <td><?= e($c['name']) ?></td>
            <td><?= $c['type'] === 'percent' ? 'Phần trăm' : 'Cố định' ?></td>
            <td>
                <?= $c['type'] === 'percent' ? $c['value'].'%' : number_format((float)$c['value']).'đ' ?>
                <?php if ($c['max_discount']): ?><br><span class="text-muted text-xs">Tối đa: <?= number_format((float)$c['max_discount']) ?>đ</span><?php endif; ?>
            </td>
            <td><?= $c['used_count'] ?><?= $c['max_uses'] ? ' / '.$c['max_uses'] : '' ?></td>
            <td><?= $c['ends_at'] ? date('d/m/Y', strtotime($c['ends_at'])) : '—' ?></td>
            <td><span class="badge badge--<?= $stc ?>"><?= $stl ?></span></td>
            <td>
                <div class="action-group">
                    <button class="btn btn--sm btn--outline" onclick='openEditCoupon(<?= htmlspecialchars(json_encode($c), ENT_QUOTES) ?>)'>✏️</button>
                    <form method="POST" action="<?= base_url('/admin/discounts/'.$c['id']) ?>" class="form-inline" onsubmit="return confirm('Xóa coupon này?')">
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
<div class="admin-modal" id="modal-add-coupon">
    <div class="admin-modal__backdrop" onclick="this.parentElement.classList.remove('is-open')"></div>
    <div class="admin-modal__dialog">
        <div class="admin-modal__header"><h3>Thêm mã khuyến mãi</h3><button onclick="document.getElementById('modal-add-coupon').classList.remove('is-open')" class="modal-close">✕</button></div>
        <form method="POST" action="<?= base_url('/admin/discounts') ?>" class="admin-form">
            <?= csrf_field() ?>
            <div class="form-grid form-grid--2">
                <div class="form-group"><label class="form-label">Mã coupon *</label><input type="text" name="code" class="form-control" required style="text-transform:uppercase" placeholder="VD: SALE20"></div>
                <div class="form-group"><label class="form-label">Tên</label><input type="text" name="name" class="form-control" placeholder="Mô tả ngắn"></div>
                <div class="form-group">
                    <label class="form-label">Loại giảm *</label>
                    <select name="type" class="form-control">
                        <option value="percent">Phần trăm (%)</option>
                        <option value="fixed">Số tiền cố định (đ)</option>
                    </select>
                </div>
                <div class="form-group"><label class="form-label">Giá trị *</label><input type="number" name="value" class="form-control" required min="0" step="0.01" placeholder="VD: 10 hoặc 50000"></div>
                <div class="form-group"><label class="form-label">Giảm tối đa (đ)</label><input type="number" name="max_discount" class="form-control" min="0" placeholder="Để trống = không giới hạn"></div>
                <div class="form-group"><label class="form-label">Đơn tối thiểu (đ)</label><input type="number" name="min_order_value" class="form-control" min="0"></div>
                <div class="form-group"><label class="form-label">Số lần dùng tối đa</label><input type="number" name="max_uses" class="form-control" min="1" placeholder="Để trống = không giới hạn"></div>
                <div class="form-group"><label class="form-label">Bắt đầu</label><input type="datetime-local" name="starts_at" class="form-control"></div>
                <div class="form-group"><label class="form-label">Kết thúc</label><input type="datetime-local" name="ends_at" class="form-control"></div>
                <div class="form-group"><label class="toggle-switch"><input type="checkbox" name="is_active" value="1" checked><span class="toggle-switch__slider"></span><span class="toggle-switch__label">Kích hoạt ngay</span></label></div>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn--primary">Thêm coupon</button>
                <button type="button" class="btn btn--ghost" onclick="document.getElementById('modal-add-coupon').classList.remove('is-open')">Huỷ</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Modal -->
<div class="admin-modal" id="modal-edit-coupon">
    <div class="admin-modal__backdrop" onclick="this.parentElement.classList.remove('is-open')"></div>
    <div class="admin-modal__dialog">
        <div class="admin-modal__header"><h3>Sửa coupon: <span id="edit-coupon-code-label"></span></h3><button onclick="document.getElementById('modal-edit-coupon').classList.remove('is-open')" class="modal-close">✕</button></div>
        <form method="POST" id="form-edit-coupon" class="admin-form">
            <?= csrf_field() ?>
            <div class="form-grid form-grid--2">
                <div class="form-group"><label class="form-label">Tên</label><input type="text" name="name" class="form-control" id="edit-coupon-name"></div>
                <div class="form-group"><label class="form-label">Loại</label><select name="type" class="form-control" id="edit-coupon-type"><option value="percent">Phần trăm</option><option value="fixed">Cố định</option></select></div>
                <div class="form-group"><label class="form-label">Giá trị</label><input type="number" name="value" class="form-control" id="edit-coupon-value" min="0" step="0.01"></div>
                <div class="form-group"><label class="form-label">Giảm tối đa</label><input type="number" name="max_discount" class="form-control" id="edit-coupon-max-discount" min="0"></div>
                <div class="form-group"><label class="form-label">Đơn tối thiểu</label><input type="number" name="min_order_value" class="form-control" id="edit-coupon-min-order" min="0"></div>
                <div class="form-group"><label class="form-label">Số lần tối đa</label><input type="number" name="max_uses" class="form-control" id="edit-coupon-max-uses" min="1"></div>
                <div class="form-group"><label class="form-label">Bắt đầu</label><input type="datetime-local" name="starts_at" class="form-control" id="edit-coupon-starts"></div>
                <div class="form-group"><label class="form-label">Kết thúc</label><input type="datetime-local" name="ends_at" class="form-control" id="edit-coupon-ends"></div>
                <div class="form-group"><label class="toggle-switch"><input type="checkbox" name="is_active" value="1" id="edit-coupon-active"><span class="toggle-switch__slider"></span><span class="toggle-switch__label">Kích hoạt</span></label></div>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn--primary">Lưu thay đổi</button>
                <button type="button" class="btn btn--ghost" onclick="document.getElementById('modal-edit-coupon').classList.remove('is-open')">Huỷ</button>
            </div>
        </form>
    </div>
</div>

<script>
function toLocalDT(s) {
    if (!s) return '';
    return new Date(s.replace(' ', 'T')).toISOString().slice(0,16);
}
function openEditCoupon(c) {
    document.getElementById('edit-coupon-code-label').textContent = c.code;
    document.getElementById('edit-coupon-name').value         = c.name ?? '';
    document.getElementById('edit-coupon-type').value         = c.type;
    document.getElementById('edit-coupon-value').value        = c.value;
    document.getElementById('edit-coupon-max-discount').value = c.max_discount ?? '';
    document.getElementById('edit-coupon-min-order').value    = c.min_order_value ?? '';
    document.getElementById('edit-coupon-max-uses').value     = c.max_uses ?? '';
    document.getElementById('edit-coupon-starts').value       = toLocalDT(c.starts_at);
    document.getElementById('edit-coupon-ends').value         = toLocalDT(c.ends_at);
    document.getElementById('edit-coupon-active').checked     = c.is_active == 1;
    document.getElementById('form-edit-coupon').action        = `<?= base_url('/admin/discounts/') ?>${c.id}`;
    document.getElementById('modal-edit-coupon').classList.add('is-open');
}
</script>
