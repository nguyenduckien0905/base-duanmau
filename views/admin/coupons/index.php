<!-- Thanh công cụ mã giảm giá. -->
<div class="toolbar">
    <p>Quản lý thời gian, số lượng và giá trị ưu đãi.</p>
    <a class="btn btn-primary" href="<?= e(url('admin/coupons/create')) ?>">
        + Thêm mã giảm giá
    </a>
</div>

<!-- Bảng mã giảm giá. -->
<div class="panel">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Mã</th>
                    <th>Giá trị giảm</th>
                    <th>Đơn tối thiểu</th>
                    <th>Thời gian</th>
                    <th>Số lượng</th>
                    <th>Trạng thái</th>
                    <th class="text-right">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($coupons as $coupon): ?>
                    <tr>
                        <td><code class="coupon-code"><?= e($coupon['code']) ?></code></td>
                        <td>
                            <strong>
                                <?php if ($coupon['discount_type'] === 'percent'): ?>
                                    <?= e($coupon['discount_value']) ?>%
                                <?php else: ?>
                                    <?= e(formatPrice($coupon['discount_value'])) ?>
                                <?php endif; ?>
                            </strong>

                            <?php if ($coupon['max_discount'] !== null): ?>
                                <small class="table-subtext">
                                    Tối đa <?= e(formatPrice($coupon['max_discount'])) ?>
                                </small>
                            <?php endif; ?>
                        </td>
                        <td><?= e(formatPrice($coupon['min_order_value'])) ?></td>
                        <td>
                            <?= e(date('d/m/Y', strtotime($coupon['start_date']))) ?>
                            <small class="table-subtext">
                                đến <?= e(date('d/m/Y', strtotime($coupon['end_date']))) ?>
                            </small>
                        </td>
                        <td><?= e($coupon['quantity']) ?></td>
                        <td>
                            <span class="badge <?= (int) $coupon['status'] === 1 ? 'badge-success' : 'badge-muted' ?>">
                                <?= (int) $coupon['status'] === 1 ? 'Hoạt động' : 'Đang tắt' ?>
                            </span>
                        </td>
                        <td class="actions">
                            <a
                                class="btn btn-light btn-small"
                                href="<?= e(url('admin/coupons/edit', ['id' => $coupon['coupon_id']])) ?>"
                            >
                                Sửa
                            </a>

                            <form
                                action="<?= e(url('admin/coupons/delete', ['id' => $coupon['coupon_id']])) ?>"
                                method="post"
                                data-confirm="Bạn có chắc muốn xóa mã giảm giá này?"
                            >
                                <?= csrfField() ?>
                                <button class="btn btn-danger btn-small" type="submit">Xóa</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>

                <?php if (empty($coupons)): ?>
                    <tr>
                        <td colspan="7" class="empty-state">Chưa có mã giảm giá.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
