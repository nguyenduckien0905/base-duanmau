<?php

// Lấy action trên URL.
// Nếu URL không có action thì mở trang chủ Client.
$action = $_GET['action'] ?? '/';

match ($action) {
    // ==================================================
    // ROUTE ADMIN
    // ==================================================

    // Xác thực Admin.
    'admin/login' => (new AdminAuthController())->login(),
    'admin/logout' => (new AdminAuthController())->logout(),

    // Dashboard.
    'admin',
    'admin/dashboard' => (new AdminDashboardController())->index(),

    // Danh mục.
    'admin/categories' =>
        (new AdminCategoryController())->index(),

    'admin/categories/create' =>
        (new AdminCategoryController())->create(),

    'admin/categories/edit' =>
        (new AdminCategoryController())->edit(),

    'admin/categories/delete' =>
        (new AdminCategoryController())->delete(),

    // Thương hiệu.
    'admin/brands' =>
        (new AdminBrandController())->index(),

    'admin/brands/create' =>
        (new AdminBrandController())->create(),

    'admin/brands/edit' =>
        (new AdminBrandController())->edit(),

    'admin/brands/delete' =>
        (new AdminBrandController())->delete(),

    // Sản phẩm.
    'admin/products' =>
        (new AdminProductController())->index(),

    'admin/products/create' =>
        (new AdminProductController())->create(),

    'admin/products/edit' =>
        (new AdminProductController())->edit(),

    'admin/products/delete' =>
        (new AdminProductController())->delete(),

    // Đơn hàng.
    'admin/orders' =>
        (new AdminOrderController())->index(),

    'admin/orders/show' =>
        (new AdminOrderController())->show(),

    'admin/orders/update-status' =>
        (new AdminOrderController())->updateStatus(),

    'admin/orders/update-payment' =>
        (new AdminOrderController())->updatePayment(),

    // Tài khoản.
    'admin/users' =>
        (new AdminUserController())->index(),

    'admin/users/toggle-status' =>
        (new AdminUserController())->toggleStatus(),

    // Đánh giá.
    'admin/reviews' =>
        (new AdminReviewController())->index(),

    'admin/reviews/toggle-status' =>
        (new AdminReviewController())->toggleStatus(),

    'admin/reviews/delete' =>
        (new AdminReviewController())->delete(),

    // Mã giảm giá.
    'admin/coupons' =>
        (new AdminCouponController())->index(),

    'admin/coupons/create' =>
        (new AdminCouponController())->create(),

    'admin/coupons/edit' =>
        (new AdminCouponController())->edit(),

    'admin/coupons/delete' =>
        (new AdminCouponController())->delete(),

    // Banner.
    'admin/banners' =>
        (new AdminBannerController())->index(),

    'admin/banners/create' =>
        (new AdminBannerController())->create(),

    'admin/banners/edit' =>
        (new AdminBannerController())->edit(),

    'admin/banners/delete' =>
        (new AdminBannerController())->delete(),

    // ==================================================
    // ROUTE CLIENT
    // ==================================================

    // Trang chủ.
    '/' => (new ClientHomeController())->index(),

    // Sản phẩm.
    'products' =>
        (new ClientProductController())->index(),

    'products/detail' =>
        (new ClientProductController())->detail(),

    'products/search' =>
        (new ClientProductController())->search(),

    // Tài khoản khách hàng.
    'register' =>
        (new ClientAuthController())->register(),

    'login' =>
        (new ClientAuthController())->login(),

    'profile' =>
        (new ClientAuthController())->profile(),

    'logout' =>
        (new ClientAuthController())->logout(),

    // Giỏ hàng.
    'cart' =>
        (new ClientCartController())->index(),

    'cart/add' =>
        (new ClientCartController())->add(),

    'cart/update' =>
        (new ClientCartController())->update(),

    'cart/remove' =>
        (new ClientCartController())->remove(),

    // Thanh toán và đơn hàng.
    'checkout' =>
        (new ClientOrderController())->checkout(),

    'orders' =>
        (new ClientOrderController())->history(),

    'orders/show' =>
        (new ClientOrderController())->show(),

    'orders/confirm-received' =>
        (new ClientOrderController())->confirmReceived(),

    'reviews/create' =>
        (new ClientReviewController())->create(),

    // Action không tồn tại.
    default => abort404(),
};
