<!-- Thanh thông báo trên cùng -->
<div class="topbar">
    Miễn phí vận chuyển cho đơn hàng từ 500.000 đ
</div>

<!-- Header chính -->
<header class="site-header">
    <div class="container header-inner">
        <!-- Logo -->
        <a
            class="logo"
            href="<?= e(url('/')) ?>"
        >
            K<span>FASHION</span>
        </a>

        <!-- Menu điều hướng -->
        <nav class="nav">
    <!-- Trang chủ -->
    <a href="<?= e(url('/')) ?>">
        Trang chủ
    </a>

    <!-- Sản phẩm -->
    <a href="<?= e(url('products')) ?>">
        Sản phẩm
    </a>

    <!-- Đơn hàng chỉ xuất hiện khi đã đăng nhập -->
    <?php if (currentClient()): ?>
        <a href="<?= e(url('orders')) ?>">
            Đơn hàng
        </a>
    <?php endif; ?>
<!-- Giỏ hàng -->
<a
    class="cart-menu"
    href="<?= e(url('cart')) ?>"
    title="Giỏ hàng"
>
    <span class="cart-menu-icon">🛒</span>

    <!-- Luôn tạo thẻ số lượng -->
    <span
        class="cart-menu-count <?= cartCount() <= 0 ? 'cart-menu-count-empty' : '' ?>"
    >
        <?= cartCount() ?>
    </span>
</a>
</nav>

        <!-- Tìm kiếm sản phẩm -->
        <form
            class="header-search"
            action="<?= e(url('products/search')) ?>"
            method="get"
        >
            <!-- Route tìm kiếm -->
            <input
                type="hidden"
                name="action"
                value="products/search"
            >

            <!-- Từ khóa -->
            <input
                type="search"
                name="keyword"
                value="<?= e($_GET['keyword'] ?? '') ?>"
                placeholder="Tìm sản phẩm..."
            >

            <!-- Nút tìm kiếm -->
            <button type="submit">
                Tìm
            </button>
        </form>

        <!-- Tài khoản khách hàng -->
        <div class="account-links">
            <?php if (currentClient()): ?>
                <!-- Tên khách hàng -->
                <a href="<?= e(url('profile')) ?>">
                    Xin chào, <?= e(currentClient()['fullname']) ?>
                </a>

                <!-- Đăng xuất phải gửi bằng POST -->
                <form
                    action="<?= e(url('logout')) ?>"
                    method="post"
                >
                    <?= csrfField() ?>

                    <button
                        class="link-button"
                        type="submit"
                    >
                        Đăng xuất
                    </button>
                </form>
            <?php else: ?>
                <!-- Khi chưa đăng nhập -->
                <a href="<?= e(url('login')) ?>">
                    Đăng nhập
                </a>

                <a href="<?= e(url('register')) ?>">
                    Đăng ký
                </a>
            <?php endif; ?>
        </div>
    </div>
</header>