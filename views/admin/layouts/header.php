<!DOCTYPE html>
<html lang="vi">

<head>
    <!-- Khai báo bảng mã hỗ trợ tiếng Việt. -->
    <meta charset="UTF-8">

    <!-- Giúp giao diện co giãn đúng trên điện thoại. -->
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <!-- Tiêu đề trình duyệt thay đổi theo trang. -->
    <title>
        <?= e($pageTitle ?? 'Admin') ?> | Clothing Shop
    </title>

    <!-- CSS chính của trang quản trị. -->
    <link
        rel="stylesheet"
        href="<?= e(
            BASE_URL
            . 'assets/css/admin.css?v='
            . filemtime(PATH_ROOT . 'assets/css/admin.css')
        ) ?>"
    >

    <!-- CSS dành cho form biến thể sản phẩm. -->
    <link
        rel="stylesheet"
        href="<?= e(
            BASE_URL
            . 'assets/css/admin-variants.css?v='
            . filemtime(PATH_ROOT . 'assets/css/admin-variants.css')
        ) ?>"
    >

    <!-- CSS phân trang phải được nạp cuối cùng. -->
    <link
        rel="stylesheet"
        href="<?= e(
            BASE_URL
            . 'assets/css/admin-pagination.css?v='
            . filemtime(PATH_ROOT . 'assets/css/admin-pagination.css')
        ) ?>"
    >

    <link
        rel="stylesheet"
        href="<?= e(
            BASE_URL
            . 'assets/css/checkout-workflow.css?v='
            . filemtime(PATH_ROOT . 'assets/css/checkout-workflow.css')
        ) ?>"
    >
</head>

<body>
    <!-- Khung bao toàn bộ sidebar và nội dung. -->
    <div class="admin-shell">
