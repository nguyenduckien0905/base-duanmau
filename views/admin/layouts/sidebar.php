<?php

/*
|--------------------------------------------------------------------------
| SIDEBAR ADMIN
|--------------------------------------------------------------------------
| Lấy giá trị act hiện tại để đánh dấu menu đang được chọn.
|--------------------------------------------------------------------------
*/

$currentAct = $_GET['act'] ?? 'admin-dashboard';

/*
|--------------------------------------------------------------------------
| HÀM KIỂM TRA MENU ACTIVE
|--------------------------------------------------------------------------
| Ví dụ:
| isMenuActive(['admin-products', 'admin-product-create'])
|
| Nếu act hiện tại nằm trong danh sách thì trả về class "active".
|--------------------------------------------------------------------------
*/
function isMenuActive(array $actions, string $currentAct): string
{
    return in_array($currentAct, $actions, true) ? 'active' : '';
}
?>

<aside class="admin-sidebar" id="adminSidebar">

    <!-- Logo hoặc tên website -->
    <a href="index.php?act=admin-dashboard" class="sidebar-brand">
        <i class="bi bi-bag-heart-fill me-2"></i>
        Fashion Admin
    </a>

    <ul class="sidebar-menu">

        <!-- Dashboard -->
        <li>
            <a
                href="index.php?act=admin-dashboard"
                class="<?= isMenuActive(
                    ['admin-dashboard'],
                    $currentAct
                ) ?>">

                <i class="bi bi-speedometer2"></i>
                <span>Tổng quan</span>
            </a>
        </li>

        <!-- Sản phẩm -->
        <li>
            <a
                href="index.php?act=admin-products"
                class="<?= isMenuActive(
                    [
                        'admin-products',
                        'admin-product-create',
                        'admin-product-edit'
                    ],
                    $currentAct
                ) ?>">

                <i class="bi bi-box-seam"></i>
                <span>Sản phẩm</span>
            </a>
        </li>

        <!-- Danh mục -->
        <li>
            <a
                href="index.php?act=admin-categories"
                class="<?= isMenuActive(
                    [
                        'admin-categories',
                        'admin-category-create',
                        'admin-category-edit'
                    ],
                    $currentAct
                ) ?>">

                <i class="bi bi-list-ul"></i>
                <span>Danh mục</span>
            </a>
        </li>

        <!-- Thương hiệu -->
        <li>
            <a
                href="index.php?act=admin-brands"
                class="<?= isMenuActive(
                    [
                        'admin-brands',
                        'admin-brand-create',
                        'admin-brand-edit'
                    ],
                    $currentAct
                ) ?>">

                <i class="bi bi-tags"></i>
                <span>Thương hiệu</span>
            </a>
        </li>

        <!-- Đơn hàng -->
        <li>
            <a
                href="index.php?act=admin-orders"
                class="<?= isMenuActive(
                    [
                        'admin-orders',
                        'admin-order-detail'
                    ],
                    $currentAct
                ) ?>">

                <i class="bi bi-cart-check"></i>
                <span>Đơn hàng</span>
            </a>
        </li>

        <!-- Người dùng -->
        <li>
            <a
                href="index.php?act=admin-users"
                class="<?= isMenuActive(
                    [
                        'admin-users',
                        'admin-user-create',
                        'admin-user-edit'
                    ],
                    $currentAct
                ) ?>">

                <i class="bi bi-people"></i>
                <span>Người dùng</span>
            </a>
        </li>

        <!-- Banner -->
        <li>
            <a
                href="index.php?act=admin-banners"
                class="<?= isMenuActive(
                    [
                        'admin-banners',
                        'admin-banner-create',
                        'admin-banner-edit'
                    ],
                    $currentAct
                ) ?>">

                <i class="bi bi-images"></i>
                <span>Banner</span>
            </a>
        </li>

        <!-- Đánh giá -->
        <li>
            <a
                href="index.php?act=admin-reviews"
                class="<?= isMenuActive(
                    ['admin-reviews'],
                    $currentAct
                ) ?>">

                <i class="bi bi-star"></i>
                <span>Đánh giá</span>
            </a>
        </li>

        <!-- Mã giảm giá -->
        <li>
            <a
                href="index.php?act=admin-coupons"
                class="<?= isMenuActive(
                    ['admin-coupons'],
                    $currentAct
                ) ?>">

                <i class="bi bi-ticket-perforated"></i>
                <span>Mã giảm giá</span>
            </a>
        </li>

        <li>
            <hr class="border-secondary">
        </li>

        <!-- Về trang chủ -->
        <li>
            <a href="index.php">
                <i class="bi bi-house"></i>
                <span>Về trang chủ</span>
            </a>
        </li>

        <!-- Đăng xuất -->
        <li>
            <a
                href="index.php?act=admin-logout"
                class="text-danger"
                onclick="return confirm('Bạn có chắc muốn đăng xuất?')">

                <i class="bi bi-box-arrow-right"></i>
                <span>Đăng xuất</span>
            </a>
        </li>
    </ul>
</aside>