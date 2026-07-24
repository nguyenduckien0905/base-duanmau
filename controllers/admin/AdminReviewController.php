<?php

/**
 * Controller kiểm duyệt đánh giá sản phẩm.
 */
class AdminReviewController extends AdminBaseController
{
    // Model đánh giá.
    private ReviewModel $model;

    /**
     * Kiểm tra đăng nhập và tạo model.
     */
    public function __construct()
    {
        parent::__construct();
        $this->model = new ReviewModel();
    }

    /**
     * Hiển thị danh sách đánh giá.
     */
    public function index(): void
    {
        $this->render('admin/reviews/index', [
            'pageTitle' => 'Đánh giá',
            'reviews' => $this->model->getAll(),
        ]);
    }

    /**
     * Ẩn hoặc hiện đánh giá.
     */
    public function toggleStatus(): void
    {
        // Chỉ xử lý form POST.
        if (!isPost()) {
            abort404();
            return;
        }

        verifyCsrf();

        // Cập nhật trạng thái theo id.
        $id = (int) ($_GET['id'] ?? 0);
        $this->model->toggleStatus($id);

        setFlash('success', 'Cập nhật trạng thái đánh giá thành công.');
        redirect('admin/reviews');
    }

    /**
     * Xóa đánh giá.
     */
    public function delete(): void
    {
        // Chỉ chấp nhận POST để tránh xóa do bấm nhầm đường dẫn.
        if (!isPost()) {
            abort404();
            return;
        }

        verifyCsrf();

        // Xóa theo id.
        $id = (int) ($_GET['id'] ?? 0);
        $this->model->delete($id);

        setFlash('success', 'Xóa đánh giá thành công.');
        redirect('admin/reviews');
    }
}
