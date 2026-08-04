<!-- Danh sách chỉ chứa đơn của user_id đang đăng nhập. -->
<section class="section">
    <div class="container">
        <div class="section-heading">
            <div>
                <h1>Đơn hàng của tôi</h1>
                <p>Theo dõi tình trạng các đơn hàng đã đặt.</p>
            </div>
        </div>

        <?php if (!empty($orders)): ?>
            <div class="order-list">
                <?php foreach ($orders as $order): ?>
                    <article class="order-card">
                        <div>
                            <strong>Đơn #<?= e($order['order_id']) ?></strong>
                            <p><?= e(date('d/m/Y H:i', strtotime($order['created_at']))) ?></p>
                        </div>
                        <div>
                            <span class="status-pill status-<?= e($order['status']) ?>">
                                <?= e(orderStatusText($order['status'])) ?>
                            </span>
                        </div>
                        <div>
                            <span>Tổng tiền</span>
                            <strong class="product-price"><?= e(formatPrice($order['total_price'])) ?></strong>
                        </div>
                        <a class="btn btn-light" href="<?= e(url('orders/show', ['id' => $order['order_id']])) ?>">
                            Xem chi tiết
                        </a>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty">
                <h2>Bạn chưa có đơn hàng</h2>
                <p>Các đơn đã đặt sẽ xuất hiện tại đây.</p>
                <a class="btn btn-primary" href="<?= e(url('products')) ?>">Mua sắm ngay</a>
            </div>
        <?php endif; ?>
    </div>
</section>
