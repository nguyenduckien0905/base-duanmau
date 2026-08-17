<?php

// Khởi tạo session cho đăng nhập, CSRF và giỏ hàng.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ==================================================
// NẠP CONFIG VÀ HELPER
// ==================================================

require_once __DIR__ . '/configs/env.php';
require_once PATH_ROOT . 'configs/helper.php';

// ==================================================
// NẠP MODEL DÙNG CHUNG
// ==================================================

require_once PATH_MODEL . 'BaseModel.php';

// ==================================================
// NẠP MODEL ADMIN
// ==================================================

require_once PATH_MODEL . 'admin/AdminAuthModel.php';
require_once PATH_MODEL . 'admin/DashboardModel.php';
require_once PATH_MODEL . 'admin/CategoryModel.php';
require_once PATH_MODEL . 'admin/BrandModel.php';
require_once PATH_MODEL . 'admin/ProductModel.php';
require_once PATH_MODEL . 'admin/OrderModel.php';
require_once PATH_MODEL . 'admin/UserModel.php';
require_once PATH_MODEL . 'admin/ReviewModel.php';
require_once PATH_MODEL . 'admin/CouponModel.php';
require_once PATH_MODEL . 'admin/BannerModel.php';

// ==================================================
// NẠP MODEL CLIENT
// ==================================================

require_once PATH_MODEL . 'client/ProductModel.php';
require_once PATH_MODEL . 'client/AuthModel.php';
require_once PATH_MODEL . 'client/OrderModel.php';
require_once PATH_MODEL . 'client/ReviewModel.php';

// ==================================================
// NẠP CONTROLLER NỀN CỦA ADMIN
// ==================================================

require_once PATH_CONTROLLER . 'admin/BaseController.php';
require_once PATH_CONTROLLER . 'admin/AdminBaseController.php';

// ==================================================
// NẠP CONTROLLER ADMIN
// ==================================================

require_once PATH_CONTROLLER . 'admin/AdminAuthController.php';
require_once PATH_CONTROLLER . 'admin/AdminDashboardController.php';
require_once PATH_CONTROLLER . 'admin/AdminCategoryController.php';
require_once PATH_CONTROLLER . 'admin/AdminBrandController.php';
require_once PATH_CONTROLLER . 'admin/AdminProductController.php';
require_once PATH_CONTROLLER . 'admin/AdminOrderController.php';
require_once PATH_CONTROLLER . 'admin/AdminUserController.php';
require_once PATH_CONTROLLER . 'admin/AdminReviewController.php';
require_once PATH_CONTROLLER . 'admin/AdminCouponController.php';
require_once PATH_CONTROLLER . 'admin/AdminBannerController.php';

// ==================================================
// NẠP CONTROLLER CLIENT
// ==================================================

require_once PATH_CONTROLLER . 'client/HomeController.php';
require_once PATH_CONTROLLER . 'client/ProductController.php';
require_once PATH_CONTROLLER . 'client/AuthController.php';
require_once PATH_CONTROLLER . 'client/CartController.php';
require_once PATH_CONTROLLER . 'client/OrderController.php';
require_once PATH_CONTROLLER . 'client/ReviewController.php';

// ==================================================
// NẠP ROUTER CUỐI CÙNG
// ==================================================

require_once PATH_ROOT . 'routes/index.php';
