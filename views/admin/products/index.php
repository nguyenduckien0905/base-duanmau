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
                    <th>Tồn kho</th>
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
                            <span class="<?= (int) $product['stock'] <= 10 ? 'stock-low' : '' ?>">
                                <?= e($product['stock']) ?>
                            </span>
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
                                <button class="btn btn-danger btn-small" type="submit">Xóa</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>

                <?php if (empty($products)): ?>
                    <tr>
                        <td colspan="7" class="empty-state">Không tìm thấy sản phẩm.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
