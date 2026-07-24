<?php

/**
 * Controller nền chứa các thao tác dùng chung cho mọi controller.
 */
class BaseController
{
    /**
     * Hiển thị một view bên trong layout chính của Admin.
     */
    protected function render(string $view, array $data = []): void
    {
        // Chuyển từng phần tử của mảng data thành biến cho view sử dụng.
        extract($data);

        // Nạp layout Admin; layout sẽ nạp tiếp view con.
        require PATH_VIEW_ADMIN_MAIN;
    }

    /**
     * Hiển thị view độc lập, dùng cho trang đăng nhập.
     */
    protected function renderStandalone(string $view, array $data = []): void
    {
        // Chuyển dữ liệu controller gửi sang thành biến.
        extract($data);

        // Nạp trực tiếp file view không qua layout Admin.
        require PATH_VIEW . $view . '.php';
    }
}
