<?php

/**
 * Controller hiển thị trang tổng quan Admin.
 */
class AdminDashboardController extends AdminBaseController
{
    // Model cung cấp các số liệu thống kê.
    private DashboardModel $model;

    /**
     * Kiểm tra đăng nhập rồi khởi tạo model.
     */
    public function __construct()
    {
        parent::__construct();
        $this->model = new DashboardModel();
    }

    /**
     * Hiển thị dashboard.
     */
    public function index(): void
    {
        // Lấy số liệu và danh sách cần hiển thị.
        $this->render('admin/dashboard/index', [
            'pageTitle' => 'Tổng quan',
            'statistics' => $this->model->getStatistics(),
            'latestOrders' => $this->model->getLatestOrders(),
            'lowStockProducts' => $this->model->getLowStockProducts(),
        ]);
    }
}
