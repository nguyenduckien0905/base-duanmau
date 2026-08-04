<!DOCTYPE html>
<!-- Khai báo tài liệu HTML5. -->
<html lang="vi">
<head>
    <!-- Cho phép hiển thị đúng tiếng Việt. -->
    <meta charset="UTF-8">
    <!-- Giúp giao diện co giãn đúng trên điện thoại và máy tính bảng. -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- $pageTitle được controller truyền sang; e() giúp chống chèn mã HTML. -->
    <title><?= e($pageTitle ?? 'K Fashion') ?> | K Fashion</title>
    <!-- Nạp file CSS riêng của giao diện Client. -->
   <link
    rel="stylesheet"
    href="<?= e(
        BASE_URL
        . 'assets/css/client.css?v='
        . filemtime(PATH_ROOT . 'assets/css/client.css')
    ) ?>"
>
</head>
<body>
    <!-- Nạp phần đầu trang dùng chung: logo, menu và ô tìm kiếm. -->
    <?php require PATH_VIEW . 'client/layouts/header.php'; ?>

    <!-- Lấy thông báo một lần từ session. -->
    <?php $flash = getFlash(); ?>

    <!-- Chỉ tạo khối thông báo khi controller đã setFlash(). -->
    <?php if ($flash): ?>
        <div class="container">
            <div class="alert alert-<?= e($flash['type']) ?>">
                <?= e($flash['message']) ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Khu vực nội dung thay đổi theo từng trang. -->
    <main>
        <!-- $contentView do controller quyết định: home, list, detail hoặc 404. -->
        <?php require $contentView; ?>
    </main>

    <!-- Nạp chân trang dùng chung. -->
    <?php require PATH_VIEW . 'client/layouts/footer.php'; ?>
</body>
</html>
