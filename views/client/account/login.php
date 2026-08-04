<section class="auth-page">
    <div class="auth-wrapper">
        <!-- Phần giới thiệu bên trái -->
        <div class="auth-banner">
            <a class="auth-logo" href="<?= e(url('/')) ?>">
                K<span>FASHION</span>
            </a>

            <div class="auth-banner-content">
                <span class="auth-badge">Chào mừng trở lại</span>

                <h1>Thời trang dành cho phong cách của bạn</h1>

                <p>
                    Đăng nhập để mua sắm, theo dõi đơn hàng
                    và nhận những ưu đãi mới nhất.
                </p>

                <ul class="auth-benefits">
                    <li>✓ Theo dõi trạng thái đơn hàng</li>
                    <li>✓ Thanh toán nhanh chóng</li>
                    <li>✓ Cập nhật sản phẩm mới</li>
                </ul>
            </div>
        </div>

        <!-- Form đăng nhập bên phải -->
        <div class="auth-form-side">
            <div class="auth-form-header">
                <span class="auth-form-label">TÀI KHOẢN</span>

                <h2>Đăng nhập</h2>

                <p>
                    Nhập email và mật khẩu để tiếp tục.
                </p>
            </div>

            <!-- Hiển thị lỗi đăng nhập -->
            <?php if (!empty($errors)): ?>
                <div class="auth-alert">
                    <?php foreach ($errors as $error): ?>
                        <div>⚠ <?= e($error) ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form
                class="auth-form"
                action="<?= e(
                    url(
                        'login',
                        ['next' => $_GET['next'] ?? '']
                    )
                ) ?>"
                method="post"
            >
                <?= csrfField() ?>

                <div class="auth-field">
                    <label for="email">Địa chỉ email</label>

                    <div class="auth-input-group">
                        <span class="auth-input-icon">✉</span>

                        <input
                            id="email"
                            type="email"
                            name="email"
                            value="<?= e($email) ?>"
                            placeholder="example@gmail.com"
                            autocomplete="email"
                            required
                        >
                    </div>
                </div>

                <div class="auth-field">
                    <label for="password">Mật khẩu</label>

                    <div class="auth-input-group">
                        <span class="auth-input-icon">●</span>

                        <input
                            id="password"
                            type="password"
                            name="password"
                            placeholder="Nhập mật khẩu"
                            minlength="6"
                            autocomplete="current-password"
                            required
                        >

                        <button
                            class="password-toggle"
                            type="button"
                            data-password-toggle="password"
                        >
                            Hiện
                        </button>
                    </div>
                </div>

                <button
                    class="auth-submit"
                    type="submit"
                >
                    Đăng nhập
                    <span>→</span>
                </button>
            </form>

            <div class="auth-divider">
                <span>hoặc</span>
            </div>

            <p class="auth-change-page">
                Bạn chưa có tài khoản?

                <a href="<?= e(url('register')) ?>">
                    Đăng ký ngay
                </a>
            </p>

            <a
                class="auth-back"
                href="<?= e(url('/')) ?>"
            >
                ← Quay lại trang chủ
            </a>
        </div>
    </div>
</section>

<script>
    // Lấy tất cả nút hiện hoặc ẩn mật khẩu.
    document
        .querySelectorAll('[data-password-toggle]')
        .forEach(function (button) {
            // Bắt sự kiện khi bấm nút.
            button.addEventListener('click', function () {
                // Lấy id input mật khẩu.
                const inputId = button.dataset.passwordToggle;

                // Tìm input tương ứng.
                const input = document.getElementById(inputId);

                // Kiểm tra mật khẩu đang được ẩn hay hiện.
                const isHidden = input.type === 'password';

                // Đổi kiểu input.
                input.type = isHidden ? 'text' : 'password';

                // Đổi nội dung nút.
                button.textContent = isHidden ? 'Ẩn' : 'Hiện';
            });
        });
</script>