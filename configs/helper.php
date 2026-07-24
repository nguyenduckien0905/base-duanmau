<?php

// ==================================================
// HELPER CỦA CLIENT
// ==================================================

if (!function_exists('debug')) {
    /**
     * In dữ liệu để kiểm tra và dừng chương trình.
     */
    function debug($data)
    {
        echo '<pre>';
        print_r($data);
        die;
    }
}

if (!function_exists('upload_file')) {
    /**
     * Upload file theo cách đang được phần Client sử dụng.
     */
    function upload_file($folder, $file)
    {
        // Tạo đường dẫn file cần lưu.
        $targetFile = $folder . '/' . time() . '-' . $file['name'];

        // Di chuyển file vào thư mục uploads.
        if (
            move_uploaded_file(
                $file['tmp_name'],
                PATH_ASSETS_UPLOADS . $targetFile
            )
        ) {
            return $targetFile;
        }

        // Thông báo khi upload thất bại.
        throw new Exception('Upload file không thành công!');
    }
}

// ==================================================
// HELPER DÙNG CHUNG CHO ADMIN
// ==================================================

if (!function_exists('e')) {
    /**
     * Mã hóa dữ liệu trước khi in ra HTML để chống XSS.
     */
    function e($value): string
    {
        return htmlspecialchars(
            (string) $value,
            ENT_QUOTES,
            'UTF-8'
        );
    }
}

if (!function_exists('url')) {
    /**
     * Tạo URL theo action của router.
     */
    function url(string $action = '/', array $params = []): string
    {
        // Trang chủ không cần thêm action.
        if ($action === '/') {
            return BASE_URL . 'index.php';
        }

        // Ghép action với các tham số như id hoặc keyword.
        $query = http_build_query(
            array_merge(
                ['action' => $action],
                $params
            )
        );

        // Trả về URL hoàn chỉnh.
        return BASE_URL . 'index.php?' . $query;
    }
}

if (!function_exists('redirect')) {
    /**
     * Chuyển hướng đến một action khác.
     */
    function redirect(string $action, array $params = []): void
    {
        // Gửi header chuyển trang.
        header('Location: ' . url($action, $params));

        // Dừng chương trình sau khi chuyển trang.
        exit;
    }
}

if (!function_exists('isPost')) {
    /**
     * Kiểm tra request hiện tại có phải POST hay không.
     */
    function isPost(): bool
    {
        return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
    }
}

// ==================================================
// CSRF
// ==================================================

if (!function_exists('csrfToken')) {
    /**
     * Tạo hoặc lấy token CSRF trong session.
     */
    function csrfToken(): string
    {
        // Chỉ tạo token nếu session chưa có.
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        // Trả token hiện tại.
        return $_SESSION['csrf_token'];
    }
}

if (!function_exists('csrfField')) {
    /**
     * Tạo input ẩn chứa token CSRF.
     */
    function csrfField(): string
    {
        return '<input type="hidden" name="csrf_token" value="'
            . e(csrfToken())
            . '">';
    }
}

if (!function_exists('verifyCsrf')) {
    /**
     * Kiểm tra token CSRF khi gửi form POST.
     */
    function verifyCsrf(): void
    {
        // Lấy token từ form.
        $submittedToken = (string) ($_POST['csrf_token'] ?? '');

        // So sánh token trong form với token trong session.
        if (!hash_equals(csrfToken(), $submittedToken)) {
            http_response_code(419);

            exit(
                'Phiên làm việc đã hết hạn. '
                . 'Vui lòng tải lại trang và thử lại.'
            );
        }
    }
}

// ==================================================
// THÔNG BÁO FLASH
// ==================================================

if (!function_exists('setFlash')) {
    /**
     * Lưu thông báo dùng một lần vào session.
     */
    function setFlash(string $type, string $message): void
    {
        $_SESSION['flash'] = [
            'type' => $type,
            'message' => $message,
        ];
    }
}

if (!function_exists('getFlash')) {
    /**
     * Lấy và xóa thông báo khỏi session.
     */
    function getFlash(): ?array
    {
        // Không có thông báo thì trả null.
        if (empty($_SESSION['flash'])) {
            return null;
        }

        // Lưu thông báo vào biến tạm.
        $flash = $_SESSION['flash'];

        // Xóa thông báo khỏi session.
        unset($_SESSION['flash']);

        // Trả thông báo cho view.
        return $flash;
    }
}

// ==================================================
// XÁC THỰC ADMIN
// ==================================================

if (!function_exists('requireAdmin')) {
    /**
     * Bắt buộc phải đăng nhập mới được truy cập Admin.
     */
    function requireAdmin(): void
    {
        // Nếu chưa đăng nhập thì chuyển về trang login.
        if (empty($_SESSION['admin'])) {
            setFlash(
                'error',
                'Bạn cần đăng nhập để truy cập trang quản trị.'
            );

            redirect('admin/login');
        }
    }
}

if (!function_exists('currentAdmin')) {
    /**
     * Lấy thông tin Admin đang đăng nhập.
     */
    function currentAdmin(): ?array
    {
        return $_SESSION['admin'] ?? null;
    }
}

// ==================================================
// ĐỊNH DẠNG DỮ LIỆU
// ==================================================

if (!function_exists('formatPrice')) {
    /**
     * Định dạng tiền Việt Nam.
     */
    function formatPrice($price): string
    {
        return number_format(
            (float) $price,
            0,
            ',',
            '.'
        ) . ' đ';
    }
}

if (!function_exists('orderStatusText')) {
    /**
     * Đổi trạng thái đơn hàng sang tiếng Việt.
     */
    function orderStatusText(string $status): string
    {
        return match ($status) {
            'pending' => 'Chờ xác nhận',
            'confirmed' => 'Đã xác nhận',
            'preparing' => 'Đang chuẩn bị',
            'shipping' => 'Đang giao',
            'completed' => 'Hoàn thành',
            'cancelled' => 'Đã hủy',
            default => 'Không xác định',
        };
    }
}

if (!function_exists('orderStatusClass')) {
    /**
     * Lấy class CSS tương ứng trạng thái đơn hàng.
     */
    function orderStatusClass(string $status): string
    {
        return match ($status) {
            'completed' => 'badge-success',
            'cancelled' => 'badge-danger',
            'shipping' => 'badge-info',

            'confirmed',
            'preparing' => 'badge-warning',

            default => 'badge-muted',
        };
    }
}

if (!function_exists('mysqlDateTime')) {
    /**
     * Đổi datetime-local sang định dạng DATETIME của MySQL.
     */
    function mysqlDateTime(string $value): string
    {
        // Chuyển chuỗi ngày thành timestamp.
        $timestamp = strtotime($value);

        // Trả chuỗi rỗng nếu ngày không hợp lệ.
        if ($timestamp === false) {
            return '';
        }

        // Trả ngày giờ theo định dạng MySQL.
        return date('Y-m-d H:i:s', $timestamp);
    }
}

// ==================================================
// UPLOAD ẢNH ADMIN
// ==================================================

if (!function_exists('uploadImage')) {
    /**
     * Upload ảnh Admin và trả đường dẫn tương đối.
     */
    function uploadImage(
        string $fieldName,
        string $folder = 'admin'
    ): ?string {
        // Không xử lý khi form không có file.
        if (empty($_FILES[$fieldName])) {
            return null;
        }

        // Lấy thông tin file upload.
        $file = $_FILES[$fieldName];

        // Người dùng chưa chọn file.
        if (
            ($file['error'] ?? UPLOAD_ERR_NO_FILE)
            === UPLOAD_ERR_NO_FILE
        ) {
            return null;
        }

        // PHP báo lỗi trong quá trình upload.
        if (
            ($file['error'] ?? UPLOAD_ERR_OK)
            !== UPLOAD_ERR_OK
        ) {
            throw new RuntimeException(
                'Không thể tải ảnh lên.'
            );
        }

        // Giới hạn dung lượng ảnh tối đa 2 MB.
        if (($file['size'] ?? 0) > 2 * 1024 * 1024) {
            throw new RuntimeException(
                'Ảnh không được lớn hơn 2 MB.'
            );
        }

        // Đọc loại MIME thật của file.
        $fileInfo = new finfo(FILEINFO_MIME_TYPE);

        // Lấy MIME của file tạm.
        $mime = $fileInfo->file($file['tmp_name']);

        // Danh sách định dạng ảnh hợp lệ.
        $allowedMimes = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
        ];

        // Từ chối file không phải ảnh hợp lệ.
        if (!isset($allowedMimes[$mime])) {
            throw new RuntimeException(
                'Chỉ chấp nhận ảnh JPG, PNG hoặc WEBP.'
            );
        }

        // Tạo đường dẫn thư mục cần lưu.
        $destinationDirectory = PATH_ASSETS_UPLOADS
            . trim($folder, '/')
            . '/';

        // Tạo thư mục nếu chưa tồn tại.
        if (!is_dir($destinationDirectory)) {
            mkdir(
                $destinationDirectory,
                0775,
                true
            );
        }

        // Tạo tên file ngẫu nhiên để tránh trùng.
        $fileName = date('YmdHis')
            . '-'
            . bin2hex(random_bytes(5))
            . '.'
            . $allowedMimes[$mime];

        // Đường dẫn đầy đủ của file.
        $destinationPath = $destinationDirectory
            . $fileName;

        // Di chuyển file vào thư mục upload.
        if (
            !move_uploaded_file(
                $file['tmp_name'],
                $destinationPath
            )
        ) {
            throw new RuntimeException(
                'Không thể lưu ảnh vào thư mục uploads.'
            );
        }

        // Trả đường dẫn tương đối để lưu database.
        return trim($folder, '/')
            . '/'
            . $fileName;
    }
}

if (!function_exists('deleteUploadedImage')) {
    /**
     * Xóa ảnh cũ do khu vực Admin upload.
     */
    function deleteUploadedImage(
        ?string $relativePath
    ): void {
        // Không xử lý khi không có đường dẫn.
        if (empty($relativePath)) {
            return;
        }

        // Chuẩn hóa dấu gạch chéo.
        $normalizedPath = str_replace(
            '\\',
            '/',
            $relativePath
        );

        // Chỉ xóa file nằm trong thư mục admin.
        if (
            !str_starts_with(
                $normalizedPath,
                'admin/'
            )
        ) {
            return;
        }

        // Ghép thành đường dẫn đầy đủ.
        $absolutePath = PATH_ASSETS_UPLOADS
            . $normalizedPath;

        // Chỉ xóa khi file tồn tại.
        if (is_file($absolutePath)) {
            unlink($absolutePath);
        }
    }
}

// ==================================================
// TRANG 404
// ==================================================

if (!function_exists('abort404')) {
    /**
     * Trả về lỗi không tìm thấy trang.
     */
    function abort404(): void
    {
        // Đặt HTTP status code là 404.
        http_response_code(404);

        // In thông báo.
        echo '404 - Không tìm thấy trang.';
    }
}