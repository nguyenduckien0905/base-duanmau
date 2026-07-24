<!-- Thanh công cụ của trang thương hiệu. -->
<div class="toolbar">
    <p>Quản lý nhãn hiệu được gắn với sản phẩm.</p>
    <a class="btn btn-primary" href="<?= e(url('admin/brands/create')) ?>">
        + Thêm thương hiệu
    </a>
</div>

<!-- Bảng thương hiệu. -->
<div class="panel">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Logo</th>
                    <th>Tên thương hiệu</th>
                    <th>Trạng thái</th>
                    <th class="text-right">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($brands as $brand): ?>
                    <tr>
                        <td>#<?= e($brand['brand_id']) ?></td>
                        <td>
                            <?php if ($brand['logo']): ?>
                                <img
                                    class="table-image"
                                    src="<?= e(BASE_ASSETS_UPLOADS . $brand['logo']) ?>"
                                    alt="<?= e($brand['brand_name']) ?>"
                                >
                            <?php else: ?>
                                <span class="image-placeholder">LOGO</span>
                            <?php endif; ?>
                        </td>
                        <td><strong><?= e($brand['brand_name']) ?></strong></td>
                        <td>
                            <span class="badge <?= (int) $brand['status'] === 1 ? 'badge-success' : 'badge-muted' ?>">
                                <?= (int) $brand['status'] === 1 ? 'Hoạt động' : 'Đang ẩn' ?>
                            </span>
                        </td>
                        <td class="actions">
                            <a
                                class="btn btn-light btn-small"
                                href="<?= e(url('admin/brands/edit', ['id' => $brand['brand_id']])) ?>"
                            >
                                Sửa
                            </a>

                            <form
                                action="<?= e(url('admin/brands/delete', ['id' => $brand['brand_id']])) ?>"
                                method="post"
                                data-confirm="Bạn có chắc muốn xóa thương hiệu này?"
                            >
                                <?= csrfField() ?>
                                <button class="btn btn-danger btn-small" type="submit">Xóa</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>

                <?php if (empty($brands)): ?>
                    <tr>
                        <td colspan="5" class="empty-state">Chưa có thương hiệu.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
