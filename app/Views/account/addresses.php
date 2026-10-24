<?php /** account/addresses.php — Variables: $addresses */ ?>
<div class="account-page">
  <div class="container">
    <div class="account-grid">
      <?php include __DIR__ . '/../partials/account-nav.php'; ?>
      <div class="account-content">
        <h1 class="account-content__title">Địa chỉ của tôi</h1>
        <?php if ($success = \App\Core\Session::getFlash('success')): ?>
        <div class="alert alert--success"><?= e($success) ?></div>
        <?php endif; ?>

        <div class="addresses-grid">
          <?php foreach ($addresses as $addr): ?>
          <div class="address-card <?= $addr['is_default'] ? 'address-card--default' : '' ?>">
            <?php if ($addr['is_default']): ?><span class="address-card__badge">Mặc định</span><?php endif; ?>
            <div class="address-card__name"><?= e($addr['recipient_name']) ?></div>
            <div class="address-card__phone"><?= e($addr['phone']) ?></div>
            <div class="address-card__address">
              <?= e($addr['address_line']) ?>, <?= e($addr['ward']) ?>, <?= e($addr['district']) ?>, <?= e($addr['province']) ?>
            </div>
            <div class="address-card__actions">
              <button class="btn btn--sm btn--ghost" onclick='openEditAddr(<?= htmlspecialchars(json_encode($addr), ENT_QUOTES) ?>)'>✏️ Sửa</button>
              <button class="btn btn--sm btn--ghost text-danger" onclick="deleteAddr(<?= $addr['id'] ?>)">🗑 Xóa</button>
            </div>
          </div>
          <?php endforeach; ?>

          <!-- Add new -->
          <button class="address-card address-card--add" onclick="document.getElementById('modal-add-addr').classList.add('is-open')">
            <span class="address-card__add-icon">＋</span>
            <span>Thêm địa chỉ mới</span>
          </button>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Add Modal -->
<div class="modal" id="modal-add-addr">
  <div class="modal__backdrop" onclick="this.parentElement.classList.remove('is-open')"></div>
  <div class="modal__dialog">
    <div class="modal__header"><h3>Thêm địa chỉ mới</h3><button class="modal__close" onclick="document.getElementById('modal-add-addr').classList.remove('is-open')">✕</button></div>
    <form method="POST" action="<?= base_url('/account/addresses') ?>" class="account-form">
      <?= csrf_field() ?>
      <?php include __DIR__ . '/partials/address-fields.php'; ?>
      <div class="form-actions">
        <button type="submit" class="btn btn--primary">Thêm địa chỉ</button>
        <button type="button" class="btn btn--ghost" onclick="document.getElementById('modal-add-addr').classList.remove('is-open')">Huỷ</button>
      </div>
    </form>
  </div>
</div>

<!-- Edit Modal -->
<div class="modal" id="modal-edit-addr">
  <div class="modal__backdrop" onclick="this.parentElement.classList.remove('is-open')"></div>
  <div class="modal__dialog">
    <div class="modal__header"><h3>Sửa địa chỉ</h3><button class="modal__close" onclick="document.getElementById('modal-edit-addr').classList.remove('is-open')">✕</button></div>
    <form method="POST" id="form-edit-addr" class="account-form">
      <?= csrf_field() ?>
      <div class="form-group"><label class="form-label">Họ tên người nhận</label><input type="text" name="recipient_name" class="form-control" required id="edit-addr-name"></div>
      <div class="form-group"><label class="form-label">Số điện thoại</label><input type="tel" name="phone" class="form-control" required id="edit-addr-phone"></div>
      <div class="form-group"><label class="form-label">Địa chỉ chi tiết</label><input type="text" name="address_line" class="form-control" required id="edit-addr-line"></div>
      <div class="form-grid form-grid--2">
        <div class="form-group"><label class="form-label">Quận/Huyện</label><input type="text" name="district" class="form-control" id="edit-addr-district"></div>
        <div class="form-group"><label class="form-label">Tỉnh/Thành phố</label><input type="text" name="province" class="form-control" id="edit-addr-province"></div>
      </div>
      <div class="form-group"><label class="toggle-switch"><input type="checkbox" name="is_default" value="1" id="edit-addr-default"><span class="toggle-switch__slider"></span><span class="toggle-switch__label">Đặt làm địa chỉ mặc định</span></label></div>
      <div class="form-actions">
        <button type="submit" class="btn btn--primary">Lưu</button>
        <button type="button" class="btn btn--ghost" onclick="document.getElementById('modal-edit-addr').classList.remove('is-open')">Huỷ</button>
      </div>
    </form>
  </div>
</div>

<script>
function openEditAddr(addr) {
  document.getElementById('edit-addr-name').value     = addr.recipient_name;
  document.getElementById('edit-addr-phone').value    = addr.phone;
  document.getElementById('edit-addr-line').value     = addr.address_line;
  document.getElementById('edit-addr-district').value = addr.district;
  document.getElementById('edit-addr-province').value = addr.province;
  document.getElementById('edit-addr-default').checked = addr.is_default == 1;
  document.getElementById('form-edit-addr').action = `<?= base_url('/account/addresses/') ?>${addr.id}`;
  document.getElementById('modal-edit-addr').classList.add('is-open');
}
async function deleteAddr(id) {
  if (!confirm('Xóa địa chỉ này?')) return;
  const res = await Http.delete(`/api/v1/account/addresses/${id}`);
  if (res.ok) { Toast?.success?.('Đã xóa.'); setTimeout(() => location.reload(), 800); }
  else { Toast?.error?.('Không thể xóa.'); }
}
</script>