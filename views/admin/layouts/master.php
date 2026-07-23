<?php

/*
|--------------------------------------------------------------------------
| MASTER LAYOUT CỦA TRANG ADMIN
|--------------------------------------------------------------------------
| Tất cả các trang trong admin sẽ sử dụng chung file này.
|
| Controller cần truyền vào:
| - $pageTitle: Tiêu đề trang.
| - $contentView: Đường dẫn tới file nội dung cần hiển thị.
|
| Ví dụ:
| $pageTitle = 'Trang tổng quan';
| $contentView = __DIR__ . '/../views/admin/dashboard.php';
| require __DIR__ . '/../views/admin/layouts/master.php';
|--------------------------------------------------------------------------
*/

// Khởi động session nếu session chưa được khởi động.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/*
|--------------------------------------------------------------------------
| KIỂM TRA ĐĂNG NHẬP ADMIN
|--------------------------------------------------------------------------
| Tạm thời comment đoạn kiểm tra này khi chưa làm chức năng đăng nhập.
| Sau khi làm xong phần đăng nhập, bạn bỏ dấu comment để sử dụng.
|--------------------------------------------------------------------------
*/

/*
if (
    !isset($_SESSION['user']) ||
    (int) $_SESSION['user']['role_id'] !== 1
) {
    header('Location: index.php?act=admin-login');
    exit;
}
*/

// Tiêu đề mặc định của trang.
$pageTitle = $pageTitle ?? 'Quản trị hệ thống';

// Nếu controller chưa truyền contentView thì hiển thị Dashboard.
$contentView = $contentView ?? __DIR__ . '/../dashboard.php';

// Kiểm tra file nội dung có tồn tại hay không.
if (!file_exists($contentView)) {
    die('Không tìm thấy giao diện: ' . htmlspecialchars($contentView));
}

// Nhúng phần đầu trang.
require __DIR__ . '/header.php';

// Nhúng thanh menu bên trái.
require __DIR__ . '/sidebar.php';
?>

<!-- Nội dung chính của trang admin -->
<main class="admin-main">
    <div class="container-fluid py-4">

        <?php
        /*
        |--------------------------------------------------------------------------
        | HIỂN THỊ THÔNG BÁO
        |--------------------------------------------------------------------------
        | Controller có thể lưu thông báo vào session:
        |
        | $_SESSION['success'] = 'Thêm sản phẩm thành công';
        | $_SESSION['error'] = 'Có lỗi xảy ra';
        |--------------------------------------------------------------------------
        */
        ?>

        <?php if (!empty($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?= htmlspecialchars($_SESSION['success']) ?>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="alert">
                </button>
            </div>

            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <?php if (!empty($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <?= htmlspecialchars($_SESSION['error']) ?>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="alert">
                </button>
            </div>

            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <!-- Nhúng nội dung riêng của từng trang -->
        <?php require $contentView; ?>

    </div>
</main>

<?php
// Nhúng phần chân trang.
require __DIR__ . '/footer.php';
?>