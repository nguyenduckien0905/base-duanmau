<!-- Giới thiệu chức năng kiểm duyệt. -->
<div class="toolbar">
    <p>Ẩn đánh giá không phù hợp hoặc xóa đánh giá khỏi hệ thống.</p>
</div>

<!-- Bảng đánh giá. -->
<div class="panel">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Khách hàng</th>
                    <th>Sản phẩm</th>
                    <th>Số sao</th>
                    <th>Nội dung</th>
                    <th>Ngày đánh giá</th>
                    <th>Trạng thái</th>
                    <th class="text-right">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($reviews as $review): ?>
                    <tr>
                        <td>
                            <strong><?= e($review['fullname']) ?></strong>
                            <small class="table-subtext"><?= e($review['email']) ?></small>
                        </td>
                        <td><?= e($review['product_name']) ?></td>
                        <td><span class="stars"><?= str_repeat('★', (int) $review['rating']) ?></span></td>
                        <td class="comment-cell"><?= e($review['comment'] ?: 'Không có nội dung') ?></td>
                        <td><?= e(date('d/m/Y H:i', strtotime($review['created_at']))) ?></td>
                        <td>
                            <span class="badge <?= (int) $review['status'] === 1 ? 'badge-success' : 'badge-muted' ?>">
                                <?= (int) $review['status'] === 1 ? 'Đang hiện' : 'Đã ẩn' ?>
                            </span>
                        </td>
                        <td class="actions">
                            <form
                                action="<?= e(url('admin/reviews/toggle-status', ['id' => $review['review_id']])) ?>"
                                method="post"
                            >
                                <?= csrfField() ?>
                                <button class="btn btn-light btn-small" type="submit">
                                    <?= (int) $review['status'] === 1 ? 'Ẩn' : 'Hiện' ?>
                                </button>
                            </form>

                            <form
                                action="<?= e(url('admin/reviews/delete', ['id' => $review['review_id']])) ?>"
                                method="post"
                                data-confirm="Bạn có chắc muốn xóa vĩnh viễn đánh giá này?"
                            >
                                <?= csrfField() ?>
                                <button class="btn btn-danger btn-small" type="submit">Xóa</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>

                <?php if (empty($reviews)): ?>
                    <tr>
                        <td colspan="7" class="empty-state">Chưa có đánh giá.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
