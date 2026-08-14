<?php 

session_start();

spl_autoload_register(function ($class) {    
    $fileName = "$class.php";

    $fileModel              = PATH_MODEL . $fileName;
    $fileController         = PATH_CONTROLLER . $fileName;

    if (is_readable($fileModel)) {
        require_once $fileModel;
    } 
    else if (is_readable($fileController)) {
        require_once $fileController;
    }
});

require_once './configs/env.php';
require_once './configs/helper.php';
// ==================================================
// NẠP MODEL DÙNG CHUNG
// ==================================================

// BaseModel nằm trực tiếp trong thư mục models.
require_once PATH_MODEL . 'BaseModel.php';

// ==================================================
// NẠP MODEL CỦA CLIENT
// ==================================================
require_once PATH_MODEL_CLIENT . 'AuthModel.php';
require_once PATH_MODEL_CLIENT . 'OrderModel.php';
require_once PATH_MODEL_CLIENT . 'ReviewModel.php';
// Model Client đọc cùng bảng products mà phần Admin đang quản lý.
require_once PATH_MODEL_CLIENT . 'ProductModel.php';

// Model tài khoản Client đọc và tạo khách hàng trong bảng users.
require_once PATH_MODEL_CLIENT . 'AuthModel.php';

// Model đơn Client ghi vào các bảng mà phần Admin đơn hàng đang đọc.
require_once PATH_MODEL_CLIENT . 'OrderModel.php';

// ==================================================
// NẠP CONTROLLER CỦA CLIENT
// ==================================================

// Hai controller Client được đặt riêng trong thư mục controllers/client.
require_once PATH_CONTROLLER_CLIENT . 'HomeController.php';
require_once PATH_CONTROLLER_CLIENT . 'ProductController.php';
require_once PATH_CONTROLLER_CLIENT . 'AuthController.php';
require_once PATH_CONTROLLER_CLIENT . 'CartController.php';
require_once PATH_CONTROLLER_CLIENT . 'OrderController.php';
require_once PATH_CONTROLLER_CLIENT . 'ReviewController.php';
// Điều hướng
require_once './routes/index.php';
