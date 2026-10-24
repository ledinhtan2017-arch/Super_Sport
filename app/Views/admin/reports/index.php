<?php
/**
 * admin/reports/index.php
 * Variables: $stats, $total_customers, $total_products
 */
function fmt(float $n): string { return number_format($n, 0, ',', '.'); }
?>
<div class="dash-grid">
    <div class="kpi-card kpi-card--blue">
        <div class="kpi-card__icon">💰</div>
        <div class="kpi-card__body">
            <div class="kpi-card__label">Tổng doanh thu</div>
            <div class="kpi-card__value"><?= fmt($stats['total_revenue']) ?>đ</div>
        </div>
    </div>
    <div class="kpi-card kpi-card--green">
        <div class="kpi-card__icon">📋</div>
        <div class="kpi-card__body">
            <div class="kpi-card__label">Tổng đơn hàng</div>
            <div class="kpi-card__value"><?= number_format($stats['total_orders']) ?></div>
        </div>
    </div>
    <div class="kpi-card kpi-card--orange">
        <div class="kpi-card__icon">⏳</div>
        <div class="kpi-card__body">
            <div class="kpi-card__label">Đơn chờ xử lý</div>
            <div class="kpi-card__value"><?= number_format($stats['pending']) ?></div>
        </div>
    </div>
    <div class="kpi-card kpi-card--purple">
        <div class="kpi-card__icon">👥</div>
        <div class="kpi-card__body">
            <div class="kpi-card__label">Khách hàng</div>
            <div class="kpi-card__value"><?= number_format($total_customers) ?></div>
        </div>
    </div>
    <div class="kpi-card kpi-card--teal">
        <div class="kpi-card__icon">📦</div>
        <div class="kpi-card__body">
            <div class="kpi-card__label">Sản phẩm</div>
            <div class="kpi-card__value"><?= number_format($total_products) ?></div>
        </div>
    </div>
    <div class="kpi-card kpi-card--blue">
        <div class="kpi-card__icon">📅</div>
        <div class="kpi-card__body">
            <div class="kpi-card__label">Doanh thu tháng này</div>
            <div class="kpi-card__value"><?= fmt($stats['month_revenue']) ?>đ</div>
        </div>
    </div>
</div>

<div class="admin-table-card">
    <div class="admin-table-card__header">
        <h2 class="admin-table-card__title">📈 Báo cáo nâng cao</h2>
    </div>
    <div class="admin-empty">
        <div style="font-size:3rem;margin-bottom:1rem">📊</div>
        <p>Biểu đồ doanh thu, thống kê chi tiết theo ngày/tháng sẽ được phát triển ở Phase 5 (sau khi tích hợp đơn hàng đầy đủ).</p>
        <a href="<?= base_url('/admin/orders') ?>" class="btn btn--primary" style="margin-top:1rem">Xem đơn hàng ngay</a>
    </div>
</div>
