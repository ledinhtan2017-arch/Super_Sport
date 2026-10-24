<?php
/**
 * admin/dashboard/index.php
 * Variables: $stats, $recentOrders
 */
function fmt(float $n): string { return number_format($n, 0, ',', '.'); }
$statusColors = [
    'pending'=>'warning','confirmed'=>'info','processing'=>'primary',
    'shipping'=>'accent','delivered'=>'success','cancelled'=>'danger','refunded'=>'muted'
];
?>
<div class="dash-grid">
    <!-- KPI Cards -->
    <div class="kpi-card kpi-card--blue">
        <div class="kpi-card__icon">💰</div>
        <div class="kpi-card__body">
            <div class="kpi-card__label">Doanh thu tháng</div>
            <div class="kpi-card__value"><?= fmt($stats['revenue'] ?? 0) ?>đ</div>
        </div>
    </div>
    <div class="kpi-card kpi-card--green">
        <div class="kpi-card__icon">📋</div>
        <div class="kpi-card__body">
            <div class="kpi-card__label">Tổng đơn hàng</div>
            <div class="kpi-card__value"><?= number_format($stats['orders'] ?? 0) ?></div>
        </div>
    </div>
    <div class="kpi-card kpi-card--orange">
        <div class="kpi-card__icon">⏳</div>
        <div class="kpi-card__body">
            <div class="kpi-card__label">Chờ xác nhận</div>
            <div class="kpi-card__value"><?= number_format($stats['pending'] ?? 0) ?></div>
        </div>
    </div>
    <div class="kpi-card kpi-card--purple">
        <div class="kpi-card__icon">👥</div>
        <div class="kpi-card__body">
            <div class="kpi-card__label">Khách hàng</div>
            <div class="kpi-card__value"><?= number_format($stats['customers'] ?? 0) ?></div>
        </div>
    </div>
    <div class="kpi-card kpi-card--teal">
        <div class="kpi-card__icon">📦</div>
        <div class="kpi-card__body">
            <div class="kpi-card__label">Sản phẩm</div>
            <div class="kpi-card__value"><?= number_format($stats['products'] ?? 0) ?></div>
        </div>
    </div>
    <div class="kpi-card kpi-card--red">
        <div class="kpi-card__icon">📅</div>
        <div class="kpi-card__body">
            <div class="kpi-card__label">Đơn hôm nay</div>
            <div class="kpi-card__value"><?= number_format($stats['orders_today'] ?? 0) ?></div>
        </div>
    </div>
</div>

<!-- Quick Links -->
<div class="admin-quick-links">
    <a href="<?= base_url('/admin/products/create') ?>" class="btn btn--primary">+ Thêm sản phẩm</a>
    <a href="<?= base_url('/admin/orders') ?>" class="btn btn--outline">Xem đơn hàng</a>
    <a href="<?= base_url('/admin/categories') ?>" class="btn btn--outline">Danh mục</a>
    <a href="<?= base_url('/admin/brands') ?>" class="btn btn--outline">Thương hiệu</a>
    <a href="<?= base_url('/admin/discounts') ?>" class="btn btn--outline">Khuyến mãi</a>
    <a href="<?= base_url('/admin/reports') ?>" class="btn btn--outline">Báo cáo</a>
</div>

<!-- Recent Orders -->
<div class="admin-table-card">
    <div class="admin-table-card__header">
        <h2 class="admin-table-card__title">Đơn hàng gần đây</h2>
        <a href="<?= base_url('/admin/orders') ?>" class="btn btn--sm btn--ghost">Xem tất cả →</a>
    </div>
    <?php if (empty($recentOrders)): ?>
    <div class="admin-empty">Chưa có đơn hàng nào.</div>
    <?php else: ?>
    <div class="table-wrap">
    <table class="admin-table">
        <thead>
            <tr>
                <th>Mã đơn</th>
                <th>Khách hàng</th>
                <th>Tổng tiền</th>
                <th>Trạng thái</th>
                <th>Ngày tạo</th>
                <th>Hành động</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($recentOrders as $order): ?>
        <tr>
            <td><a href="<?= base_url('/admin/orders/' . $order['id']) ?>" class="admin-link"><?= e($order['order_number']) ?></a></td>
            <td><?= e($order['customer_name'] ?? '—') ?></td>
            <td><?= number_format((float)$order['total']) ?>đ</td>
            <td><span class="badge badge--<?= $statusColors[$order['status']] ?? 'muted' ?>"><?= \App\Models\Order::statusLabel($order['status']) ?></span></td>
            <td><?= date('d/m/Y', strtotime($order['created_at'])) ?></td>
            <td><a href="<?= base_url('/admin/orders/' . $order['id']) ?>" class="btn btn--sm btn--ghost">Chi tiết</a></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
    <?php endif; ?>
</div>
