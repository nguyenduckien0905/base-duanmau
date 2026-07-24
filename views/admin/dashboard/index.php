<!-- Các thẻ thống kê tổng quan. -->
<section class="stats-grid">
    <article class="stat-card">
        <span class="stat-icon purple">◫</span>
        <div>
            <small>Sản phẩm</small>
            <strong><?= e($statistics['products']) ?></strong>
        </div>
    </article>

    <article class="stat-card">
        <span class="stat-icon blue">♙</span>
        <div>
            <small>Thành viên</small>
            <strong><?= e($statistics['members']) ?></strong>
        </div>
    </article>

    <article class="stat-card">
        <span class="stat-icon orange">▣</span>
        <div>
            <small>Đơn hàng</small>
            <strong><?= e($statistics['orders']) ?></strong>
        </div>
    </article>

    <article class="stat-card">
        <span class="stat-icon green">₫</span>
        <div>
            <small>Doanh thu hoàn thành</small>
            <strong><?= e(formatPrice($statistics['revenue'])) ?></strong>
        </div>
    </article>
</section>

<!-- Hai bảng thông tin mới nhất. -->
<section class="dashboard-grid">
    <div class="panel">
        <div class="panel-heading">
            <h2>Đơn hàng mới</h2>
            <a href="<?= e(url('admin/orders')) ?>">Xem tất cả</a>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Mã đơn</th>
                        <th>Khách hàng</th>
                        <th>Tổng tiền</th>
                        <th>Trạng thái</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($latestOrders as $order): ?>
                        <tr>
                            <td>
                                <a href="<?= e(url('admin/orders/show', ['id' => $order['order_id']])) ?>">
                                    #<?= e($order['order_id']) ?>
                                </a>
                            </td>
                            <td><?= e($order['fullname']) ?></td>
                            <td><?= e(formatPrice($order['total_price'])) ?></td>
                            <td>
                                <span class="badge <?= e(orderStatusClass($order['status'])) ?>">
                                    <?= e(orderStatusText($order['status'])) ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                    <?php if (empty($latestOrders)): ?>
                        <tr>
                            <td colspan="4" class="empty-state">Chưa có đơn hàng.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="panel">
        <div class="panel-heading">
            <h2>Sắp hết hàng</h2>
            <a href="<?= e(url('admin/products')) ?>">Quản lý kho</a>
        </div>

        <div class="stock-list">
            <?php foreach ($lowStockProducts as $product): ?>
                <a href="<?= e(url('admin/products/edit', ['id' => $product['product_id']])) ?>">
                    <span><?= e($product['product_name']) ?></span>
                    <strong><?= e($product['stock']) ?> sản phẩm</strong>
                </a>
            <?php endforeach; ?>

            <?php if (empty($lowStockProducts)): ?>
                <p class="empty-state">Không có sản phẩm sắp hết hàng.</p>
            <?php endif; ?>
        </div>
    </div>
</section>
