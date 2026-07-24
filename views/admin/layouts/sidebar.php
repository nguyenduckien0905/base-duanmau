<?php

// Hàm nhỏ kiểm tra nhóm action nào đang được mở.
$isActive = static function (string $prefix) use ($currentAction): string {
    return str_starts_with($currentAction, $prefix) ? 'active' : '';
};
?>

<!-- Thanh điều hướng cố định bên trái. -->
<aside class="admin-sidebar">
    <!-- Logo của khu vực quản trị. -->
    <a class="brand" href="<?= e(url('admin/dashboard')) ?>">
        <span class="brand-mark">CS</span>
        <span>
            <strong>Clothing Shop</strong>
            <small>ADMIN PANEL</small>
        </span>
    </a>

    <!-- Danh sách các phân hệ Admin. -->
    <nav class="admin-nav">
        <a class="<?= e($isActive('admin/dashboard')) ?>" href="<?= e(url('admin/dashboard')) ?>">
            <span>▦</span> Tổng quan
        </a>
        <a class="<?= e($isActive('admin/categories')) ?>" href="<?= e(url('admin/categories')) ?>">
            <span>▤</span> Danh mục
        </a>
        <a class="<?= e($isActive('admin/brands')) ?>" href="<?= e(url('admin/brands')) ?>">
            <span>◆</span> Thương hiệu
        </a>
        <a class="<?= e($isActive('admin/products')) ?>" href="<?= e(url('admin/products')) ?>">
            <span>◫</span> Sản phẩm
        </a>
        <a class="<?= e($isActive('admin/orders')) ?>" href="<?= e(url('admin/orders')) ?>">
            <span>▣</span> Đơn hàng
        </a>
        <a class="<?= e($isActive('admin/users')) ?>" href="<?= e(url('admin/users')) ?>">
            <span>♙</span> Tài khoản
        </a>
        <a class="<?= e($isActive('admin/reviews')) ?>" href="<?= e(url('admin/reviews')) ?>">
            <span>★</span> Đánh giá
        </a>
        <a class="<?= e($isActive('admin/coupons')) ?>" href="<?= e(url('admin/coupons')) ?>">
            <span>％</span> Mã giảm giá
        </a>
        <a class="<?= e($isActive('admin/banners')) ?>" href="<?= e(url('admin/banners')) ?>">
            <span>▧</span> Banner
        </a>
    </nav>

    <!-- Form đăng xuất dùng POST và CSRF. -->
    <form class="logout-form" action="<?= e(url('admin/logout')) ?>" method="post">
        <?= csrfField() ?>
        <button type="submit">↪ Đăng xuất</button>
    </form>
</aside>
