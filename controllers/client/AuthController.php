<?php

/**
 * Controller đăng ký, đăng nhập, đăng xuất
 * và cập nhật tài khoản khách hàng.
 */
class AuthController
{
    // Model thao tác với bảng users.
    private ClientAuthModel $model;

    /**
     * Khởi tạo model tài khoản Client.
     */
    public function __construct()
    {
        $this->model = new ClientAuthModel();
    }

    /**
     * Hiển thị và xử lý đăng ký.
     */
    public function register(): void
    {
        // Nếu đã đăng nhập thì chuyển về trang chủ.
        if (!empty($_SESSION['client'])) {
            redirect('/');
        }

        // Mảng chứa lỗi validate.
        $errors = [];

        /**
         * Giữ dữ liệu cũ khi form đăng ký bị lỗi.
         *
         * Không sử dụng address vì bảng users
         * không có cột address.
         */
        $form = [
            'fullname' => trim(
                (string) ($_POST['fullname'] ?? '')
            ),

            'email' => trim(
                (string) ($_POST['email'] ?? '')
            ),

            'phone' => trim(
                (string) ($_POST['phone'] ?? '')
            ),

            /**
             * Giữ khóa address rỗng để view đăng ký cũ
             * không bị lỗi Undefined array key.
             *
             * Dữ liệu này không được lưu vào bảng users.
             */
            'address' => '',
        ];

        // Chỉ xử lý dữ liệu khi form gửi bằng POST.
        if (isPost()) {
            // Kiểm tra CSRF token.
            verifyCsrf();

            // Lấy mật khẩu.
            $password = (string) (
                $_POST['password'] ?? ''
            );

            // Lấy mật khẩu nhập lại.
            $passwordConfirmation = (string) (
                $_POST['password_confirmation'] ?? ''
            );

            // Kiểm tra họ tên.
            if ($form['fullname'] === '') {
                $errors[] = 'Vui lòng nhập họ tên.';
            }

            // Kiểm tra email.
            if (
                $form['email'] === ''
                || !filter_var(
                    $form['email'],
                    FILTER_VALIDATE_EMAIL
                )
            ) {
                $errors[] = 'Email không hợp lệ.';
            } elseif (
                $this->model->findByEmail($form['email'])
            ) {
                // Không cho đăng ký trùng email.
                $errors[] = 'Email này đã được sử dụng.';
            }

            /**
             * Số điện thoại không bắt buộc.
             * Nếu có nhập thì phải hợp lệ.
             */
            if (
                $form['phone'] !== ''
                && !preg_match(
                    '/^[0-9+ .-]{9,15}$/',
                    $form['phone']
                )
            ) {
                $errors[] = 'Số điện thoại không hợp lệ.';
            }

            // Mật khẩu phải có ít nhất 6 ký tự.
            if (strlen($password) < 6) {
                $errors[] =
                    'Mật khẩu phải có ít nhất 6 ký tự.';
            }

            // Kiểm tra mật khẩu nhập lại.
            if ($password !== $passwordConfirmation) {
                $errors[] =
                    'Mật khẩu nhập lại không khớp.';
            }

            // Chỉ tạo tài khoản khi không có lỗi.
            if (empty($errors)) {
                /**
                 * Chỉ gửi các trường tồn tại
                 * trong bảng users sang model.
                 *
                 * Không có address trong mảng này.
                 */
                $accountData = [
                    'fullname' => $form['fullname'],
                    'email' => $form['email'],
                    'phone' => $form['phone'],

                    // Mã hóa mật khẩu trước khi lưu.
                    'password' => password_hash(
                        $password,
                        PASSWORD_DEFAULT
                    ),
                ];

                // Tạo tài khoản role_id = 3.
                $this->model->create($accountData);

                // Tạo thông báo thành công.
                setFlash(
                    'success',
                    'Đăng ký thành công. Bạn có thể đăng nhập.'
                );

                // Chuyển đến trang đăng nhập.
                redirect('login');
            }
        }

        // Tiêu đề trang.
        $pageTitle = 'Đăng ký';

        // View đăng ký.
        $contentView =
            PATH_VIEW . 'client/account/register.php';

        // Nạp layout Client.
        require PATH_VIEW_CLIENT_MAIN;
    }

    /**
     * Hiển thị và xử lý đăng nhập.
     */
    public function login(): void
    {
        // Nếu đã đăng nhập thì chuyển về trang chủ.
        if (!empty($_SESSION['client'])) {
            redirect('/');
        }

        // Mảng chứa lỗi đăng nhập.
        $errors = [];

        // Lấy email từ form.
        $email = trim(
            (string) ($_POST['email'] ?? '')
        );

        // Chỉ xử lý khi form gửi bằng POST.
        if (isPost()) {
            // Kiểm tra CSRF.
            verifyCsrf();

            // Lấy mật khẩu.
            $password = (string) (
                $_POST['password'] ?? ''
            );

            // Kiểm tra email.
            if (
                $email === ''
                || !filter_var(
                    $email,
                    FILTER_VALIDATE_EMAIL
                )
            ) {
                $errors[] = 'Email không hợp lệ.';
            }

            // Kiểm tra mật khẩu.
            if ($password === '') {
                $errors[] = 'Vui lòng nhập mật khẩu.';
            }

            // Chỉ truy vấn khi dữ liệu hợp lệ.
            if (empty($errors)) {
                // Tìm người dùng theo email.
                $user = $this->model->findByEmail($email);

                // Kiểm tra tài khoản và mật khẩu.
                if (
                    !$user
                    || !password_verify(
                        $password,
                        $user['password']
                    )
                ) {
                    $errors[] =
                        'Email hoặc mật khẩu không đúng.';
                } elseif ((int) $user['role_id'] !== 3) {
                    // Chỉ role_id = 3 được đăng nhập Client.
                    $errors[] =
                        'Tài khoản này không phải tài khoản khách hàng.';
                } elseif ((int) $user['status'] !== 1) {
                    // Tài khoản bị Admin khóa.
                    $errors[] =
                        'Tài khoản của bạn đang bị khóa.';
                } else {
                    // Tạo session id mới.
                    session_regenerate_id(true);

                    /**
                     * Lưu thông tin khách hàng vào session.
                     *
                     * Không đọc $user['address'] vì bảng users
                     * không có cột address.
                     */
                    $_SESSION['client'] = [
                        'user_id' => (int) $user['user_id'],
                        'fullname' => $user['fullname'],
                        'email' => $user['email'],
                        'phone' => $user['phone'] ?? '',

                        // Địa chỉ để trống và nhập ở checkout.
                        'address' => '',
                    ];

                    // Thông báo đăng nhập thành công.
                    setFlash(
                        'success',
                        'Đăng nhập thành công.'
                    );

                    /**
                     * Nếu đăng nhập từ trang thanh toán
                     * thì quay lại checkout.
                     */
                    if (
                        ($_GET['next'] ?? '') === 'checkout'
                    ) {
                        redirect('checkout');
                    }

                    // Nếu không thì về trang chủ.
                    redirect('/');
                }
            }
        }

        // Tiêu đề trang.
        $pageTitle = 'Đăng nhập';

        // View đăng nhập.
        $contentView =
            PATH_VIEW . 'client/account/login.php';

        // Nạp layout Client.
        require PATH_VIEW_CLIENT_MAIN;
    }

    /**
     * Đăng xuất tài khoản khách hàng.
     */
    public function logout(): void
    {
        // Chỉ cho đăng xuất bằng POST.
        if (!isPost()) {
            abort404();
            return;
        }

        // Kiểm tra CSRF.
        verifyCsrf();

        // Xóa session Client.
        unset($_SESSION['client']);

        // Tạo session id mới.
        session_regenerate_id(true);

        // Tạo thông báo.
        setFlash(
            'success',
            'Bạn đã đăng xuất.'
        );

        // Chuyển về trang chủ.
        redirect('/');
    }

    /**
     * Hiển thị và cập nhật hồ sơ khách hàng.
     */
    public function profile(): void
    {
        // Bắt buộc đăng nhập.
        requireClient();

        // Lấy thông tin session Client.
        $client = currentClient();

        // Lấy id tài khoản.
        $userId = (int) $client['user_id'];

        // Lấy thông tin mới nhất từ database.
        $user = $this->model->find($userId);

        // Nếu tài khoản không tồn tại.
        if (!$user) {
            unset($_SESSION['client']);

            setFlash(
                'error',
                'Tài khoản không còn tồn tại.'
            );

            redirect('login');
        }

        // Nếu tài khoản bị Admin khóa.
        if ((int) $user['status'] !== 1) {
            unset($_SESSION['client']);

            setFlash(
                'error',
                'Tài khoản của bạn đã bị khóa.'
            );

            redirect('login');
        }

        // Mảng chứa lỗi cập nhật.
        $errors = [];

        /**
         * Dữ liệu hiển thị trên form hồ sơ.
         *
         * address chỉ được lưu trong session,
         * không được lưu trong bảng users.
         */
        $form = [
            'fullname' => trim(
                (string) (
                    $_POST['fullname']
                    ?? $user['fullname']
                )
            ),

            'phone' => trim(
                (string) (
                    $_POST['phone']
                    ?? ($user['phone'] ?? '')
                )
            ),

            'address' => trim(
                (string) (
                    $_POST['address']
                    ?? ($client['address'] ?? '')
                )
            ),
        ];

        // Xử lý cập nhật bằng POST.
        if (isPost()) {
            // Kiểm tra CSRF.
            verifyCsrf();

            // Kiểm tra họ tên.
            if ($form['fullname'] === '') {
                $errors[] = 'Vui lòng nhập họ tên.';
            }

            // Kiểm tra số điện thoại.
            if (
                $form['phone'] !== ''
                && !preg_match(
                    '/^[0-9+ .-]{9,15}$/',
                    $form['phone']
                )
            ) {
                $errors[] =
                    'Số điện thoại không hợp lệ.';
            }

            // Chỉ cập nhật khi không có lỗi.
            if (empty($errors)) {
                /**
                 * Chỉ gửi các trường có trong bảng users.
                 *
                 * Không gửi address vào model.
                 */
                $profileData = [
                    'fullname' => $form['fullname'],
                    'phone' => $form['phone'],
                ];

                // Cập nhật bảng users.
                $this->model->updateProfile(
                    $userId,
                    $profileData
                );

                // Đồng bộ họ tên vào session.
                $_SESSION['client']['fullname'] =
                    $form['fullname'];

                // Đồng bộ số điện thoại vào session.
                $_SESSION['client']['phone'] =
                    $form['phone'];

                /**
                 * Địa chỉ chỉ lưu tạm trong session.
                 *
                 * Khi đặt hàng, địa chỉ sẽ được lưu vào:
                 * orders.shipping_address
                 */
                $_SESSION['client']['address'] =
                    $form['address'];

                // Thông báo thành công.
                setFlash(
                    'success',
                    'Cập nhật hồ sơ thành công.'
                );

                // Tải lại trang hồ sơ.
                redirect('profile');
            }
        }

        // Email chỉ hiển thị, không cho sửa.
        $email = $user['email'];

        // Tiêu đề trang.
        $pageTitle = 'Tài khoản của tôi';

        // View hồ sơ.
        $contentView =
            PATH_VIEW . 'client/account/profile.php';

        // Nạp layout Client.
        require PATH_VIEW_CLIENT_MAIN;
    }
}
