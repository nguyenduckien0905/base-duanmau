<?php

/**
 * Controller CRUD banner.
 */
class AdminBannerController extends AdminBaseController
{
    // Model banner.
    private BannerModel $model;

    /**
     * Kiểm tra đăng nhập và tạo model.
     */
    public function __construct()
    {
        parent::__construct();
        $this->model = new BannerModel();
    }

    /**
     * Hiển thị danh sách banner.
     */
    public function index(): void
    {
        $this->render('admin/banners/index', [
            'pageTitle' => 'Banner',
            'banners' => $this->model->getAll(),
        ]);
    }

    /**
     * Thêm banner.
     */
    public function create(): void
    {
        // Dữ liệu mặc định.
        $banner = [
            'title' => '',
            'image' => null,
            'link' => '',
            'status' => 1,
        ];
        $errors = [];

        // Xử lý form POST.
        if (isPost()) {
            verifyCsrf();

            $banner['title'] = trim((string) ($_POST['title'] ?? ''));
            $banner['link'] = trim((string) ($_POST['link'] ?? ''));
            $banner['status'] = isset($_POST['status']) ? 1 : 0;

            if ($banner['title'] === '') {
                $errors[] = 'Vui lòng nhập tiêu đề banner.';
            }

            if (empty($errors)) {
                try {
                    // Banner bắt buộc phải có ảnh.
                    $banner['image'] = uploadImage('image');

                    if (!$banner['image']) {
                        throw new RuntimeException('Vui lòng chọn ảnh banner.');
                    }

                    $this->model->create($banner);
                    setFlash('success', 'Thêm banner thành công.');
                    redirect('admin/banners');
                } catch (Throwable $exception) {
                    deleteUploadedImage($banner['image']);
                    $errors[] = $exception instanceof RuntimeException
                        ? $exception->getMessage()
                        : 'Không thể thêm banner.';
                }
            }
        }

        // Hiển thị form thêm.
        $this->render('admin/banners/form', [
            'pageTitle' => 'Thêm banner',
            'banner' => $banner,
            'errors' => $errors,
            'formAction' => url('admin/banners/create'),
        ]);
    }

    /**
     * Sửa banner.
     */
    public function edit(): void
    {
        // Tìm banner theo id.
        $id = (int) ($_GET['id'] ?? 0);
        $banner = $this->model->find($id);

        if (!$banner) {
            abort404();
            return;
        }

        // Lưu ảnh cũ.
        $oldImage = $banner['image'];
        $errors = [];

        // Xử lý form POST.
        if (isPost()) {
            verifyCsrf();

            $banner['title'] = trim((string) ($_POST['title'] ?? ''));
            $banner['link'] = trim((string) ($_POST['link'] ?? ''));
            $banner['status'] = isset($_POST['status']) ? 1 : 0;

            if ($banner['title'] === '') {
                $errors[] = 'Vui lòng nhập tiêu đề banner.';
            }

            if (empty($errors)) {
                $newImage = null;

                try {
                    // Giữ ảnh cũ khi không chọn ảnh mới.
                    $newImage = uploadImage('image');
                    $banner['image'] = $newImage ?: $oldImage;

                    // Chỉ truyền đúng các cột cần cập nhật cho model.
                    $this->model->update($id, [
                        'title' => $banner['title'],
                        'image' => $banner['image'],
                        'link' => $banner['link'],
                        'status' => $banner['status'],
                    ]);

                    if ($newImage) {
                        deleteUploadedImage($oldImage);
                    }

                    setFlash('success', 'Cập nhật banner thành công.');
                    redirect('admin/banners');
                } catch (Throwable $exception) {
                    if ($newImage) {
                        deleteUploadedImage($newImage);
                    }
                    $errors[] = 'Không thể cập nhật banner.';
                }
            }
        }

        // Hiển thị form sửa.
        $this->render('admin/banners/form', [
            'pageTitle' => 'Sửa banner',
            'banner' => $banner,
            'errors' => $errors,
            'formAction' => url('admin/banners/edit', ['id' => $id]),
        ]);
    }

    /**
     * Xóa banner.
     */
    public function delete(): void
    {
        // Chỉ xử lý POST.
        if (!isPost()) {
            abort404();
            return;
        }

        verifyCsrf();

        // Tìm banner trước khi xóa.
        $id = (int) ($_GET['id'] ?? 0);
        $banner = $this->model->find($id);

        if (!$banner) {
            setFlash('error', 'Banner không tồn tại.');
            redirect('admin/banners');
        }

        // Xóa database rồi xóa ảnh.
        $this->model->delete($id);
        deleteUploadedImage($banner['image']);

        setFlash('success', 'Xóa banner thành công.');
        redirect('admin/banners');
    }
}
