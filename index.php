<?php

// Nạp các hằng số cấu hình của dự án.
require_once __DIR__ . '/configs/env.php';

// Nạp các hàm dùng chung của dự án.
require_once PATH_ROOT . 'configs/helper.php';

// ==================================================
// NẠP MODEL DÙNG CHUNG
// ==================================================

// BaseModel nằm trực tiếp trong thư mục models.
require_once PATH_MODEL . 'BaseModel.php';

// ==================================================
// NẠP MODEL CỦA ADMIN
// ==================================================

// Các model Admin nằm trong thư mục models/admin.
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
// NẠP CONTROLLER CỦA CLIENT
// ==================================================

// HomeController nằm trực tiếp trong thư mục controllers.
require_once PATH_CONTROLLER . 'HomeController.php';

// ==================================================
// NẠP CONTROLLER NỀN CỦA ADMIN
// ==================================================

// Controller nền phải được nạp trước controller con.
require_once PATH_CONTROLLER . 'admin/BaseController.php';
require_once PATH_CONTROLLER . 'admin/AdminBaseController.php';

// ==================================================
// NẠP CONTROLLER CỦA ADMIN
// ==================================================

// Các controller Admin nằm trong thư mục controllers/admin.
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
// NẠP ROUTER
// ==================================================

// Router phải được nạp cuối cùng sau model và controller.
require_once PATH_ROOT . 'routes/index.php';