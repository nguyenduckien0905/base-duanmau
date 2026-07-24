<?php

/**
 * Controller xử lý đăng nhập và đăng xuất Admin.
 */
class AdminAuthController extends BaseController
{
    // Model dùng để tìm tài khoản đăng nhập.
    private AdminAuthModel $model;

    /**
     * Khởi tạo model xác thực.
     */
    public function __construct()
    {
        $this->model = new AdminAuthModel();
    }

    /**
     * Hiển thị và xử lý form đăng nhập.
     */
    public function login(): void
    {
        // Người đã đăng nhập không cần xem lại form đăng nhập.
        if (!empty($_SESSION['admin'])) {
            redirect('admin/dashboard');
        }

        // Khởi tạo mảng lỗi rỗng cho view.
        $errors = [];

        // Chỉ kiểm tra tài khoản khi người dùng gửi form POST.
        if (isPost()) {
            // Kiểm tra token chống gửi form giả mạo.
            verifyCsrf();

            // Lấy và làm sạch dữ liệu từ form.
            $email = trim((string) ($_POST['email'] ?? ''));
            $password = (string) ($_POST['password'] ?? '');

            // Kiểm tra email bắt buộc và đúng định dạng.
            if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Email không hợp lệ.';
            }

            // Kiểm tra mật khẩu bắt buộc.
            if ($password === '') {
                $errors[] = 'Vui lòng nhập mật khẩu.';
            }

            // Chỉ truy vấn database khi dữ liệu đầu vào hợp lệ.
            if (empty($errors)) {
                $user = $this->model->findByEmail($email);

                // Kiểm tra tài khoản tồn tại và mật khẩu khớp hash.
                if (!$user || !password_verify($password, $user['password'])) {
                    $errors[] = 'Email hoặc mật khẩu không đúng.';
                } elseif ((int) $user['status'] !== 1) {
                    // Tài khoản bị khóa không được đăng nhập.
                    $errors[] = 'Tài khoản của bạn đang bị khóa.';
                } elseif (!in_array((int) $user['role_id'], [1, 2], true)) {
                    // Chỉ vai trò admin và staff được vào trang quản trị.
                    $errors[] = 'Tài khoản này không có quyền truy cập Admin.';
                } else {
                    // Đổi session id để chống chiếm quyền phiên đăng nhập.
                    session_regenerate_id(true);

                    // Chỉ lưu các thông tin cần thiết vào session.
                    $_SESSION['admin'] = [
                        'user_id' => (int) $user['user_id'],
                        'fullname' => $user['fullname'],
                        'email' => $user['email'],
                        'role_id' => (int) $user['role_id'],
                        'role_name' => $user['role_name'],
                        'avatar' => $user['avatar'],
                    ];

                    // Thông báo và chuyển đến dashboard.
                    setFlash('success', 'Đăng nhập thành công.');
                    redirect('admin/dashboard');
                }
            }
        }

        // Hiển thị form cùng các lỗi nếu có.
        $this->renderStandalone('admin/auth/login', [
            'errors' => $errors,
        ]);
    }

    /**
     * Đăng xuất tài khoản Admin.
     */
    public function logout(): void
    {
        // Chỉ cho phép đăng xuất bằng form POST.
        if (!isPost()) {
            abort404();
            return;
        }

        // Kiểm tra token của form đăng xuất.
        verifyCsrf();

        // Xóa riêng dữ liệu đăng nhập Admin khỏi session.
        unset($_SESSION['admin']);

        // Tạo session id mới sau khi đăng xuất.
        session_regenerate_id(true);

        // Chuyển về trang đăng nhập.
        setFlash('success', 'Bạn đã đăng xuất.');
        redirect('admin/login');
    }
}
