<!-- Trang cập nhật thông tin cá nhân của khách hàng đang đăng nhập. -->
<section class="section">
    <div class="container">
        <div class="auth-card">
            <h1>Tài khoản của tôi</h1>
            <p>Thông tin được dùng để điền nhanh khi thanh toán.</p>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <?php foreach ($errors as $error): ?>
                        <div><?= e($error) ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form class="client-form" action="<?= e(url('profile')) ?>" method="post">
                <?= csrfField() ?>

                <label>
                    Email
                    <input class="field" type="email" value="<?= e($email) ?>" disabled>
                </label>

                <label>
                    Họ và tên
                    <input class="field" type="text" name="fullname" value="<?= e($form['fullname']) ?>" required>
                </label>

                <label>
                    Số điện thoại
                    <input class="field" type="tel" name="phone" value="<?= e($form['phone']) ?>">
                </label>

                <!-- <label>
                    Địa chỉ
                    <textarea class="field" name="address" rows="4"><?= e($form['address']) ?></textarea>
                </label> -->

                <button class="btn btn-primary btn-full" type="submit">Lưu thay đổi</button>
                <a class="btn btn-light btn-full" href="<?= e(url('orders')) ?>">Xem đơn hàng của tôi</a>
            </form>
        </div>
    </div>
</section>
