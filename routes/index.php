<?php

$action = $_GET['action'] ?? '/';

match ($action) {
    '/' => (new HomeController())->index(),

    // Danh sách sản phẩm, đồng thời xử lý lọc danh mục và sắp xếp.
    'products' => (new ProductController())->index(),

    // Chi tiết sản phẩm; id được truyền trên URL.
    'products/detail' => (new ProductController())->detail(),

    // Tìm kiếm sản phẩm; phương thức search() dùng lại xử lý của index().
    'products/search' => (new ProductController())->search(),

    // Nhóm route đăng ký, đăng nhập và đăng xuất khách hàng.
    'register' => (new AuthController())->register(),
    'login' => (new AuthController())->login(),
    'profile' => (new AuthController())->profile(),
    'logout' => (new AuthController())->logout(),

    // Nhóm route giỏ hàng lưu bằng session.
    'cart' => (new CartController())->index(),
    'cart/add' => (new CartController())->add(),
    'cart/update' => (new CartController())->update(),
    'cart/remove' => (new CartController())->remove(),

    // Nhóm route thanh toán và đơn hàng Client.
    'checkout' => (new ClientOrderController())->checkout(),
    'orders' => (new ClientOrderController())->history(),
    'orders/show' => (new ClientOrderController())->show(),
    'orders/confirm-received' =>
        (new OrderController())->confirmReceived(),
    'reviews/create' =>
        (new ReviewController())->create(),


    // Action không tồn tại sẽ trả lỗi 404.
    default => abort404(),
};