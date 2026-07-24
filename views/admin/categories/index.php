<!-- Thanh công cụ phía trên bảng. -->
<div class="toolbar">
    <p>Quản lý nhóm sản phẩm và cấu trúc danh mục cha - con.</p>
    <a class="btn btn-primary" href="<?= e(url('admin/categories/create')) ?>">
        + Thêm danh mục
    </a>
</div>

<!-- Bảng danh sách danh mục. -->
<div class="panel">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Ảnh</th>
                    <th>Tên danh mục</th>
                    <th>Danh mục cha</th>
                    <th>Trạng thái</th>
                    <th class="text-right">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($categories as $category): ?>
                    <tr>
                        <td>#<?= e($category['category_id']) ?></td>
                        <td>
                            <?php if ($category['image']): ?>
                                <img
                                    class="table-image"
                                    src="<?= e(BASE_ASSETS_UPLOADS . $category['image']) ?>"
                                    alt="<?= e($category['name']) ?>"
                                >
                            <?php else: ?>
                                <span class="image-placeholder">IMG</span>
                            <?php endif; ?>
                        </td>
                        <td><strong><?= e($category['name']) ?></strong></td>
                        <td><?= e($category['parent_name'] ?? 'Danh mục gốc') ?></td>
                        <td>
                            <span class="badge <?= (int) $category['status'] === 1 ? 'badge-success' : 'badge-muted' ?>">
                                <?= (int) $category['status'] === 1 ? 'Hiển thị' : 'Đang ẩn' ?>
                            </span>
                        </td>
                        <td class="actions">
                            <a
                                class="btn btn-light btn-small"
                                href="<?= e(url('admin/categories/edit', ['id' => $category['category_id']])) ?>"
                            >
                                Sửa
                            </a>

                            <form
                                action="<?= e(url('admin/categories/delete', ['id' => $category['category_id']])) ?>"
                                method="post"
                                data-confirm="Bạn có chắc muốn xóa danh mục này?"
                            >
                                <?= csrfField() ?>
                                <button class="btn btn-danger btn-small" type="submit">Xóa</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>

                <?php if (empty($categories)): ?>
                    <tr>
                        <td colspan="6" class="empty-state">Chưa có danh mục.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
