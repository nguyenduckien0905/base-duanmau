<!-- Chi tiết đơn đã được controller kiểm tra quyền sở hữu. -->
<section class="section">
    <div class="container">
        <div class="section-heading">
            <div>
                <h1>Đơn hàng #<?= e($order['order_id']) ?></h1>
                <p>Đặt lúc <?= e(date('d/m/Y H:i', strtotime($order['created_at']))) ?></p>
            </div>
            <span class="status-pill status-<?= e($order['status']) ?>">
                <?= e(orderStatusText($order['status'])) ?>
            </span>
        </div>

        <div class="checkout-layout">
            <div>
                <div class="checkout-panel">
                    <h2>Thông tin nhận hàng</h2>
                    <div class="spec-row"><strong>Người nhận</strong><span><?= e($order['receiver_name']) ?></span></div>
                    <div class="spec-row"><strong>Điện thoại</strong><span><?= e($order['receiver_phone']) ?></span></div>
                    <div class="spec-row"><strong>Địa chỉ</strong><span><?= e($order['shipping_address']) ?></span></div>
                    <div class="spec-row"><strong>Ghi chú</strong><span><?= e($order['note'] ?: 'Không có') ?></span></div>
                    <div class="spec-row"><strong>Thanh toán</strong><span><?= e(strtoupper($order['payment_method'] ?? 'COD')) ?></span></div>
                </div>

                <div class="checkout-panel order-products">
                    <h2>Sản phẩm</h2>
                    <?php foreach ($items as $item): ?>
                        <div class="checkout-item">
                            <div>
                                <strong><?= e($item['product_name']) ?></strong>
                                <small>Size: <?= e($item['size'] ?: '-') ?> · Màu: <?= e($item['color'] ?: '-') ?> · SL: <?= e($item['quantity']) ?></small>
                            </div>
                            <strong><?= e(formatPrice($item['price'] * $item['quantity'])) ?></strong>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <aside class="cart-summary">
                <h2>Tổng thanh toán</h2>
                <div class="summary-row"><span>Tạm tính</span><strong><?= e(formatPrice($order['subtotal'])) ?></strong></div>
                <div class="summary-row"><span>Phí giao hàng</span><strong><?= e(formatPrice($order['shipping_fee'])) ?></strong></div>
                <div class="summary-row"><span>Giảm giá</span><strong>-<?= e(formatPrice($order['discount'])) ?></strong></div>
                <div class="summary-row grand-total"><span>Tổng cộng</span><strong><?= e(formatPrice($order['total_price'])) ?></strong></div>
                <a class="btn btn-light btn-full" href="<?= e(url('orders')) ?>">Quay lại lịch sử</a>
            </aside>
        </div>
    </div>
</section>
