<?php

/**
 * Controller quản lý trạng thái giao hàng và duyệt thanh toán.
 */
class AdminOrderController extends AdminBaseController
{
    private OrderModel $model;

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
        $status = trim((string) ($_GET['status'] ?? ''));
        $keyword = trim((string) ($_GET['keyword'] ?? ''));

        if ($status !== '' && !array_key_exists($status, $this->statusLabels())) {
            $status = '';
        }

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
        $id = (int) ($_GET['id'] ?? 0);
        $order = $this->model->find($id);

        if (!$order) {
            abort404();
            return;
        }

        $this->render('admin/orders/show', [
            'pageTitle' => 'Chi tiết đơn #' . $id,
            'order' => $order,
            'items' => $this->model->getItems($id),
            'nextStatuses' => $this->allowedNextStatuses($order['status']),
            'statusLabels' => $this->statusLabels(),
        ]);
    }

    /**
     * Cập nhật trạng thái giao hàng bằng POST.
     */
    public function updateStatus(): void
    {
        if (!isPost()) {
            abort404();
            return;
        }

        verifyCsrf();

        $id = (int) ($_GET['id'] ?? 0);
        $newStatus = trim((string) ($_POST['status'] ?? ''));
        $order = $this->model->find($id);

        if (!$order) {
            setFlash('error', 'Đơn hàng không tồn tại.');
            redirect('admin/orders');
        }

        $allowed = $this->allowedNextStatuses($order['status']);
        if (!in_array($newStatus, $allowed, true)) {
            setFlash('error', 'Không thể chuyển sang trạng thái đã chọn.');
            redirect('admin/orders/show', ['id' => $id]);
        }

        // Đơn chuyển khoản phải được duyệt tiền trước khi xác nhận đơn.
        if (
            $newStatus === 'confirmed'
            && $order['payment_method'] === 'bank_transfer'
            && $order['payment_status'] !== 'paid'
        ) {
            setFlash(
                'error',
                'Hãy kiểm tra và xác nhận thanh toán trước khi xác nhận đơn.'
            );
            redirect('admin/orders/show', ['id' => $id]);
        }

        try {
            $this->model->updateStatus($id, $newStatus);

            $message = $newStatus === 'delivered'
                ? 'Đã xác nhận giao hàng thành công. Chờ khách xác nhận đã nhận.'
                : 'Cập nhật trạng thái đơn hàng thành công.';

            setFlash('success', $message);
        } catch (Throwable $exception) {
            setFlash('error', 'Không thể cập nhật trạng thái đơn hàng.');
        }

        redirect('admin/orders/show', ['id' => $id]);
    }

    /**
     * Duyệt hoặc từ chối thanh toán/chứng từ chuyển khoản.
     */
    public function updatePayment(): void
    {
        if (!isPost()) {
            abort404();
            return;
        }

        verifyCsrf();

        $id = (int) ($_GET['id'] ?? 0);
        $paymentStatus = trim((string) ($_POST['payment_status'] ?? ''));
        $adminNote = trim((string) ($_POST['admin_note'] ?? ''));
        $order = $this->model->find($id);

        if (!$order) {
            setFlash('error', 'Đơn hàng không tồn tại.');
            redirect('admin/orders');
        }

        if (!in_array($paymentStatus, ['pending', 'paid', 'failed'], true)) {
            setFlash('error', 'Trạng thái thanh toán không hợp lệ.');
            redirect('admin/orders/show', ['id' => $id]);
        }

        if (
            $order['payment_method'] === 'bank_transfer'
            && $paymentStatus === 'paid'
            && empty($order['proof_image'])
        ) {
            setFlash('error', 'Đơn chuyển khoản chưa có ảnh minh chứng.');
            redirect('admin/orders/show', ['id' => $id]);
        }

        $this->model->updatePaymentStatus($id, $paymentStatus, $adminNote);
        setFlash('success', 'Đã cập nhật trạng thái thanh toán.');
        redirect('admin/orders/show', ['id' => $id]);
    }

    /**
     * Nhãn trạng thái dùng chung cho bộ lọc và form.
     */
    private function statusLabels(): array
    {
        return [
            'pending' => 'Chờ xác nhận',
            'confirmed' => 'Đã xác nhận',
            'preparing' => 'Đang chuẩn bị',
            'shipping' => 'Đang giao',
            'delivered' => 'Đã giao - chờ khách xác nhận',
            'completed' => 'Hoàn thành',
            'cancelled' => 'Đã hủy',
        ];
    }

    /**
     * Admin chỉ đánh dấu đến “đã giao”; Client tự xác nhận hoàn thành.
     */
    private function allowedNextStatuses(string $currentStatus): array
    {
        return match ($currentStatus) {
            'pending' => ['confirmed', 'cancelled'],
            'confirmed' => ['preparing', 'cancelled'],
            'preparing' => ['shipping', 'cancelled'],
            'shipping' => ['delivered'],
            'delivered', 'completed', 'cancelled' => [],
            default => [],
        };
    }
}
