<?php
// Các tham số này được giữ lại khi nhân viên chuyển trang.
$paginationParams = [];

if ($keyword !== '') {
    $paginationParams['keyword'] = $keyword;
}

if ($categoryId > 0) {
    $paginationParams['category_id'] = $categoryId;
}

// Chỉ hiển thị một nhóm nhỏ số trang để danh sách 1.000 sản phẩm vẫn gọn.
$startPage = max(1, $page - 2);
$endPage = min($totalPages, $page + 2);
?>

<!-- Bộ lọc và nút thêm sản phẩm. -->
<div class="toolbar toolbar-wrap">
    <form class="filter-form" action="<?= e(url('admin/products')) ?>" method="get">
        <input type="hidden" name="action" value="admin/products">

        <input
            type="search"
            name="keyword"
            value="<?= e($keyword) ?>"
            placeholder="Tìm tên sản phẩm..."
        >

        <select name="category_id">
            <option value="">Tất cả danh mục</option>

            <?php foreach ($categories as $category): ?>
                <option
                    value="<?= e($category['category_id']) ?>"
                    <?= $categoryId === (int) $category['category_id'] ? 'selected' : '' ?>
                >
                    <?= e($category['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <button class="btn btn-light" type="submit">Lọc</button>
    </form>

    <a class="btn btn-primary" href="<?= e(url('admin/products/create')) ?>">
        + Thêm sản phẩm
    </a>
</div>

<!-- Bảng sản phẩm. -->
<div class="panel">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Sản phẩm</th>
                    <th>Danh mục</th>
                    <th>Thương hiệu</th>
                    <th>Giá</th>
                    <th>Biến thể</th>
                    <th>Tổng tồn kho</th>
                    <th>Trạng thái</th>
                    <th class="text-right">Thao tác</th>
                </tr>
            </thead>

            <tbody>
                <?php foreach ($products as $product): ?>
                    <tr>
                        <td>
                            <div class="product-cell">
                                <?php if ($product['image']): ?>
                                    <img
                                        class="table-image"
                                        src="<?= e(BASE_ASSETS_UPLOADS . $product['image']) ?>"
                                        alt="<?= e($product['product_name']) ?>"
                                    >
                                <?php else: ?>
                                    <span class="image-placeholder">IMG</span>
                                <?php endif; ?>

                                <div>
                                    <strong><?= e($product['product_name']) ?></strong>
                                    <small>#<?= e($product['product_id']) ?></small>
                                </div>
                            </div>
                        </td>

                        <td><?= e($product['category_name']) ?></td>
                        <td><?= e($product['brand_name'] ?? 'Không có') ?></td>
                        <td><strong><?= e(formatPrice($product['price'])) ?></strong></td>

                        <td>
                            <span class="badge badge-info">
                                <?= e($product['variant_count']) ?> biến thể
                            </span>
                        </td>

                        <td>
                            <strong class="<?= (int) $product['total_stock'] <= 10 ? 'stock-low' : '' ?>">
                                <?= e($product['total_stock']) ?>
                            </strong>
                        </td>

                        <td>
                            <span class="badge <?= (int) $product['status'] === 1 ? 'badge-success' : 'badge-muted' ?>">
                                <?= (int) $product['status'] === 1 ? 'Đang bán' : 'Đang ẩn' ?>
                            </span>
                        </td>

                        <td class="actions">
                            <a
                                class="btn btn-light btn-small"
                                href="<?= e(url('admin/products/edit', ['id' => $product['product_id']])) ?>"
                            >
                                Sửa
                            </a>

                            <form
                                action="<?= e(url('admin/products/delete', ['id' => $product['product_id']])) ?>"
                                method="post"
                                data-confirm="Bạn có chắc muốn xóa sản phẩm này?"
                            >
                                <?= csrfField() ?>

                                <button class="btn btn-danger btn-small" type="submit">
                                    Xóa
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>

                <?php if (empty($products)): ?>
                    <tr>
                        <td colspan="8" class="empty-state">
                            Không tìm thấy sản phẩm.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if ($totalProducts > 0): ?>
        <div class="pagination-footer">
            <p class="pagination-summary">
                Hiển thị <?= e($fromProduct) ?>–<?= e($toProduct) ?>
                trong <?= e($totalProducts) ?> sản phẩm
            </p>

            <?php if ($totalPages > 1): ?>
                <nav class="pagination" aria-label="Phân trang sản phẩm Admin">
                    <?php if ($page > 1): ?>
                        <a
                            class="pagination-link"
                            href="<?= e(url(
                                'admin/products',
                                array_merge($paginationParams, ['page' => $page - 1])
                            )) ?>"
                        >
                            ‹ Trước
                        </a>
                    <?php else: ?>
                        <span class="pagination-link disabled">‹ Trước</span>
                    <?php endif; ?>

                    <?php if ($startPage > 1): ?>
                        <a
                            class="pagination-link"
                            href="<?= e(url(
                                'admin/products',
                                array_merge($paginationParams, ['page' => 1])
                            )) ?>"
                        >1</a>

                        <?php if ($startPage > 2): ?>
                            <span class="pagination-ellipsis">…</span>
                        <?php endif; ?>
                    <?php endif; ?>

                    <?php for ($pageNumber = $startPage; $pageNumber <= $endPage; $pageNumber++): ?>
                        <a
                            class="pagination-link <?= $pageNumber === $page ? 'active' : '' ?>"
                            href="<?= e(url(
                                'admin/products',
                                array_merge($paginationParams, ['page' => $pageNumber])
                            )) ?>"
                            <?= $pageNumber === $page ? 'aria-current="page"' : '' ?>
                        >
                            <?= e($pageNumber) ?>
                        </a>
                    <?php endfor; ?>

                    <?php if ($endPage < $totalPages): ?>
                        <?php if ($endPage < $totalPages - 1): ?>
                            <span class="pagination-ellipsis">…</span>
                        <?php endif; ?>

                        <a
                            class="pagination-link"
                            href="<?= e(url(
                                'admin/products',
                                array_merge($paginationParams, ['page' => $totalPages])
                            )) ?>"
                        ><?= e($totalPages) ?></a>
                    <?php endif; ?>

                    <?php if ($page < $totalPages): ?>
                        <a
                            class="pagination-link"
                            href="<?= e(url(
                                'admin/products',
                                array_merge($paginationParams, ['page' => $page + 1])
                            )) ?>"
                        >
                            Sau ›
                        </a>
                    <?php else: ?>
                        <span class="pagination-link disabled">Sau ›</span>
                    <?php endif; ?>
                </nav>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>
