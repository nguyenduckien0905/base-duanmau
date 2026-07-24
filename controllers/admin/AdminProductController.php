<?php

/**
 * Controller CRUD sản phẩm.
 */
class AdminProductController extends AdminBaseController
{
    // Model sản phẩm.
    private ProductModel $model;

    // Model danh mục dùng cho bộ lọc và form.
    private CategoryModel $categoryModel;

    // Model thương hiệu dùng cho form.
    private BrandModel $brandModel;

    /**
     * Kiểm tra đăng nhập và khởi tạo các model.
     */
    public function __construct()
    {
        parent::__construct();
        $this->model = new ProductModel();
        $this->categoryModel = new CategoryModel();
        $this->brandModel = new BrandModel();
    }

    /**
     * Hiển thị danh sách sản phẩm.
     */
    public function index(): void
    {
        // Lấy điều kiện lọc từ URL.
        $keyword = trim((string) ($_GET['keyword'] ?? ''));
        $categoryId = (int) ($_GET['category_id'] ?? 0);

        // Gửi dữ liệu sang view.
        $this->render('admin/products/index', [
            'pageTitle' => 'Sản phẩm',
            'products' => $this->model->getAll($keyword, $categoryId),
            'categories' => $this->categoryModel->getOptions(),
            'keyword' => $keyword,
            'categoryId' => $categoryId,
        ]);
    }

    /**
     * Hiển thị và xử lý form thêm sản phẩm.
     */
    public function create(): void
    {
        // Tạo bộ dữ liệu mặc định cho form.
        $product = $this->emptyProduct();
        $errors = [];

        // Xử lý dữ liệu POST.
        if (isPost()) {
            verifyCsrf();

            // Chuẩn hóa dữ liệu từ form.
            $product = $this->productDataFromRequest($product);

            // Kiểm tra các trường quan trọng.
            $errors = $this->validateProduct($product);

            if (empty($errors)) {
                try {
                    // Ảnh sản phẩm không bắt buộc.
                    $product['image'] = uploadImage('image');
                    $this->model->create($product);

                    setFlash('success', 'Thêm sản phẩm thành công.');
                    redirect('admin/products');
                } catch (Throwable $exception) {
                    deleteUploadedImage($product['image']);
                    $errors[] = 'Không thể thêm sản phẩm.';
                }
            }
        }

        // Hiển thị form thêm.
        $this->renderProductForm(
            'Thêm sản phẩm',
            $product,
            $errors,
            url('admin/products/create')
        );
    }

    /**
     * Hiển thị và xử lý form sửa sản phẩm.
     */
    public function edit(): void
    {
        // Tìm sản phẩm theo id.
        $id = (int) ($_GET['id'] ?? 0);
        $product = $this->model->find($id);

        if (!$product) {
            abort404();
            return;
        }

        // Lưu ảnh cũ để dùng khi người dùng không đổi ảnh.
        $oldImage = $product['image'];
        $errors = [];

        // Xử lý dữ liệu POST.
        if (isPost()) {
            verifyCsrf();

            $product = $this->productDataFromRequest($product);
            $errors = $this->validateProduct($product);

            if (empty($errors)) {
                $newImage = null;

                try {
                    // Upload ảnh mới nếu người dùng đã chọn file.
                    $newImage = uploadImage('image');
                    $product['image'] = $newImage ?: $oldImage;

                    // Cập nhật database.
                    $this->model->update($id, $product);

                    // Xóa ảnh cũ sau khi cập nhật thành công.
                    if ($newImage) {
                        deleteUploadedImage($oldImage);
                    }

                    setFlash('success', 'Cập nhật sản phẩm thành công.');
                    redirect('admin/products');
                } catch (Throwable $exception) {
                    if ($newImage) {
                        deleteUploadedImage($newImage);
                    }
                    $errors[] = 'Không thể cập nhật sản phẩm.';
                }
            }
        }

        // Hiển thị form sửa.
        $this->renderProductForm(
            'Sửa sản phẩm',
            $product,
            $errors,
            url('admin/products/edit', ['id' => $id])
        );
    }

    /**
     * Xóa sản phẩm.
     */
    public function delete(): void
    {
        // Chỉ cho phép xóa bằng POST.
        if (!isPost()) {
            abort404();
            return;
        }

        verifyCsrf();

        // Tìm sản phẩm trước khi xóa.
        $id = (int) ($_GET['id'] ?? 0);
        $product = $this->model->find($id);

        if (!$product) {
            setFlash('error', 'Sản phẩm không tồn tại.');
            redirect('admin/products');
        }

        try {
            // Xóa bản ghi và ảnh liên quan.
            $this->model->delete($id);
            deleteUploadedImage($product['image']);
            setFlash('success', 'Xóa sản phẩm thành công.');
        } catch (Throwable $exception) {
            setFlash('error', 'Không thể xóa sản phẩm.');
        }

        redirect('admin/products');
    }

    /**
     * Trả bộ dữ liệu mặc định của một sản phẩm mới.
     */
    private function emptyProduct(): array
    {
        return [
            'category_id' => '',
            'brand_id' => null,
            'product_name' => '',
            'description' => '',
            'material' => '',
            'color' => '',
            'size' => '',
            'stock' => 0,
            'price' => 0,
            'image' => null,
            'status' => 1,
        ];
    }

    /**
     * Lấy dữ liệu sản phẩm từ request POST.
     */
    private function productDataFromRequest(array $product): array
    {
        // Chỉ trả đúng các cột model cần để PDO không nhận tham số thừa.
        return [
            // Ép kiểu số cho khóa ngoại.
            'category_id' => (int) ($_POST['category_id'] ?? 0),
            'brand_id' => (int) ($_POST['brand_id'] ?? 0) ?: null,

            // Làm sạch khoảng trắng ở các chuỗi.
            'product_name' => trim((string) ($_POST['product_name'] ?? '')),
            'description' => trim((string) ($_POST['description'] ?? '')),
            'material' => trim((string) ($_POST['material'] ?? '')),
            'color' => trim((string) ($_POST['color'] ?? '')),
            'size' => trim((string) ($_POST['size'] ?? '')),

            // Không cho tồn kho và giá nhận số âm.
            'stock' => max(0, (int) ($_POST['stock'] ?? 0)),
            'price' => max(0, (float) ($_POST['price'] ?? 0)),

            // Giữ ảnh hiện tại cho đến khi upload được ảnh mới.
            'image' => $product['image'] ?? null,

            // Checkbox được chọn tương ứng status bằng 1.
            'status' => isset($_POST['status']) ? 1 : 0,
        ];
    }

    /**
     * Kiểm tra dữ liệu sản phẩm.
     */
    private function validateProduct(array $product): array
    {
        // Khởi tạo danh sách lỗi.
        $errors = [];

        if ($product['product_name'] === '') {
            $errors[] = 'Vui lòng nhập tên sản phẩm.';
        }

        if ((int) $product['category_id'] <= 0) {
            $errors[] = 'Vui lòng chọn danh mục.';
        }

        if ((float) $product['price'] <= 0) {
            $errors[] = 'Giá sản phẩm phải lớn hơn 0.';
        }

        return $errors;
    }

    /**
     * Nạp form sản phẩm với các danh sách lựa chọn.
     */
    private function renderProductForm(
        string $pageTitle,
        array $product,
        array $errors,
        string $formAction
    ): void {
        $this->render('admin/products/form', [
            'pageTitle' => $pageTitle,
            'product' => $product,
            'errors' => $errors,
            'formAction' => $formAction,
            'categories' => $this->categoryModel->getOptions(),
            'brands' => $this->brandModel->getOptions(),
        ]);
    }
}
