<section class="auth-page">
    <div class="auth-wrapper">
        <!-- Phần giới thiệu -->
        <div class="auth-banner auth-register-banner">
            <a class="auth-logo" href="<?= e(url('/')) ?>">
                KD<span>FASHION</span>
            </a>

            <div class="auth-banner-content">
                <span class="auth-badge">THÀNH VIÊN MỚI</span>

                <h1>Tạo tài khoản và bắt đầu mua sắm</h1>

                <p>
                    Đăng ký tài khoản để quản lý đơn hàng
                    và mua sắm thuận tiện hơn.
                </p>

                <ul class="auth-benefits">
                    <li>✓ Đăng ký hoàn toàn miễn phí</li>
                    <li>✓ Quản lý lịch sử mua hàng</li>
                    <li>✓ Nhận nhiều chương trình ưu đãi</li>
                </ul>
            </div>
        </div>

        <!-- Form đăng ký -->
        <div class="auth-form-side">
            <div class="auth-form-header">
                <span class="auth-form-label">BẮT ĐẦU</span>

                <h2>Đăng ký tài khoản</h2>

                <p>
                    Điền thông tin bên dưới để tạo tài khoản.
                </p>
            </div>

            <!-- Hiển thị lỗi đăng ký -->
            <?php if (!empty($errors)): ?>
                <div class="auth-alert">
                    <?php foreach ($errors as $error): ?>
                        <div>⚠ <?= e($error) ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form
                class="auth-form"
                action="<?= e(url('register')) ?>"
                method="post"
            >
                <?= csrfField() ?>

                <div class="auth-field">
                    <label for="fullname">
                        Họ và tên
                    </label>

                    <div class="auth-input-group">
                        <span class="auth-input-icon">♟</span>

                        <input
                            id="fullname"
                            type="text"
                            name="fullname"
                            value="<?= e($form['fullname']) ?>"
                            placeholder="Nhập họ và tên"
                            autocomplete="name"
                            required
                        >
                    </div>
                </div>

                <div class="auth-field">
                    <label for="email">
                        Địa chỉ email
                    </label>

                    <div class="auth-input-group">
                        <span class="auth-input-icon">✉</span>

                        <input
                            id="email"
                            type="email"
                            name="email"
                            value="<?= e($form['email']) ?>"
                            placeholder="example@gmail.com"
                            autocomplete="email"
                            required
                        >
                    </div>
                </div>

                <div class="auth-field">
                    <label for="phone">
                        Số điện thoại
                    </label>

                    <div class="auth-input-group">
                        <span class="auth-input-icon">☎</span>

                        <input
                            id="phone"
                            type="tel"
                            name="phone"
                            value="<?= e($form['phone']) ?>"
                            placeholder="Nhập số điện thoại"
                            autocomplete="tel"
                        >
                    </div>
                </div>

                <div class="auth-field-row">
                    <div class="auth-field">
                        <label for="password">
                            Mật khẩu
                        </label>

                        <div class="auth-input-group">
                            <span class="auth-input-icon">●</span>

                            <input
                                id="password"
                                type="password"
                                name="password"
                                placeholder="Ít nhất 6 ký tự"
                                minlength="6"
                                autocomplete="new-password"
                                required
                            >
                        </div>
                    </div>

                    <div class="auth-field">
                        <label for="password_confirmation">
                            Nhập lại mật khẩu
                        </label>

                        <div class="auth-input-group">
                            <span class="auth-input-icon">●</span>

                            <input
                                id="password_confirmation"
                                type="password"
                                name="password_confirmation"
                                placeholder="Nhập lại mật khẩu"
                                minlength="6"
                                autocomplete="new-password"
                                required
                            >
                        </div>
                    </div>
                </div>

                <label class="auth-policy">
                    <input type="checkbox" required>

                    <span>
                        Tôi đồng ý với điều khoản sử dụng
                        và chính sách bảo mật.
                    </span>
                </label>

                <button
                    class="auth-submit"
                    type="submit"
                >
                    Tạo tài khoản
                    <span>→</span>
                </button>
            </form>

            <div class="auth-divider">
                <span>hoặc</span>
            </div>

            <p class="auth-change-page">
                Bạn đã có tài khoản?

                <a href="<?= e(url('login')) ?>">
                    Đăng nhập
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