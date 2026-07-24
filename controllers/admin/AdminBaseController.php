<?php

/**
 * Controller nền cho các trang bắt buộc phải đăng nhập Admin.
 */
class AdminBaseController extends BaseController
{
    /**
     * Hàm khởi tạo chạy trước mọi action của controller con.
     */
    public function __construct()
    {
        // Chặn người chưa đăng nhập truy cập khu vực quản trị.
        requireAdmin();
    }
}
