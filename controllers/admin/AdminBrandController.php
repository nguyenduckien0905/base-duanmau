<?php

/**
 * Controller CRUD thương hiệu.
 */
class AdminBrandController extends AdminBaseController
{
    // Model thao tác dữ liệu thương hiệu.
    private BrandModel $model;

    /**
     * Kiểm tra đăng nhập và tạo model.
     */
    public function __construct()
    {
        parent::__construct();
        $this->model = new BrandModel();
    }

    /**
     * Hiển thị danh sách thương hiệu.
     */
    public function index(): void
    {
        $this->render('admin/brands/index', [
            'pageTitle' => 'Thương hiệu',
            'brands' => $this->model->getAll(),
        ]);
    }

    /**
     * Thêm thương hiệu.
     */
    public function create(): void
    {
        // Dữ liệu mặc định của form.
        $brand = [
            'brand_name' => '',
            'logo' => null,
            'status' => 1,
        ];
        $errors = [];

        // Xử lý form POST.
        if (isPost()) {
            verifyCsrf();

            $brand['brand_name'] = trim((string) ($_POST['brand_name'] ?? ''));
            $brand['status'] = isset($_POST['status']) ? 1 : 0;

            // Tên thương hiệu không được để trống.
            if ($brand['brand_name'] === '') {
                $errors[] = 'Vui lòng nhập tên thương hiệu.';
            }

            if (empty($errors)) {
                try {
                    // Logo không bắt buộc.
                    $brand['logo'] = uploadImage('logo');
                    $this->model->create($brand);

                    setFlash('success', 'Thêm thương hiệu thành công.');
                    redirect('admin/brands');
                } catch (Throwable $exception) {
                    deleteUploadedImage($brand['logo']);
                    $errors[] = 'Không thể thêm thương hiệu. Tên có thể đã tồn tại.';
                }
            }
        }

        // Hiển thị form.
        $this->render('admin/brands/form', [
            'pageTitle' => 'Thêm thương hiệu',
            'brand' => $brand,
            'errors' => $errors,
            'formAction' => url('admin/brands/create'),
        ]);
    }

    /**
     * Sửa thương hiệu.
     */
    public function edit(): void
    {
        // Tìm thương hiệu theo id trên URL.
        $id = (int) ($_GET['id'] ?? 0);
        $brand = $this->model->find($id);

        if (!$brand) {
            abort404();
            return;
        }

        // Lưu logo cũ để có thể xóa sau khi đổi logo.
        $oldLogo = $brand['logo'];
        $errors = [];

        // Xử lý form POST.
        if (isPost()) {
            verifyCsrf();

            $brand['brand_name'] = trim((string) ($_POST['brand_name'] ?? ''));
            $brand['status'] = isset($_POST['status']) ? 1 : 0;

            if ($brand['brand_name'] === '') {
                $errors[] = 'Vui lòng nhập tên thương hiệu.';
            }

            if (empty($errors)) {
                $newLogo = null;

                try {
                    // Dùng logo mới nếu có, nếu không tiếp tục dùng logo cũ.
                    $newLogo = uploadImage('logo');
                    $brand['logo'] = $newLogo ?: $oldLogo;

                    $this->model->update($id, [
                        'brand_name' => $brand['brand_name'],
                        'logo' => $brand['logo'],
                        'status' => $brand['status'],
                    ]);

                    // Chỉ xóa logo cũ sau khi database cập nhật thành công.
                    if ($newLogo) {
                        deleteUploadedImage($oldLogo);
                    }

                    setFlash('success', 'Cập nhật thương hiệu thành công.');
                    redirect('admin/brands');
                } catch (Throwable $exception) {
                    if ($newLogo) {
                        deleteUploadedImage($newLogo);
                    }
                    $errors[] = 'Không thể cập nhật thương hiệu.';
                }
            }
        }

        // Hiển thị form sửa.
        $this->render('admin/brands/form', [
            'pageTitle' => 'Sửa thương hiệu',
            'brand' => $brand,
            'errors' => $errors,
            'formAction' => url('admin/brands/edit', ['id' => $id]),
        ]);
    }

    /**
     * Xóa thương hiệu.
     */
    public function delete(): void
    {
        // Chỉ chấp nhận request POST.
        if (!isPost()) {
            abort404();
            return;
        }

        verifyCsrf();

        // Tìm bản ghi cần xóa.
        $id = (int) ($_GET['id'] ?? 0);
        $brand = $this->model->find($id);

        if (!$brand) {
            setFlash('error', 'Thương hiệu không tồn tại.');
            redirect('admin/brands');
        }

        try {
            // Khi xóa brand, khóa ngoại của sản phẩm sẽ tự đổi brand_id thành null.
            $this->model->delete($id);
            deleteUploadedImage($brand['logo']);
            setFlash('success', 'Xóa thương hiệu thành công.');
        } catch (Throwable $exception) {
            setFlash('error', 'Không thể xóa thương hiệu.');
        }

        redirect('admin/brands');
    }
}
