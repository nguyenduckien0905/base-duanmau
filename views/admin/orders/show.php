<!-- Thao tác quay lại và cập nhật trạng thái. -->
<div class="toolbar">
    <a class="btn btn-light" href="<?= e(url('admin/orders')) ?>">← Quay lại danh sách</a>

    <?php if (!empty($nextStatuses)): ?>
        <form
            class="status-form"
            action="<?= e(url('admin/orders/update-status', ['id' => $order['order_id']])) ?>"
            method="post"
        >
            <?= csrfField() ?>

            <select name="status" required>
                <option value="">-- Chuyển trạng thái --</option>
                <?php foreach ($nextStatuses as $statusCode): ?>
                    <option value="<?= e($statusCode) ?>">
                        <?= e($statusLabels[$statusCode]) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <button class="btn btn-primary" type="submit">Cập nhật</button>
        </form>
    <?php endif; ?>
</div>

<!-- Thông tin khách hàng và thanh toán. -->
<section class="detail-grid">
    <div class="panel detail-card">
        <div class="panel-heading">
            <h2>Thông tin đơn hàng</h2>
            <span class="badge <?= e(orderStatusClass($order['status'])) ?>">
                <?= e(orderStatusText($order['status'])) ?>
            </span>
        </div>

        <dl>
            <div>
                <dt>Ngày đặt</dt>
                <dd><?= e(date('d/m/Y H:i', strtotime($order['created_at']))) ?></dd>
            </div>
            <div>
                <dt>Tài khoản</dt>
                <dd><?= e($order['fullname']) ?> (<?= e($order['email']) ?>)</dd>
            </div>
            <div>
                <dt>Người nhận</dt>
                <dd><?= e($order['receiver_name']) ?> - <?= e($order['receiver_phone']) ?></dd>
            </div>
            <div>
                <dt>Địa chỉ</dt>
                <dd><?= e($order['shipping_address']) ?></dd>
            </div>
            <div>
                <dt>Ghi chú</dt>
                <dd><?= e($order['note'] ?: 'Không có') ?></dd>
            </div>
        </dl>
    </div>

    <div class="panel detail-card">
        <div class="panel-heading">
            <h2>Thanh toán</h2>
        </div>

        <dl>
            <div>
                <dt>Phương thức</dt>
                <dd><?= e(strtoupper($order['payment_method'] ?? 'Chưa có')) ?></dd>
            </div>
            <div>
                <dt>Trạng thái</dt>
                <dd><?= e($order['payment_status'] ?? 'Chưa có') ?></dd>
            </div>
            <div>
                <dt>Mã giao dịch</dt>
                <dd><?= e($order['transaction_id'] ?: 'Không có') ?></dd>
            </div>
        </dl>
    </div>
</section>

<!-- Danh sách sản phẩm trong đơn. -->
<div class="panel">
    <div class="panel-heading">
        <h2>Sản phẩm trong đơn</h2>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Sản phẩm</th>
                    <th>Phân loại</th>
                    <th>Đơn giá</th>
                    <th>Số lượng</th>
                    <th>Thành tiền</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                    <tr>
                        <td><strong><?= e($item['product_name']) ?></strong></td>
                        <td>
                            Size: <?= e($item['size'] ?: '-') ?>,
                            Màu: <?= e($item['color'] ?: '-') ?>
                        </td>
                        <td><?= e(formatPrice($item['price'])) ?></td>
                        <td><?= e($item['quantity']) ?></td>
                        <td>
                            <strong>
                                <?= e(formatPrice((float) $item['price'] * (int) $item['quantity'])) ?>
                            </strong>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Khối cộng tiền của đơn hàng. -->
    <div class="order-totals">
        <div><span>Tạm tính</span><strong><?= e(formatPrice($order['subtotal'])) ?></strong></div>
        <div><span>Phí giao hàng</span><strong><?= e(formatPrice($order['shipping_fee'])) ?></strong></div>
        <div><span>Giảm giá</span><strong>-<?= e(formatPrice($order['discount'])) ?></strong></div>
        <div class="grand-total">
            <span>Tổng thanh toán</span>
            <strong><?= e(formatPrice($order['total_price'])) ?></strong>
        </div>
    </div>
</div>
