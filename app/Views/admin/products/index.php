<?php
/**
 * admin/products/index.php
 * Variables: $products, $paginator, $categories, $brands, $search, $status
 */
$statusColors = ['active'=>'success','draft'=>'warning','inactive'=>'muted'];
$statusLabels = ['active'=>'Đang bán','draft'=>'Nháp','inactive'=>'Ẩn'];
?>

<div class="admin-toolbar">
    <form method="GET" action="<?= base_url('/admin/products') ?>" class="admin-search-form">
        <input type="text" name="search" value="<?= e($search) ?>" placeholder="Tìm theo tên, thương hiệu..." class="admin-input" id="product-search">
        <select name="status" class="admin-select" id="product-status-filter">
            <option value="">Tất cả trạng thái</option>
            <option value="active"   <?= $status === 'active'   ? 'selected' : '' ?>>Đang bán</option>
            <option value="draft"    <?= $status === 'draft'    ? 'selected' : '' ?>>Nháp</option>
            <option value="inactive" <?= $status === 'inactive' ? 'selected' : '' ?>>Ẩn</option>
        </select>
        <button type="submit" class="btn btn--primary" id="btn-product-search">Tìm kiếm</button>
        <a href="<?= base_url('/admin/products') ?>" class="btn btn--ghost">Xóa lọc</a>
    </form>
    <a href="<?= base_url('/admin/products/create') ?>" class="btn btn--primary" id="btn-add-product">+ Thêm sản phẩm</a>
</div>

<div class="admin-table-card">
    <div class="admin-table-card__header">
        <h2 class="admin-table-card__title">Danh sách sản phẩm <span class="badge badge--muted"><?= number_format($paginator['total']) ?></span></h2>
    </div>
    <?php if (empty($products)): ?>
    <div class="admin-empty">Không tìm thấy sản phẩm nào.</div>
    <?php else: ?>
    <div class="table-wrap">
    <table class="admin-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Sản phẩm</th>
                <th>Danh mục</th>
                <th>Thương hiệu</th>
                <th>Giá</th>
                <th>Tồn kho</th>
                <th>Trạng thái</th>
                <th>Hành động</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($products as $p): ?>
        <tr>
            <td class="text-muted">#<?= $p['id'] ?></td>
            <td>
                <div class="product-row">
                    <?php if (!empty($p['thumbnail'])): ?>
                    <img src="<?= e($p['thumbnail']) ?>" alt="" class="product-thumb" loading="lazy">
                    <?php else: ?>
                    <div class="product-thumb product-thumb--placeholder">📦</div>
                    <?php endif; ?>
                    <div>
                        <div class="product-name"><?= e($p['name']) ?></div>
                        <div class="text-muted text-sm"><?= e($p['slug']) ?></div>
                    </div>
                </div>
            </td>
            <td><?= e($p['category_name']) ?></td>
            <td><?= e($p['brand_name']) ?></td>
            <td><?= number_format((float)$p['min_price']) ?>đ</td>
            <td>
                <?php if ((int)$p['total_stock'] <= 5): ?>
                <span class="badge badge--danger"><?= $p['total_stock'] ?></span>
                <?php else: ?>
                <?= $p['total_stock'] ?>
                <?php endif; ?>
            </td>
            <td><span class="badge badge--<?= $statusColors[$p['status']] ?? 'muted' ?>"><?= $statusLabels[$p['status']] ?? e($p['status']) ?></span></td>
            <td>
                <div class="action-group">
                    <a href="<?= base_url('/product/' . e($p['slug'])) ?>" class="btn btn--sm btn--ghost" target="_blank" title="Xem">👁</a>
                    <a href="<?= base_url('/admin/products/' . $p['id'] . '/edit') ?>" class="btn btn--sm btn--outline" title="Sửa">✏️</a>
                    <a href="<?= base_url('/admin/products/' . $p['id'] . '/variants') ?>" class="btn btn--sm btn--ghost" title="Biến thể">🔧</a>
                    <form method="POST" action="<?= base_url('/admin/products/' . $p['id']) ?>" class="form-inline" onsubmit="return confirm('Xác nhận xóa sản phẩm này?')">
                        <?= csrf_field() ?>
                        <input type="hidden" name="_method" value="DELETE">
                        <button type="submit" class="btn btn--sm btn--danger" title="Xóa">🗑</button>
                    </form>
                </div>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>

    <!-- Pagination -->
    <?php if ($paginator['last_page'] > 1): ?>
    <div class="admin-pagination">
        <?php if ($paginator['current_page'] > 1): ?>
        <a href="?page=<?= $paginator['current_page'] - 1 ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($status) ?>" class="btn btn--ghost btn--sm">← Trước</a>
        <?php endif; ?>
        <span class="pagination-info">Trang <?= $paginator['current_page'] ?> / <?= $paginator['last_page'] ?></span>
        <?php if ($paginator['current_page'] < $paginator['last_page']): ?>
        <a href="?page=<?= $paginator['current_page'] + 1 ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($status) ?>" class="btn btn--ghost btn--sm">Tiếp →</a>
        <?php endif; ?>
    </div>
    <?php endif; ?>
    <?php endif; ?>
</div>
