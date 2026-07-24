<?php

// URL gốc của dự án trên XAMPP; sửa BaseExam thành tên thư mục dự án của bạn.
define('BASE_URL', 'http://localhost/Du_An_1/base-duanmau/');

// Đường dẫn tuyệt đối tới thư mục gốc của dự án.
define('PATH_ROOT', __DIR__ . '/../');

// Đường dẫn tới thư mục chứa toàn bộ view.
define('PATH_VIEW', PATH_ROOT . 'views/');

// Đường dẫn tới layout chính của phần Admin.
define('PATH_VIEW_ADMIN_MAIN', PATH_VIEW . 'admin/main.php');

// Đường dẫn tới thư mục controller.
define('PATH_CONTROLLER', PATH_ROOT . 'controllers/');

// Đường dẫn tới thư mục model.
define('PATH_MODEL', PATH_ROOT . 'models/');

// URL dùng để hiển thị các ảnh đã upload.
define('BASE_ASSETS_UPLOADS', BASE_URL . 'assets/uploads/');

// Đường dẫn thật dùng để lưu các ảnh upload trên máy chủ.
define('PATH_ASSETS_UPLOADS', PATH_ROOT . 'assets/uploads/');

// Đường dẫn đến controller Admin.
define('PATH_CONTROLLER_ADMIN', PATH_CONTROLLER . 'admin/');

// Đường dẫn đến model Admin.
define('PATH_MODEL_ADMIN', PATH_MODEL . 'admin/');

// Thông tin kết nối MySQL trên XAMPP.
define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'shop_ban_quan_ao');

// Các tùy chọn giúp PDO báo lỗi rõ ràng và trả dữ liệu dạng mảng.
define('DB_OPTIONS', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
]);

// Đặt múi giờ Việt Nam để ngày giờ hiển thị đúng.
date_default_timezone_set('Asia/Ho_Chi_Minh');

// Chỉ tạo session khi session chưa được khởi động.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
