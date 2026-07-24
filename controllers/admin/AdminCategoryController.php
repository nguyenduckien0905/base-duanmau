<?php

/**
 * Controller CRUD danh mục sản phẩm.
 */
class AdminCategoryController extends AdminBaseController
{
    // Model danh mục được dùng trong mọi action.
    private CategoryModel $model;

    /**
     * Kiểm tra đăng nhập và tạo model.
     */
    public function __construct()
    {
        parent::__construct();
        $this->model = new CategoryModel();
    }

    /**
     * Hiển thị danh sách danh mục.
     */
    public function index(): void
    {
        $this->render('admin/categories/index', [
            'pageTitle' => 'Danh mục',
            'categories' => $this->model->getAll(),
        ]);
    }

    /**
     * Hiển thị và xử lý form thêm danh mục.
     */
    public function create(): void
    {
        // Khởi tạo dữ liệu mặc định cho form.
        $category = [
            'parent_id' => '',
            'name' => '',
            'image' => null,
            'status' => 1,
        ];
        $errors = [];

        // Xử lý khi form được gửi.
        if (isPost()) {
            verifyCsrf();

            // Lấy dữ liệu từ form.
            $category['parent_id'] = (int) ($_POST['parent_id'] ?? 0) ?: null;
            $category['name'] = trim((string) ($_POST['name'] ?? ''));
            $category['status'] = isset($_POST['status']) ? 1 : 0;

            // Tên danh mục là dữ liệu bắt buộc.
            if ($category['name'] === '') {
                $errors[] = 'Vui lòng nhập tên danh mục.';
            }

            // Upload và lưu khi dữ liệu hợp lệ.
            if (empty($errors)) {
                try {
                    $category['image'] = uploadImage('image');
                    $this->model->create($category);

                    setFlash('success', 'Thêm danh mục thành công.');
                    redirect('admin/categories');
                } catch (Throwable $exception) {
                    // Xóa ảnh vừa upload nếu database không lưu được.
                    deleteUploadedImage($category['image']);
                    $errors[] = 'Không thể thêm danh mục. Tên có thể đã tồn tại.';
                }
            }
        }

        // Hiển thị form thêm.
        $this->render('admin/categories/form', [
            'pageTitle' => 'Thêm danh mục',
            'category' => $category,
            'parentCategories' => $this->model->getOptions(),
            'errors' => $errors,
            'formAction' => url('admin/categories/create'),
        ]);
    }

    /**
     * Hiển thị và xử lý form sửa danh mục.
     */
    public function edit(): void
    {
        // Lấy id từ URL.
        $id = (int) ($_GET['id'] ?? 0);
        $category = $this->model->find($id);

        // Trả 404 khi id không tồn tại.
        if (!$category) {
            abort404();
            return;
        }

        // Lưu đường dẫn ảnh cũ để xử lý sau khi cập nhật.
        $oldImage = $category['image'];
        $errors = [];

        // Xử lý dữ liệu POST.
        if (isPost()) {
            verifyCsrf();

            // Lấy dữ liệu mới.
            $category['parent_id'] = (int) ($_POST['parent_id'] ?? 0) ?: null;
            $category['name'] = trim((string) ($_POST['name'] ?? ''));
            $category['status'] = isset($_POST['status']) ? 1 : 0;

            // Không cho danh mục chọn chính nó làm danh mục cha.
            if ((int) $category['parent_id'] === $id) {
                $errors[] = 'Danh mục không thể là cha của chính nó.';
            }

            // Kiểm tra tên bắt buộc.
            if ($category['name'] === '') {
                $errors[] = 'Vui lòng nhập tên danh mục.';
            }

            // Chỉ cập nhật khi không có lỗi.
            if (empty($errors)) {
                $newImage = null;

                try {
                    // Giữ ảnh cũ khi người dùng không chọn ảnh mới.
                    $newImage = uploadImage('image');
                    $category['image'] = $newImage ?: $oldImage;

                    // Cập nhật database.
                    $this->model->update($id, [
                        'parent_id' => $category['parent_id'],
                        'name' => $category['name'],
                        'image' => $category['image'],
                        'status' => $category['status'],
                    ]);

                    // Xóa ảnh cũ sau khi ảnh mới đã được lưu thành công.
                    if ($newImage) {
                        deleteUploadedImage($oldImage);
                    }

                    setFlash('success', 'Cập nhật danh mục thành công.');
                    redirect('admin/categories');
                } catch (Throwable $exception) {
                    // Xóa ảnh mới nếu quá trình cập nhật thất bại.
                    if ($newImage) {
                        deleteUploadedImage($newImage);
                    }
                    $errors[] = 'Không thể cập nhật danh mục.';
                }
            }
        }

        // Hiển thị form sửa.
        $this->render('admin/categories/form', [
            'pageTitle' => 'Sửa danh mục',
            'category' => $category,
            'parentCategories' => $this->model->getOptions($id),
            'errors' => $errors,
            'formAction' => url('admin/categories/edit', ['id' => $id]),
        ]);
    }

    /**
     * Xóa danh mục bằng POST.
     */
    public function delete(): void
    {
        // Chặn truy cập trực tiếp bằng GET.
        if (!isPost()) {
            abort404();
            return;
        }

        verifyCsrf();

        // Lấy và kiểm tra bản ghi cần xóa.
        $id = (int) ($_GET['id'] ?? 0);
        $category = $this->model->find($id);

        if (!$category) {
            setFlash('error', 'Danh mục không tồn tại.');
            redirect('admin/categories');
        }

        try {
            // Xóa database trước, sau đó mới xóa file ảnh.
            $this->model->delete($id);
            deleteUploadedImage($category['image']);
            setFlash('success', 'Xóa danh mục thành công.');
        } catch (Throwable $exception) {
            // Khóa ngoại sẽ chặn xóa danh mục đang có sản phẩm.
            setFlash('error', 'Không thể xóa danh mục đang có sản phẩm.');
        }

        redirect('admin/categories');
    }
}
