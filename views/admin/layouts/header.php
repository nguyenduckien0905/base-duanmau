<!DOCTYPE html>
<html lang="vi">
<head>
    <!-- Khai báo bảng mã hỗ trợ tiếng Việt. -->
    <meta charset="UTF-8">

    <!-- Giúp giao diện co giãn đúng trên điện thoại. -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Tiêu đề trình duyệt thay đổi theo trang. -->
    <title><?= e($pageTitle ?? 'Admin') ?> | Clothing Shop</title>

    <!-- Nạp CSS riêng của trang quản trị. -->
    <link rel="stylesheet" href="<?= e(BASE_URL . 'assets/css/admin.css') ?>">
</head>
<body>
    <!-- Khung bao toàn bộ sidebar và nội dung. -->
    <div class="admin-shell">
