<?php
/**
 * admin/orders/index.php
 * Variables: $orders, $paginator, $filters
 */
$statusColors  = ['pending'=>'warning','confirmed'=>'info','processing'=>'primary','shipping'=>'accent','delivered'=>'success','cancelled'=>'danger','refunded'=>'muted'];
$paymentColors = ['pending'=>'warning','paid'=>'success','failed'=>'danger','refunded'=>'muted'];
?>
<div class="admin-toolbar">
    <form method="GET" action="<?= base_url('/admin/orders') ?>" class="admin-search-form">
        <input type="text" name="search" value="<?= e($filters['search'] ?? '') ?>" placeholder="Mã đơn, tên, email..." class="admin-input" id="order-search">
        <select name="status" class="admin-select" id="order-status-filter">
            <option value="">Tất cả trạng thái</option>
            <?php foreach (['pending'=>'Chờ xác nhận','confirmed'=>'Đã xác nhận','processing'=>'Đang xử lý','shipping'=>'Đang giao','delivered'=>'Đã giao','cancelled'=>'Đã huỷ','refunded'=>'Hoàn tiền'] as $s => $l): ?>
            <option value="<?= $s ?>" <?= ($filters['status'] ?? '') === $s ? 'selected' : '' ?>><?= $l ?></option>
            <?php endforeach; ?>
        </select>
        <select name="payment_status" class="admin-select" id="order-payment-filter">
            <option value="">Tất cả thanh toán</option>
            <?php foreach (['pending'=>'Chưa TT','paid'=>'Đã TT','failed'=>'Thất bại','refunded'=>'Hoàn tiền'] as $s => $l): ?>
            <option value="<?= $s ?>" <?= ($filters['payment_status'] ?? '') === $s ? 'selected' : '' ?>><?= $l ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn--primary">Tìm</button>
        <a href="<?= base_url('/admin/orders') ?>" class="btn btn--ghost">Reset</a>
    </form>
</div>
<div class="admin-table-card">
    <div class="admin-table-card__header">
        <h2 class="admin-table-card__title">Đơn hàng <span class="badge badge--muted"><?= number_format($paginator['total']) ?></span></h2>
    </div>
    <?php if (empty($orders)): ?>
    <div class="admin-empty">Không có đơn hàng nào.</div>
    <?php else: ?>
    <div class="table-wrap">
    <table class="admin-table">
        <thead>
            <tr><th>Mã đơn</th><th>Khách hàng</th><th>Tổng tiền</th><th>Thanh toán</th><th>Trạng thái</th><th>Ngày tạo</th><th>Hành động</th></tr>
        </thead>
        <tbody>
        <?php foreach ($orders as $o): ?>
        <tr>
            <td><a href="<?= base_url('/admin/orders/'.$o['id']) ?>" class="admin-link"><?= e($o['order_number']) ?></a></td>
            <td>
                <div><?= e($o['customer_name'] ?? 'Khách vãng lai') ?></div>
                <div class="text-muted text-sm"><?= e($o['customer_email'] ?? '') ?></div>
            </td>
            <td><?= number_format((float)$o['total']) ?>đ</td>
            <td><span class="badge badge--<?= $paymentColors[$o['payment_status']] ?? 'muted' ?>"><?= \App\Models\Order::paymentLabel($o['payment_status']) ?></span></td>
            <td><span class="badge badge--<?= $statusColors[$o['status']] ?? 'muted' ?>"><?= \App\Models\Order::statusLabel($o['status']) ?></span></td>
            <td><?= date('d/m/Y H:i', strtotime($o['created_at'])) ?></td>
            <td><a href="<?= base_url('/admin/orders/'.$o['id']) ?>" class="btn btn--sm btn--outline">Chi tiết</a></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
    <?php if ($paginator['last_page'] > 1): ?>
    <div class="admin-pagination">
        <?php if ($paginator['current_page'] > 1): ?>
        <a href="?page=<?= $paginator['current_page']-1 ?>&<?= http_build_query($filters) ?>" class="btn btn--ghost btn--sm">← Trước</a>
        <?php endif; ?>
        <span class="pagination-info">Trang <?= $paginator['current_page'] ?> / <?= $paginator['last_page'] ?></span>
        <?php if ($paginator['current_page'] < $paginator['last_page']): ?>
        <a href="?page=<?= $paginator['current_page']+1 ?>&<?= http_build_query($filters) ?>" class="btn btn--ghost btn--sm">Tiếp →</a>
        <?php endif; ?>
    </div>
    <?php endif; ?>
    <?php endif; ?>
</div>
