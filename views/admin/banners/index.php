<!-- Thanh công cụ banner. -->
<div class="toolbar">
    <p>Quản lý hình ảnh quảng bá hiển thị ở phần Client.</p>
    <a class="btn btn-primary" href="<?= e(url('admin/banners/create')) ?>">
        + Thêm banner
    </a>
</div>

<!-- Bảng banner. -->
<div class="panel">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Ảnh</th>
                    <th>Tiêu đề</th>
                    <th>Đường dẫn</th>
                    <th>Trạng thái</th>
                    <th class="text-right">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($banners as $banner): ?>
                    <tr>
                        <td>#<?= e($banner['banner_id']) ?></td>
                        <td>
                            <img
                                class="banner-thumb"
                                src="<?= e(BASE_ASSETS_UPLOADS . $banner['image']) ?>"
                                alt="<?= e($banner['title']) ?>"
                            >
                        </td>
                        <td><strong><?= e($banner['title']) ?></strong></td>
                        <td class="link-cell"><?= e($banner['link'] ?: 'Không có') ?></td>
                        <td>
                            <span class="badge <?= (int) $banner['status'] === 1 ? 'badge-success' : 'badge-muted' ?>">
                                <?= (int) $banner['status'] === 1 ? 'Đang hiện' : 'Đã ẩn' ?>
                            </span>
                        </td>
                        <td class="actions">
                            <a
                                class="btn btn-light btn-small"
                                href="<?= e(url('admin/banners/edit', ['id' => $banner['banner_id']])) ?>"
                            >
                                Sửa
                            </a>

                            <form
                                action="<?= e(url('admin/banners/delete', ['id' => $banner['banner_id']])) ?>"
                                method="post"
                                data-confirm="Bạn có chắc muốn xóa banner này?"
                            >
                                <?= csrfField() ?>
                                <button class="btn btn-danger btn-small" type="submit">Xóa</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>

                <?php if (empty($banners)): ?>
                    <tr>
                        <td colspan="6" class="empty-state">Chưa có banner.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
