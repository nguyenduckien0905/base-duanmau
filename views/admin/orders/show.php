<!-- Quay lại và cập nhật trạng thái giao hàng. -->
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

            <button class="btn btn-primary" type="submit">Cập nhật giao hàng</button>
        </form>
    <?php endif; ?>
</div>

<?php if (
    $order['payment_method'] === 'bank_transfer'
    && $order['payment_status'] !== 'paid'
): ?>
    <div class="alert alert-warning">
        Đơn chuyển khoản chỉ được xác nhận sau khi nhân viên kiểm tra ảnh
        minh chứng và chuyển trạng thái thanh toán sang “Đã thanh toán”.
    </div>
<?php endif; ?>

<!-- Thông tin khách hàng và trạng thái đơn. -->
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
            <div>
                <dt>Mã giảm giá</dt>
                <dd><?= e($order['coupon_code'] ?: 'Không sử dụng') ?></dd>
            </div>

            <?php if (!empty($order['delivered_at'])): ?>
                <div>
                    <dt>Đã giao lúc</dt>
                    <dd><?= e(date('d/m/Y H:i', strtotime($order['delivered_at']))) ?></dd>
                </div>
            <?php endif; ?>

            <?php if (!empty($order['completed_at'])): ?>
                <div>
                    <dt>Khách nhận lúc</dt>
                    <dd><?= e(date('d/m/Y H:i', strtotime($order['completed_at']))) ?></dd>
                </div>
            <?php endif; ?>
        </dl>
    </div>

    <div class="panel detail-card payment-review-card">
        <div class="panel-heading">
            <h2>Kiểm tra thanh toán</h2>
            <span class="badge <?= e(paymentStatusClass($order['payment_status'] ?? null)) ?>">
                <?= e(paymentStatusText($order['payment_status'] ?? null)) ?>
            </span>
        </div>

        <dl>
            <div>
                <dt>Phương thức</dt>
                <dd><?= e(paymentMethodText($order['payment_method'] ?? null)) ?></dd>
            </div>
            <div>
                <dt>Mã giao dịch</dt>
                <dd><?= e($order['transaction_id'] ?: 'Không có') ?></dd>
            </div>
            <div>
                <dt>Ghi chú duyệt</dt>
                <dd><?= e($order['admin_note'] ?: 'Chưa có') ?></dd>
            </div>
        </dl>

        <?php if (!empty($order['proof_image'])): ?>
            <div class="payment-proof-preview">
                <p><strong>Ảnh minh chứng khách đã gửi</strong></p>
                <a
                    href="<?= e(BASE_ASSETS_UPLOADS . $order['proof_image']) ?>"
                    target="_blank"
                    rel="noopener"
                >
                    <img
                        src="<?= e(BASE_ASSETS_UPLOADS . $order['proof_image']) ?>"
                        alt="Minh chứng thanh toán đơn #<?= e($order['order_id']) ?>"
                    >
                </a>
                <small>Bấm vào ảnh để xem kích thước đầy đủ.</small>
            </div>
        <?php elseif ($order['payment_method'] === 'bank_transfer'): ?>
            <div class="empty-state">Khách chưa gửi ảnh minh chứng.</div>
        <?php endif; ?>

        <form
            class="payment-status-form"
            action="<?= e(url('admin/orders/update-payment', ['id' => $order['order_id']])) ?>"
            method="post"
        >
            <?= csrfField() ?>

            <label>
                Trạng thái thanh toán
                <select name="payment_status" required>
                    <option value="pending" <?= ($order['payment_status'] ?? '') === 'pending' ? 'selected' : '' ?>>Chờ kiểm tra</option>
                    <option value="paid" <?= ($order['payment_status'] ?? '') === 'paid' ? 'selected' : '' ?>>Đã thanh toán</option>
                    <option value="failed" <?= ($order['payment_status'] ?? '') === 'failed' ? 'selected' : '' ?>>Minh chứng không hợp lệ</option>
                </select>
            </label>

            <label>
                Ghi chú cho khách hàng
                <textarea name="admin_note" rows="3" maxlength="500" placeholder="VD: Ảnh mờ, vui lòng liên hệ cửa hàng..."><?= e($order['admin_note'] ?? '') ?></textarea>
            </label>

            <button class="btn btn-primary" type="submit">
                Lưu trạng thái thanh toán
            </button>
        </form>
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
                            <strong><?= e(formatPrice((float) $item['price'] * (int) $item['quantity'])) ?></strong>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

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
