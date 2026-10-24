<?php
/**
 * account/orders.php
 * Variables: $orders, $paginator
 */
$statusColors = ['pending'=>'warning','confirmed'=>'info','processing'=>'primary','shipping'=>'accent','delivered'=>'success','cancelled'=>'danger','refunded'=>'muted'];
?>
<div class="account-page">
  <div class="container">
    <div class="account-grid">
      <?php include __DIR__ . '/../partials/account-nav.php'; ?>
      <div class="account-content">
        <h1 class="account-content__title">Đơn hàng của tôi</h1>

        <?php if (empty($orders)): ?>
        <div class="account-card">
          <div class="account-empty">
            <div class="account-empty__icon">📦</div>
            <p>Bạn chưa có đơn hàng nào.</p>
            <a href="<?= base_url('/shop') ?>" class="btn btn--primary">Mua sắm ngay</a>
          </div>
        </div>
        <?php else: ?>
        <div class="account-card account-card--no-pad">
          <div class="table-wrap">
          <table class="account-table">
            <thead>
              <tr><th>Mã đơn</th><th>Sản phẩm</th><th>Tổng tiền</th><th>Trạng thái</th><th>Ngày đặt</th><th></th></tr>
            </thead>
            <tbody>
            <?php foreach ($orders as $o): ?>
            <tr>
              <td><strong><?= e($o['order_number']) ?></strong></td>
              <td><?= $o['item_count'] ?> sản phẩm</td>
              <td><?= format_price((float)$o['total']) ?></td>
              <td><span class="badge badge--<?= $statusColors[$o['status']] ?? 'muted' ?>"><?= \App\Models\Order::statusLabel($o['status']) ?></span></td>
              <td><?= date('d/m/Y', strtotime($o['created_at'])) ?></td>
              <td><a href="<?= base_url('/account/orders/'.$o['id']) ?>" class="btn btn--sm btn--outline">Chi tiết</a></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
          </div>
        </div>
        <?php if ($paginator['last_page'] > 1): ?>
        <div class="pagination">
          <?php if ($paginator['current_page'] > 1): ?>
          <a href="?page=<?= $paginator['current_page']-1 ?>" class="btn btn--ghost btn--sm">← Trước</a>
          <?php endif; ?>
          <span class="pagination-info">Trang <?= $paginator['current_page'] ?> / <?= $paginator['last_page'] ?></span>
          <?php if ($paginator['current_page'] < $paginator['last_page']): ?>
          <a href="?page=<?= $paginator['current_page']+1 ?>" class="btn btn--ghost btn--sm">Tiếp →</a>
          <?php endif; ?>
        </div>
        <?php endif; ?>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>