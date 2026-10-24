<?php
/**
 * admin/products/variants.php
 * Variables: $product, $variants
 */
?>
<div class="admin-back-link">
    <a href="<?= base_url('/admin/products/' . $product['id'] . '/edit') ?>" class="btn btn--ghost">← Quay lại sản phẩm</a>
</div>

<div class="admin-table-card">
    <div class="admin-table-card__header">
        <h2 class="admin-table-card__title">Biến thể sản phẩm <span class="badge badge--muted"><?= count($variants) ?></span></h2>
        <div class="text-muted text-sm"><?= e($product['name']) ?></div>
    </div>
    <?php if (empty($variants)): ?>
    <div class="admin-empty">
        <p>Chưa có biến thể nào. Biến thể được quản lý qua database hoặc sẽ có giao diện thêm ở Phase 5.</p>
    </div>
    <?php else: ?>
    <div class="table-wrap">
    <table class="admin-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>SKU</th>
                <th>Biến thể</th>
                <th>Giá gốc</th>
                <th>Giá sale</th>
                <th>Tồn kho</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($variants as $v): ?>
        <tr>
            <td class="text-muted">#<?= $v['id'] ?></td>
            <td><code><?= e($v['sku']) ?></code></td>
            <td><?= e($v['variant_label'] ?? '—') ?></td>
            <td><?= number_format((float)$v['price']) ?>đ</td>
            <td><?= $v['sale_price'] ? number_format((float)$v['sale_price']).'đ' : '<span class="text-muted">—</span>' ?></td>
            <td>
                <?php if ((int)$v['stock_quantity'] <= 5): ?>
                <span class="badge badge--danger"><?= $v['stock_quantity'] ?></span>
                <?php else: ?>
                <?= $v['stock_quantity'] ?>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
    <?php endif; ?>
</div>
