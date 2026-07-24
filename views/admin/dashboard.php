<?php

/*
|--------------------------------------------------------------------------
| TRANG DASHBOARD
|--------------------------------------------------------------------------
| Controller sẽ truyền vào hai biến:
|
| 1. $statistics: Các số liệu thống kê.
| 2. $recentOrders: Danh sách đơn hàng mới nhất.
|
| Nếu controller chưa truyền dữ liệu, các giá trị mặc định sẽ được sử dụng.
|--------------------------------------------------------------------------
*/

$statistics = $statistics ?? [
    'products' => 0,
    'orders'   => 0,
    'users'    => 0,
    'revenue'  => 0
];

$recentOrders = $recentOrders ?? [];
?>

<!-- Tiêu đề trang -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-1">Trang tổng quan</h2>

        <p class="text-muted mb-0">
            Theo dõi hoạt động của shop bán quần áo
        </p>
    </div>

    <span class="badge bg-primary fs-6">
        <?= date('d/m/Y') ?>
    </span>
</div>

<!-- Các thẻ thống kê -->
<div class="row g-4 mb-4">

    <!-- Tổng sản phẩm -->
    <div class="col-xl-3 col-md-6">
        <div class="card dashboard-card bg-primary shadow-sm">
            <div class="card-body d-flex justify-content-between">
                <div>
                    <p class="mb-2">Tổng sản phẩm</p>

                    <h2 class="mb-0">
                        <?= number_format($statistics['products']) ?>
                    </h2>
                </div>

                <i class="bi bi-box-seam statistic-icon"></i>
            </div>
        </div>
    </div>

    <!-- Tổng đơn hàng -->
    <div class="col-xl-3 col-md-6">
        <div class="card dashboard-card bg-success shadow-sm">
            <div class="card-body d-flex justify-content-between">
                <div>
                    <p class="mb-2">Tổng đơn hàng</p>

                    <h2 class="mb-0">
                        <?= number_format($statistics['orders']) ?>
                    </h2>
                </div>

                <i class="bi bi-cart-check statistic-icon"></i>
            </div>
        </div>
    </div>

    <!-- Tổng người dùng -->
    <div class="col-xl-3 col-md-6">
        <div class="card dashboard-card bg-warning shadow-sm">
            <div class="card-body d-flex justify-content-between">
                <div>
                    <p class="mb-2">Người dùng</p>

                    <h2 class="mb-0">
                        <?= number_format($statistics['users']) ?>
                    </h2>
                </div>

                <i class="bi bi-people statistic-icon"></i>
            </div>
        </div>
    </div>

    <!-- Tổng doanh thu -->
    <div class="col-xl-3 col-md-6">
        <div class="card dashboard-card bg-danger shadow-sm">
            <div class="card-body d-flex justify-content-between">
                <div>
                    <p class="mb-2">Doanh thu</p>

                    <h2 class="mb-0">
                        <?= number_format($statistics['revenue']) ?>đ
                    </h2>
                </div>

                <i class="bi bi-cash-stack statistic-icon"></i>
            </div>
        </div>
    </div>
</div>

<!-- Danh sách đơn hàng mới -->
<div class="card table-card shadow-sm">
    <div class="card-header bg-white py-3">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                Đơn hàng mới nhất
            </h5>

            <a
                href="index.php?act=admin-orders"
                class="btn btn-sm btn-primary">

                Xem tất cả
            </a>
        </div>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                <tr>
                    <th>Mã đơn</th>
                    <th>Khách hàng</th>
                    <th>Tổng tiền</th>
                    <th>Trạng thái</th>
                    <th>Ngày đặt</th>
                    <th>Thao tác</th>
                </tr>
                </thead>

                <tbody>
                <?php if (empty($recentOrders)): ?>
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">
                            Chưa có đơn hàng nào.
                        </td>
                    </tr>
                <?php else: ?>

                    <?php foreach ($recentOrders as $order): ?>
                        <tr>
                            <td>
                                #<?= (int) $order['order_id'] ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($order['fullname']) ?>
                            </td>

                            <td class="fw-semibold">
                                <?= number_format($order['total_price']) ?>đ
                            </td>

                            <td>
                                <?php
                                // Màu sắc tương ứng với từng trạng thái.
                                $statusClasses = [
                                    'pending'   => 'bg-warning text-dark',
                                    'confirmed' => 'bg-info text-dark',
                                    'preparing' => 'bg-primary',
                                    'shipping'  => 'bg-secondary',
                                    'completed' => 'bg-success',
                                    'cancelled' => 'bg-danger'
                                ];

                                // Tên tiếng Việt của trạng thái.
                                $statusNames = [
                                    'pending'   => 'Chờ xác nhận',
                                    'confirmed' => 'Đã xác nhận',
                                    'preparing' => 'Đang chuẩn bị',
                                    'shipping'  => 'Đang giao',
                                    'completed' => 'Hoàn thành',
                                    'cancelled' => 'Đã hủy'
                                ];

                                $status = $order['status'];
                                ?>

                                <span class="badge <?= $statusClasses[$status] ?? 'bg-dark' ?>">
                                    <?= $statusNames[$status] ?? $status ?>
                                </span>
                            </td>

                            <td>
                                <?= date(
                                    'd/m/Y H:i',
                                    strtotime($order['created_at'])
                                ) ?>
                            </td>

                            <td>
                                <a
                                    href="index.php?act=admin-order-detail&id=<?= (int) $order['order_id'] ?>"
                                    class="btn btn-sm btn-outline-primary">

                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>