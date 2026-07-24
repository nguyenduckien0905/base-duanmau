<!-- Bộ lọc đơn hàng. -->
<div class="toolbar">
    <form class="filter-form" action="<?= e(url('admin/orders')) ?>" method="get">
        <input type="hidden" name="action" value="admin/orders">

        <input
            type="search"
            name="keyword"
            value="<?= e($keyword) ?>"
            placeholder="Mã đơn, tên, số điện thoại..."
        >

        <select name="status">
            <option value="">Tất cả trạng thái</option>
            <?php foreach ($statusLabels as $statusCode => $statusLabel): ?>
                <option
                    value="<?= e($statusCode) ?>"
                    <?= $status === $statusCode ? 'selected' : '' ?>
                >
                    <?= e($statusLabel) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <button class="btn btn-light" type="submit">Lọc đơn</button>
    </form>
</div>

<!-- Bảng đơn hàng. -->
<div class="panel">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Mã đơn</th>
                    <th>Khách hàng</th>
                    <th>Người nhận</th>
                    <th>Ngày đặt</th>
                    <th>Tổng tiền</th>
                    <th>Thanh toán</th>
                    <th>Trạng thái</th>
                    <th class="text-right">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                    <tr>
                        <td><strong>#<?= e($order['order_id']) ?></strong></td>
                        <td>
                            <strong><?= e($order['fullname']) ?></strong>
                            <small class="table-subtext"><?= e($order['email']) ?></small>
                        </td>
                        <td>
                            <?= e($order['receiver_name']) ?>
                            <small class="table-subtext"><?= e($order['receiver_phone']) ?></small>
                        </td>
                        <td><?= e(date('d/m/Y H:i', strtotime($order['created_at']))) ?></td>
                        <td><strong><?= e(formatPrice($order['total_price'])) ?></strong></td>
                        <td>
                            <span class="badge <?= $order['payment_status'] === 'paid' ? 'badge-success' : 'badge-muted' ?>">
                                <?= $order['payment_status'] === 'paid' ? 'Đã trả' : 'Chưa trả' ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge <?= e(orderStatusClass($order['status'])) ?>">
                                <?= e(orderStatusText($order['status'])) ?>
                            </span>
                        </td>
                        <td class="actions">
                            <a
                                class="btn btn-light btn-small"
                                href="<?= e(url('admin/orders/show', ['id' => $order['order_id']])) ?>"
                            >
                                Chi tiết
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>

                <?php if (empty($orders)): ?>
                    <tr>
                        <td colspan="8" class="empty-state">Không tìm thấy đơn hàng.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
