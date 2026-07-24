<?php

/**
 * Controller quản lý tài khoản người dùng.
 */
class AdminUserController extends AdminBaseController
{
    // Model tài khoản.
    private UserModel $model;

    /**
     * Kiểm tra đăng nhập và tạo model.
     */
    public function __construct()
    {
        parent::__construct();
        $this->model = new UserModel();
    }

    /**
     * Hiển thị danh sách tài khoản.
     */
    public function index(): void
    {
        // Nhận điều kiện tìm kiếm và lọc vai trò.
        $keyword = trim((string) ($_GET['keyword'] ?? ''));
        $roleId = (int) ($_GET['role_id'] ?? 0);

        // Gửi dữ liệu sang view.
        $this->render('admin/users/index', [
            'pageTitle' => 'Tài khoản',
            'users' => $this->model->getAll($keyword, $roleId),
            'keyword' => $keyword,
            'roleId' => $roleId,
        ]);
    }

    /**
     * Khóa hoặc mở khóa tài khoản.
     */
    public function toggleStatus(): void
    {
        // Chỉ xử lý bằng POST.
        if (!isPost()) {
            abort404();
            return;
        }

        verifyCsrf();

        // Lấy id tài khoản cần thay đổi.
        $id = (int) ($_GET['id'] ?? 0);
        $user = $this->model->find($id);

        // Báo lỗi nếu tài khoản không tồn tại.
        if (!$user) {
            setFlash('error', 'Tài khoản không tồn tại.');
            redirect('admin/users');
        }

        // Không cho người quản trị tự khóa chính mình.
        if ($id === (int) (currentAdmin()['user_id'] ?? 0)) {
            setFlash('error', 'Bạn không thể tự khóa tài khoản đang đăng nhập.');
            redirect('admin/users');
        }

        // Đảo trạng thái tài khoản.
        $this->model->toggleStatus($id);
        setFlash('success', 'Cập nhật trạng thái tài khoản thành công.');
        redirect('admin/users');
    }
}
