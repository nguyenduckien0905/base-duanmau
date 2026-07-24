<?php

// Lấy thông báo từ session, ví dụ thông báo vừa đăng xuất.
$flash = getFlash();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <!-- Khai báo bảng mã và viewport. -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Tiêu đề trang đăng nhập. -->
    <title>Đăng nhập Admin | Clothing Shop</title>

    <!-- Dùng chung CSS của Admin. -->
    <link rel="stylesheet" href="<?= e(BASE_URL . 'assets/css/admin.css') ?>">
</head>
<body class="login-page">
    <!-- Khung đăng nhập ở giữa màn hình. -->
    <div class="login-card">
        <div class="login-brand">
            <span class="brand-mark">CS</span>
            <div>
                <h1>Clothing Shop</h1>
                <p>Đăng nhập khu vực quản trị</p>
            </div>
        </div>

        <!-- Hiển thị thông báo từ session. -->
        <?php if ($flash): ?>
            <div class="alert alert-<?= e($flash['type']) ?>">
                <?= e($flash['message']) ?>
            </div>
        <?php endif; ?>

        <!-- Hiển thị các lỗi kiểm tra dữ liệu. -->
        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <?php foreach ($errors as $error): ?>
                    <div><?= e($error) ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Form gửi về chính action đăng nhập. -->
        <form action="<?= e(url('admin/login')) ?>" method="post">
            <?= csrfField() ?>

            <div class="form-group">
                <label for="email">Email</label>
                <input
                    id="email"
                    type="email"
                    name="email"
                    value="<?= e($_POST['email'] ?? '') ?>"
                    placeholder="admin@shop.local"
                    required
                    autofocus
                >
            </div>

            <div class="form-group">
                <label for="password">Mật khẩu</label>
                <input
                    id="password"
                    type="password"
                    name="password"
                    placeholder="Nhập mật khẩu"
                    required
                >
            </div>

            <button class="btn btn-primary btn-block" type="submit">
                Đăng nhập
            </button>
        </form>

        <!-- Tài khoản có sẵn trong file demo SQL. -->
        <div class="login-demo">
            <strong>Tài khoản demo</strong>
            <span>admin@shop.local / Admin@123</span>
        </div>
    </div>
</body>
</html>
