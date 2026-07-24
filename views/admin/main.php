<?php

// Lấy thông báo một lần để layout hiển thị.
$flash = getFlash();

// Lấy thông tin người đang đăng nhập.
$admin = currentAdmin();

// Lấy action hiện tại để đánh dấu menu đang chọn.
$currentAction = (string) ($_GET['action'] ?? 'admin/dashboard');

// Nạp phần đầu trang.
require PATH_VIEW . 'admin/layouts/header.php';

// Nạp thanh menu bên trái.
require PATH_VIEW . 'admin/layouts/sidebar.php';
?>

<!-- Khu vực nội dung thay đổi theo từng chức năng. -->
<main class="admin-content">
    <!-- Thanh tiêu đề của trang hiện tại. -->
    <div class="page-heading">
        <div>
            <p class="eyebrow">KHU VỰC QUẢN TRỊ</p>
            <h1><?= e($pageTitle ?? 'Admin') ?></h1>
        </div>

        <!-- Hiển thị người đang đăng nhập. -->
        <div class="admin-user">
            <span class="avatar-circle">
                <?= e(mb_strtoupper(mb_substr($admin['fullname'] ?? 'A', 0, 1))) ?>
            </span>
            <div>
                <strong><?= e($admin['fullname'] ?? 'Admin') ?></strong>
                <small><?= e($admin['role_name'] ?? '') ?></small>
            </div>
        </div>
    </div>

    <!-- Thông báo thành công hoặc lỗi được lưu trong session. -->
    <?php if ($flash): ?>
        <div class="alert alert-<?= e($flash['type']) ?>">
            <?= e($flash['message']) ?>
        </div>
    <?php endif; ?>

    <!-- Nạp view con do controller truyền sang. -->
    <?php require PATH_VIEW . $view . '.php'; ?>
</main>

<?php
// Nạp phần cuối trang.
require PATH_VIEW . 'admin/layouts/footer.php';
?>
