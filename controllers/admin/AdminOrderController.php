<?php

/**
 * Controller quản lý và cập nhật trạng thái đơn hàng.
 */
class AdminOrderController extends AdminBaseController
{
    // Model đơn hàng.
    private OrderModel $model;

    /**
     * Kiểm tra đăng nhập và tạo model.
     */
    public function __construct()
    {
        parent::__construct();
        $this->model = new OrderModel();
    }

    /**
     * Hiển thị danh sách đơn hàng.
     */
    public function index(): void
    {
        // Lấy bộ lọc trên URL.
        $status = trim((string) ($_GET['status'] ?? ''));
        $keyword = trim((string) ($_GET['keyword'] ?? ''));

        // Chỉ chấp nhận status thuộc danh sách định nghĩa.
        if ($status !== '' && !array_key_exists($status, $this->statusLabels())) {
            $status = '';
        }

        // Gửi dữ liệu sang view.
        $this->render('admin/orders/index', [
            'pageTitle' => 'Đơn hàng',
            'orders' => $this->model->getAll($status, $keyword),
            'statusLabels' => $this->statusLabels(),
            'status' => $status,
            'keyword' => $keyword,
        ]);
    }

    /**
     * Hiển thị chi tiết một đơn hàng.
     */
    public function show(): void
    {
        // Lấy id và tìm đơn hàng.
        $id = (int) ($_GET['id'] ?? 0);
        $order = $this->model->find($id);

        // Không hiển thị nếu đơn hàng không tồn tại.
        if (!$order) {
            abort404();
            return;
        }

        // Gửi thông tin đơn và sản phẩm sang view.
        $this->render('admin/orders/show', [
            'pageTitle' => 'Chi tiết đơn #' . $id,
            'order' => $order,
            'items' => $this->model->getItems($id),
            'nextStatuses' => $this->allowedNextStatuses($order['status']),
            'statusLabels' => $this->statusLabels(),
        ]);
    }

    /**
     * Cập nhật trạng thái đơn hàng bằng POST.
     */
    public function updateStatus(): void
    {
        // Chỉ cho phép request POST.
        if (!isPost()) {
            abort404();
            return;
        }

        verifyCsrf();

        // Lấy đơn hàng và trạng thái mới.
        $id = (int) ($_GET['id'] ?? 0);
        $newStatus = trim((string) ($_POST['status'] ?? ''));
        $order = $this->model->find($id);

        // Báo lỗi nếu đơn hàng không còn tồn tại.
        if (!$order) {
            setFlash('error', 'Đơn hàng không tồn tại.');
            redirect('admin/orders');
        }

        // Chỉ cho chuyển theo đúng luồng trạng thái đã định nghĩa.
        $allowed = $this->allowedNextStatuses($order['status']);
        if (!in_array($newStatus, $allowed, true)) {
            setFlash('error', 'Không thể chuyển sang trạng thái đã chọn.');
            redirect('admin/orders/show', ['id' => $id]);
        }

        // Cập nhật database.
        $this->model->updateStatus($id, $newStatus);
        setFlash('success', 'Cập nhật trạng thái đơn hàng thành công.');
        redirect('admin/orders/show', ['id' => $id]);
    }

    /**
     * Trả danh sách nhãn trạng thái.
     */
    private function statusLabels(): array
    {
        return [
            'pending' => 'Chờ xác nhận',
            'confirmed' => 'Đã xác nhận',
            'preparing' => 'Đang chuẩn bị',
            'shipping' => 'Đang giao',
            'completed' => 'Hoàn thành',
            'cancelled' => 'Đã hủy',
        ];
    }

    /**
     * Quy định luồng chuyển trạng thái hợp lệ.
     */
    private function allowedNextStatuses(string $currentStatus): array
    {
        return match ($currentStatus) {
            'pending' => ['confirmed', 'cancelled'],
            'confirmed' => ['preparing', 'cancelled'],
            'preparing' => ['shipping', 'cancelled'],
            'shipping' => ['completed'],
            'completed', 'cancelled' => [],
            default => [],
        };
    }
}
