<?php
// Mỗi sản phẩm chỉ hiển thị một form đánh giá dù đơn có nhiều biến thể.
$reviewedProductIds = [];
?>

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

        <?php if (
            $order['payment_method'] === 'bank_transfer'
            && $order['payment_status'] === 'pending'
        ): ?>
            <div class="alert alert-info">
                Nhân viên đang kiểm tra minh chứng chuyển khoản và sẽ liên hệ với bạn.
            </div>
        <?php elseif ($order['payment_status'] === 'failed'): ?>
            <div class="alert alert-error">
                Minh chứng thanh toán chưa được chấp nhận.
                <?= e($order['admin_note'] ?: 'Nhân viên sẽ liên hệ để hỗ trợ bạn.') ?>
            </div>
        <?php endif; ?>

        <?php if ($order['status'] === 'delivered'): ?>
            <div class="received-confirmation-card">
                <div>
                    <h2>Đơn hàng đã được giao</h2>
                    <p>Hãy kiểm tra sản phẩm trước khi xác nhận bạn đã nhận hàng.</p>
                </div>

                <form
                    action="<?= e(url('orders/confirm-received', ['id' => $order['order_id']])) ?>"
                    method="post"
                    data-confirm="Bạn xác nhận đã nhận đầy đủ sản phẩm?"
                >
                    <?= csrfField() ?>
                    <button class="btn btn-primary" type="submit">
                        Tôi đã nhận được hàng
                    </button>
                </form>
            </div>
        <?php endif; ?>

        <div class="checkout-layout">
            <div>
                <div class="checkout-panel">
                    <h2>Thông tin nhận hàng</h2>
                    <div class="spec-row"><strong>Người nhận</strong><span><?= e($order['receiver_name']) ?></span></div>
                    <div class="spec-row"><strong>Điện thoại</strong><span><?= e($order['receiver_phone']) ?></span></div>
                    <div class="spec-row"><strong>Địa chỉ</strong><span><?= e($order['shipping_address']) ?></span></div>
                    <div class="spec-row"><strong>Ghi chú</strong><span><?= e($order['note'] ?: 'Không có') ?></span></div>
                    <div class="spec-row"><strong>Thanh toán</strong><span><?= e(paymentMethodText($order['payment_method'] ?? null)) ?></span></div>
                    <div class="spec-row">
                        <strong>Trạng thái tiền</strong>
                        <span class="status-pill payment-<?= e($order['payment_status'] ?? 'pending') ?>">
                            <?= e(paymentStatusText($order['payment_status'] ?? null)) ?>
                        </span>
                    </div>

                    <?php if (!empty($order['proof_image'])): ?>
                        <div class="spec-row">
                            <strong>Minh chứng</strong>
                            <a
                                class="proof-link"
                                href="<?= e(BASE_ASSETS_UPLOADS . $order['proof_image']) ?>"
                                target="_blank"
                                rel="noopener"
                            >
                                Xem ảnh đã gửi
                            </a>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="checkout-panel order-products">
                    <h2>Sản phẩm</h2>

                    <?php foreach ($items as $item): ?>
                        <?php
                        $productId = (int) ($item['product_id'] ?? 0);
                        $alreadyRenderedProduct = isset(
                            $reviewedProductIds[$productId]
                        );
                        $reviewedProductIds[$productId] = true;
                        ?>

                        <div class="ordered-product-block">
                            <div class="checkout-item">
                                <div>
                                    <strong><?= e($item['product_name']) ?></strong>
                                    <small>
                                        Size: <?= e($item['size'] ?: '-') ?> ·
                                        Màu: <?= e($item['color'] ?: '-') ?> ·
                                        SL: <?= e($item['quantity']) ?>
                                    </small>
                                </div>
                                <strong><?= e(formatPrice($item['price'] * $item['quantity'])) ?></strong>
                            </div>

                            <?php if ($order['status'] === 'completed' && !$alreadyRenderedProduct): ?>
                                <?php if (!empty($item['review_id'])): ?>
                                    <div class="existing-review">
                                        <strong>Đánh giá của bạn:</strong>
                                        <span class="review-stars">
                                            <?= str_repeat('★', (int) $item['review_rating']) ?>
                                        </span>
                                        <p><?= e($item['review_comment'] ?: 'Không có nội dung.') ?></p>
                                    </div>
                                <?php else: ?>
                                    <form
                                        class="review-form"
                                        action="<?= e(url('reviews/create')) ?>"
                                        method="post"
                                    >
                                        <?= csrfField() ?>
                                        <input type="hidden" name="order_id" value="<?= e($order['order_id']) ?>">
                                        <input type="hidden" name="order_item_id" value="<?= e($item['order_item_id']) ?>">

                                        <h3>Đánh giá sản phẩm này</h3>

                                        <label>
                                            Số sao
                                            <select class="field" name="rating" required>
                                                <option value="5">5 sao - Rất hài lòng</option>
                                                <option value="4">4 sao - Hài lòng</option>
                                                <option value="3">3 sao - Bình thường</option>
                                                <option value="2">2 sao - Chưa hài lòng</option>
                                                <option value="1">1 sao - Không hài lòng</option>
                                            </select>
                                        </label>

                                        <label>
                                            Nhận xét
                                            <textarea
                                                class="field"
                                                name="comment"
                                                rows="3"
                                                maxlength="1000"
                                                placeholder="Chia sẻ trải nghiệm của bạn..."
                                            ></textarea>
                                        </label>

                                        <button class="btn btn-primary" type="submit">
                                            Gửi đánh giá
                                        </button>
                                    </form>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <aside class="cart-summary">
                <h2>Tổng thanh toán</h2>
                <div class="summary-row"><span>Tạm tính</span><strong><?= e(formatPrice($order['subtotal'])) ?></strong></div>
                <div class="summary-row"><span>Phí giao hàng</span><strong><?= e(formatPrice($order['shipping_fee'])) ?></strong></div>

                <?php if (!empty($order['coupon_code'])): ?>
                    <div class="summary-row">
                        <span>Mã <?= e($order['coupon_code']) ?></span>
                        <strong>-<?= e(formatPrice($order['discount'])) ?></strong>
                    </div>
                <?php else: ?>
                    <div class="summary-row"><span>Giảm giá</span><strong>-<?= e(formatPrice($order['discount'])) ?></strong></div>
                <?php endif; ?>

                <div class="summary-row grand-total"><span>Tổng cộng</span><strong><?= e(formatPrice($order['total_price'])) ?></strong></div>
                <a class="btn btn-light btn-full" href="<?= e(url('orders')) ?>">Quay lại lịch sử</a>
            </aside>
        </div>
    </div>
</section>
