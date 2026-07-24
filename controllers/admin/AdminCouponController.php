<?php

/**
 * Controller CRUD mã giảm giá.
 */
class AdminCouponController extends AdminBaseController
{
    // Model mã giảm giá.
    private CouponModel $model;

    /**
     * Kiểm tra đăng nhập và tạo model.
     */
    public function __construct()
    {
        parent::__construct();
        $this->model = new CouponModel();
    }

    /**
     * Hiển thị danh sách mã giảm giá.
     */
    public function index(): void
    {
        $this->render('admin/coupons/index', [
            'pageTitle' => 'Mã giảm giá',
            'coupons' => $this->model->getAll(),
        ]);
    }

    /**
     * Thêm mã giảm giá.
     */
    public function create(): void
    {
        // Dữ liệu mặc định cho form.
        $coupon = $this->emptyCoupon();
        $errors = [];

        // Xử lý dữ liệu POST.
        if (isPost()) {
            verifyCsrf();

            $coupon = $this->couponDataFromRequest();
            $errors = $this->validateCoupon($coupon);

            if (empty($errors)) {
                try {
                    $this->model->create($coupon);
                    setFlash('success', 'Thêm mã giảm giá thành công.');
                    redirect('admin/coupons');
                } catch (Throwable $exception) {
                    $errors[] = 'Không thể thêm mã giảm giá. Mã có thể đã tồn tại.';
                }
            }
        }

        // Hiển thị form thêm.
        $this->renderCouponForm(
            'Thêm mã giảm giá',
            $coupon,
            $errors,
            url('admin/coupons/create')
        );
    }

    /**
     * Sửa mã giảm giá.
     */
    public function edit(): void
    {
        // Tìm mã theo id.
        $id = (int) ($_GET['id'] ?? 0);
        $coupon = $this->model->find($id);

        if (!$coupon) {
            abort404();
            return;
        }

        $errors = [];

        // Xử lý form POST.
        if (isPost()) {
            verifyCsrf();

            $coupon = $this->couponDataFromRequest();
            $errors = $this->validateCoupon($coupon);

            if (empty($errors)) {
                try {
                    $this->model->update($id, $coupon);
                    setFlash('success', 'Cập nhật mã giảm giá thành công.');
                    redirect('admin/coupons');
                } catch (Throwable $exception) {
                    $errors[] = 'Không thể cập nhật mã giảm giá.';
                }
            }
        }

        // Hiển thị form sửa.
        $this->renderCouponForm(
            'Sửa mã giảm giá',
            $coupon,
            $errors,
            url('admin/coupons/edit', ['id' => $id])
        );
    }

    /**
     * Xóa mã giảm giá.
     */
    public function delete(): void
    {
        // Chỉ xử lý bằng POST.
        if (!isPost()) {
            abort404();
            return;
        }

        verifyCsrf();

        // Lấy id và thử xóa.
        $id = (int) ($_GET['id'] ?? 0);

        try {
            $this->model->delete($id);
            setFlash('success', 'Xóa mã giảm giá thành công.');
        } catch (Throwable $exception) {
            // Coupon đã dùng trong đơn hàng có thể bị khóa ngoại chặn xóa.
            setFlash('error', 'Không thể xóa mã đã được sử dụng trong đơn hàng.');
        }

        redirect('admin/coupons');
    }

    /**
     * Trả dữ liệu mặc định cho form.
     */
    private function emptyCoupon(): array
    {
        return [
            'code' => '',
            'discount_type' => 'percent',
            'discount_value' => 0,
            'min_order_value' => 0,
            'max_discount' => null,
            'start_date' => date('Y-m-d H:i:s'),
            'end_date' => date('Y-m-d H:i:s', strtotime('+30 days')),
            'quantity' => 0,
            'status' => 1,
        ];
    }

    /**
     * Chuẩn hóa dữ liệu mã giảm giá từ form.
     */
    private function couponDataFromRequest(): array
    {
        // Ô max_discount có thể để trống nên cần đổi thành null.
        $maxDiscount = trim((string) ($_POST['max_discount'] ?? ''));

        return [
            'code' => strtoupper(trim((string) ($_POST['code'] ?? ''))),
            'discount_type' => (string) ($_POST['discount_type'] ?? 'percent'),
            'discount_value' => max(0, (float) ($_POST['discount_value'] ?? 0)),
            'min_order_value' => max(0, (float) ($_POST['min_order_value'] ?? 0)),
            'max_discount' => $maxDiscount === '' ? null : max(0, (float) $maxDiscount),
            'start_date' => mysqlDateTime((string) ($_POST['start_date'] ?? '')),
            'end_date' => mysqlDateTime((string) ($_POST['end_date'] ?? '')),
            'quantity' => max(0, (int) ($_POST['quantity'] ?? 0)),
            'status' => isset($_POST['status']) ? 1 : 0,
        ];
    }

    /**
     * Kiểm tra dữ liệu mã giảm giá.
     */
    private function validateCoupon(array $coupon): array
    {
        // Khởi tạo mảng lỗi.
        $errors = [];

        if ($coupon['code'] === '') {
            $errors[] = 'Vui lòng nhập mã giảm giá.';
        }

        if (!in_array($coupon['discount_type'], ['percent', 'fixed'], true)) {
            $errors[] = 'Loại giảm giá không hợp lệ.';
        }

        if ((float) $coupon['discount_value'] <= 0) {
            $errors[] = 'Giá trị giảm phải lớn hơn 0.';
        }

        if (
            $coupon['discount_type'] === 'percent'
            && (float) $coupon['discount_value'] > 100
        ) {
            $errors[] = 'Phần trăm giảm không được lớn hơn 100.';
        }

        if ($coupon['start_date'] === '' || $coupon['end_date'] === '') {
            $errors[] = 'Ngày bắt đầu và kết thúc không hợp lệ.';
        } elseif (strtotime($coupon['end_date']) <= strtotime($coupon['start_date'])) {
            $errors[] = 'Ngày kết thúc phải sau ngày bắt đầu.';
        }

        return $errors;
    }

    /**
     * Hiển thị form mã giảm giá.
     */
    private function renderCouponForm(
        string $pageTitle,
        array $coupon,
        array $errors,
        string $formAction
    ): void {
        $this->render('admin/coupons/form', [
            'pageTitle' => $pageTitle,
            'coupon' => $coupon,
            'errors' => $errors,
            'formAction' => $formAction,
        ]);
    }
}
