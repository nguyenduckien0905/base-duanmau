<?php

/*
|--------------------------------------------------------------------------
| HEADER ADMIN
|--------------------------------------------------------------------------
| File này chứa:
| - Thẻ HTML mở đầu.
| - Bootstrap CSS.
| - Bootstrap Icons.
| - Thanh điều hướng phía trên.
|--------------------------------------------------------------------------
*/

// Lấy thông tin người đăng nhập từ session.
// Nếu chưa đăng nhập thì dùng thông tin mặc định.
$currentUser = $_SESSION['user'] ?? [
    'fullname' => 'Quản trị viên',
    'avatar'   => null
];
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">

    <!-- Giúp giao diện tương thích với điện thoại -->
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>
        <?= htmlspecialchars($pageTitle) ?> - Shop quần áo
    </title>

    <!-- Bootstrap CSS -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
        rel="stylesheet">

    <style>
        :root {
            --sidebar-width: 260px;
            --sidebar-background: #212529;
            --sidebar-text: #adb5bd;
            --sidebar-active: #0d6efd;
        }

        body {
            background-color: #f4f6f9;
            min-height: 100vh;
        }

        /* Thanh menu bên trái */
        .admin-sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: var(--sidebar-width);
            height: 100vh;
            overflow-y: auto;
            background-color: var(--sidebar-background);
            z-index: 1030;
        }

        /* Nội dung chính */
        .admin-main {
            margin-left: var(--sidebar-width);
            min-height: calc(100vh - 60px);
        }

        /* Thanh điều hướng phía trên */
        .admin-navbar {
            margin-left: var(--sidebar-width);
            height: 60px;
        }

        .sidebar-brand {
            height: 60px;
            display: flex;
            align-items: center;
            padding: 0 20px;
            color: white;
            font-size: 20px;
            font-weight: bold;
            text-decoration: none;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar-menu {
            list-style: none;
            padding: 15px 10px;
            margin: 0;
        }

        .sidebar-menu li {
            margin-bottom: 5px;
        }

        .sidebar-menu a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 11px 14px;
            color: var(--sidebar-text);
            text-decoration: none;
            border-radius: 6px;
            transition: 0.2s;
        }

        .sidebar-menu a:hover,
        .sidebar-menu a.active {
            color: white;
            background-color: var(--sidebar-active);
        }

        .sidebar-menu i {
            font-size: 18px;
        }

        .dashboard-card {
            border: none;
            border-radius: 12px;
            color: white;
            overflow: hidden;
        }

        .dashboard-card .card-body {
            padding: 22px;
        }

        .dashboard-card .statistic-icon {
            font-size: 45px;
            opacity: 0.7;
        }

        .table-card {
            border: none;
            border-radius: 12px;
        }

        /*
        |--------------------------------------------------------------------------
        | GIAO DIỆN ĐIỆN THOẠI
        |--------------------------------------------------------------------------
        */
        @media (max-width: 991px) {
            .admin-sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s;
            }

            .admin-sidebar.show {
                transform: translateX(0);
            }

            .admin-navbar,
            .admin-main {
                margin-left: 0;
            }
        }
    </style>
</head>

<body>

<!-- Thanh điều hướng phía trên -->
<nav class="navbar navbar-expand bg-white shadow-sm admin-navbar">
    <div class="container-fluid">

        <!-- Nút mở sidebar trên điện thoại -->
        <button
            class="btn btn-outline-secondary d-lg-none"
            id="toggleSidebar"
            type="button">

            <i class="bi bi-list"></i>
        </button>

        <span class="navbar-text ms-2">
            Xin chào,
            <strong>
                <?= htmlspecialchars($currentUser['fullname']) ?>
            </strong>
        </span>

        <!-- Menu người dùng -->
        <div class="dropdown ms-auto">
            <button
                class="btn btn-light dropdown-toggle"
                type="button"
                data-bs-toggle="dropdown">

                <i class="bi bi-person-circle me-1"></i>

                <?= htmlspecialchars($currentUser['fullname']) ?>
            </button>

            <ul class="dropdown-menu dropdown-menu-end">
                <li>
                    <a
                        class="dropdown-item"
                        href="index.php?act=admin-profile">

                        <i class="bi bi-person me-2"></i>
                        Thông tin cá nhân
                    </a>
                </li>

                <li>
                    <hr class="dropdown-divider">
                </li>

                <li>
                    <a
                        class="dropdown-item text-danger"
                        href="index.php?act=admin-logout"
                        onclick="return confirm('Bạn có chắc muốn đăng xuất?')">

                        <i class="bi bi-box-arrow-right me-2"></i>
                        Đăng xuất
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>