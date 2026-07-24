<?php

// Lấy action trên URL; nếu không có thì coi là trang chủ Client.
$action = $_GET['action'] ?? '/';

// Điều hướng action đến đúng phương thức controller bằng biểu thức match.
match ($action) {
    // Nhóm route xác thực Admin.
    'admin/login' => (new AdminAuthController())->login(),
    'admin/logout' => (new AdminAuthController())->logout(),

    // Nhóm route dashboard.
    'admin',
    'admin/dashboard' => (new AdminDashboardController())->index(),

    // Nhóm route quản lý danh mục.
    'admin/categories' => (new AdminCategoryController())->index(),
    'admin/categories/create' => (new AdminCategoryController())->create(),
    'admin/categories/edit' => (new AdminCategoryController())->edit(),
    'admin/categories/delete' => (new AdminCategoryController())->delete(),

    // Nhóm route quản lý thương hiệu.
    'admin/brands' => (new AdminBrandController())->index(),
    'admin/brands/create' => (new AdminBrandController())->create(),
    'admin/brands/edit' => (new AdminBrandController())->edit(),
    'admin/brands/delete' => (new AdminBrandController())->delete(),

    // Nhóm route quản lý sản phẩm.
    'admin/products' => (new AdminProductController())->index(),
    'admin/products/create' => (new AdminProductController())->create(),
    'admin/products/edit' => (new AdminProductController())->edit(),
    'admin/products/delete' => (new AdminProductController())->delete(),

    // Nhóm route quản lý đơn hàng.
    'admin/orders' => (new AdminOrderController())->index(),
    'admin/orders/show' => (new AdminOrderController())->show(),
    'admin/orders/update-status' => (new AdminOrderController())->updateStatus(),

    // Nhóm route quản lý tài khoản.
    'admin/users' => (new AdminUserController())->index(),
    'admin/users/toggle-status' => (new AdminUserController())->toggleStatus(),

    // Nhóm route quản lý đánh giá.
    'admin/reviews' => (new AdminReviewController())->index(),
    'admin/reviews/toggle-status' => (new AdminReviewController())->toggleStatus(),
    'admin/reviews/delete' => (new AdminReviewController())->delete(),

    // Nhóm route quản lý mã giảm giá.
    'admin/coupons' => (new AdminCouponController())->index(),
    'admin/coupons/create' => (new AdminCouponController())->create(),
    'admin/coupons/edit' => (new AdminCouponController())->edit(),
    'admin/coupons/delete' => (new AdminCouponController())->delete(),

    // Nhóm route quản lý banner.
    'admin/banners' => (new AdminBannerController())->index(),
    'admin/banners/create' => (new AdminBannerController())->create(),
    'admin/banners/edit' => (new AdminBannerController())->edit(),
    'admin/banners/delete' => (new AdminBannerController())->delete(),

    // Khi ghép vào dự án, thay dòng dưới bằng route Client hiện có của nhóm bạn:
    // '/' => (new HomeController())->index(),
    '/' => redirect('admin/login'),

    // Action không tồn tại sẽ trả lỗi 404.
    default => abort404(),
};
