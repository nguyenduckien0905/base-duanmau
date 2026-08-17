<?php

/**
 * Controller CRUD sản phẩm và biến thể màu - size.
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
        $categoryId = max(0, (int) ($_GET['category_id'] ?? 0));

        // Admin hiển thị 20 sản phẩm trên mỗi trang.
        $perPage = 20;
        $page = max(1, (int) ($_GET['page'] ?? 1));

        // COUNT được chạy trước để xác định phạm vi trang hợp lệ.
        $totalProducts = $this->model->countAll(
            $keyword,
            $categoryId
        );
        $totalPages = max(1, (int) ceil($totalProducts / $perPage));

        // URL page quá lớn được đưa về trang cuối thay vì trả danh sách rỗng.
        $page = min($page, $totalPages);
        $offset = ($page - 1) * $perPage;

        $products = $this->model->getAll(
            $keyword,
            $categoryId,
            $perPage,
            $offset
        );

        // Gửi dữ liệu sang view.
        $this->render('admin/products/index', [
            'pageTitle' => 'Sản phẩm',
            'products' => $products,
            'categories' => $this->categoryModel->getOptions(),
            'keyword' => $keyword,
            'categoryId' => $categoryId,
            'page' => $page,
            'perPage' => $perPage,
            'totalPages' => $totalPages,
            'totalProducts' => $totalProducts,
            'fromProduct' => $totalProducts === 0 ? 0 : $offset + 1,
            'toProduct' => min($offset + $perPage, $totalProducts),
        ]);
    }

    /**
     * Hiển thị và xử lý form thêm sản phẩm.
     */
    public function create(): void
    {
        // Dữ liệu sản phẩm mặc định.
        $product = $this->emptyProduct();

        // Form mới luôn có sẵn một dòng biến thể trống.
        $variants = [
            [
                'color' => '',
                'size' => '',
                'stock' => 0,
            ],
        ];

        // Mảng chứa lỗi kiểm tra dữ liệu.
        $errors = [];

        // Xử lý dữ liệu khi người dùng gửi form.
        if (isPost()) {
            verifyCsrf();

            // Lấy thông tin chung của sản phẩm.
            $product = $this->productDataFromRequest($product);

            // Lấy danh sách các tổ hợp màu - size.
            $variants = $this->variantsFromRequest();

            // Kiểm tra cả sản phẩm và biến thể.
            $errors = array_merge(
                $this->validateProduct($product),
                $this->validateVariants($variants)
            );

            // Chỉ lưu khi dữ liệu hợp lệ.
            if (empty($errors)) {
                try {
                    // Upload ảnh sản phẩm nếu người dùng đã chọn ảnh.
                    $product['image'] = uploadImage('image');

                    // Model lưu sản phẩm và biến thể trong một transaction.
                    $this->model->createWithVariants($product, $variants);

                    setFlash('success', 'Thêm sản phẩm và biến thể thành công.');
                    redirect('admin/products');
                } catch (Throwable $exception) {
                    // Xóa ảnh mới nếu database không lưu được.
                    deleteUploadedImage($product['image']);

                    // Ghi lỗi thật vào Apache log để lập trình viên kiểm tra.
                    error_log($exception->getMessage());

                    $errors[] = 'Không thể thêm sản phẩm. Vui lòng kiểm tra lại biến thể.';
                }
            }
        }

        // Hiển thị form thêm.
        $this->renderProductForm(
            'Thêm sản phẩm',
            $product,
            $variants,
            $errors,
            url('admin/products/create')
        );
    }

    /**
     * Hiển thị và xử lý form sửa sản phẩm.
     */
    public function edit(): void
    {
        // Lấy id từ URL và tìm sản phẩm.
        $id = (int) ($_GET['id'] ?? 0);
        $product = $this->model->find($id);

        // Trả 404 khi sản phẩm không tồn tại.
        if (!$product) {
            abort404();
            return;
        }

        // Lấy các biến thể hiện tại của sản phẩm.
        $variants = $this->model->getVariants($id);

        // Nếu sản phẩm cũ chưa có biến thể thì hiển thị một dòng trống.
        if (empty($variants)) {
            $variants = [
                [
                    'color' => '',
                    'size' => '',
                    'stock' => 0,
                ],
            ];
        }

        // Lưu ảnh cũ để dùng khi người dùng không đổi ảnh.
        $oldImage = $product['image'];

        // Mảng lỗi mặc định.
        $errors = [];

        // Xử lý form POST.
        if (isPost()) {
            verifyCsrf();

            // Lấy lại dữ liệu mới từ form.
            $product = $this->productDataFromRequest($product);
            $variants = $this->variantsFromRequest();

            // Kiểm tra dữ liệu.
            $errors = array_merge(
                $this->validateProduct($product),
                $this->validateVariants($variants)
            );

            if (empty($errors)) {
                $newImage = null;

                try {
                    // Upload ảnh mới nếu có.
                    $newImage = uploadImage('image');

                    // Nếu không có ảnh mới thì tiếp tục sử dụng ảnh cũ.
                    $product['image'] = $newImage ?: $oldImage;

                    // Cập nhật sản phẩm và biến thể trong một transaction.
                    $this->model->updateWithVariants(
                        $id,
                        $product,
                        $variants
                    );

                    // Chỉ xóa ảnh cũ sau khi database cập nhật thành công.
                    if ($newImage) {
                        deleteUploadedImage($oldImage);
                    }

                    setFlash('success', 'Cập nhật sản phẩm và biến thể thành công.');
                    redirect('admin/products');
                } catch (Throwable $exception) {
                    // Xóa ảnh vừa upload nếu cập nhật database thất bại.
                    if ($newImage) {
                        deleteUploadedImage($newImage);
                    }

                    error_log($exception->getMessage());

                    $errors[] = 'Không thể cập nhật sản phẩm. Vui lòng kiểm tra lại biến thể.';
                }
            }
        }

        // Hiển thị form sửa.
        $this->renderProductForm(
            'Sửa sản phẩm',
            $product,
            $variants,
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
            // product_variants tự bị xóa nhờ ON DELETE CASCADE.
            $this->model->delete($id);

            // Xóa ảnh sau khi xóa database thành công.
            deleteUploadedImage($product['image']);

            setFlash('success', 'Xóa sản phẩm thành công.');
        } catch (Throwable $exception) {
            error_log($exception->getMessage());
            setFlash('error', 'Không thể xóa sản phẩm.');
        }

        redirect('admin/products');
    }

    /**
     * Trả bộ dữ liệu mặc định của sản phẩm mới.
     */
    private function emptyProduct(): array
    {
        return [
            'category_id' => '',
            'brand_id' => null,
            'product_name' => '',
            'description' => '',
            'material' => '',
            'price' => 0,
            'image' => null,
            'status' => 1,
        ];
    }

    /**
     * Lấy thông tin chung của sản phẩm từ POST.
     */
    private function productDataFromRequest(array $product): array
    {
        // Không lấy color, size và stock từ products nữa.
        return [
            'category_id' => (int) ($_POST['category_id'] ?? 0),
            'brand_id' => (int) ($_POST['brand_id'] ?? 0) ?: null,
            'product_name' => trim((string) ($_POST['product_name'] ?? '')),
            'description' => trim((string) ($_POST['description'] ?? '')),
            'material' => trim((string) ($_POST['material'] ?? '')),
            'price' => max(0, (float) ($_POST['price'] ?? 0)),
            'image' => $product['image'] ?? null,
            'status' => isset($_POST['status']) ? 1 : 0,
        ];
    }

    /**
     * Lấy các dòng biến thể từ form.
     */
    private function variantsFromRequest(): array
    {
        // Mỗi mảng dùng cùng chỉ số để tạo một tổ hợp.
        $colors = (array) ($_POST['variant_color'] ?? []);
        $sizes = (array) ($_POST['variant_size'] ?? []);
        $stocks = (array) ($_POST['variant_stock'] ?? []);

        // Số dòng lớn nhất được gửi từ form.
        $rowCount = max(
            count($colors),
            count($sizes),
            count($stocks)
        );

        // Danh sách kết quả.
        $variants = [];

        // Ghép màu, size và tồn kho theo cùng một chỉ số.
        for ($index = 0; $index < $rowCount; $index++) {
            $color = trim((string) ($colors[$index] ?? ''));
            $size = trim((string) ($sizes[$index] ?? ''));
            $stockText = trim((string) ($stocks[$index] ?? '0'));

            // Bỏ qua dòng hoàn toàn trống.
            if ($color === '' && $size === '' && $stockText === '') {
                continue;
            }

            // Giữ dòng chưa đầy đủ để validate hiển thị lỗi chính xác.
            $variants[] = [
                'color' => $color,
                'size' => $size,
                'stock' => max(0, (int) $stockText),
            ];
        }

        return $variants;
    }

    /**
     * Kiểm tra thông tin chung của sản phẩm.
     */
    private function validateProduct(array $product): array
    {
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
     * Kiểm tra các tổ hợp màu - size.
     */
    private function validateVariants(array $variants): array
    {
        // Mảng chứa lỗi kiểm tra dữ liệu.
        $errors = [];

        // Mảng dùng để phát hiện tổ hợp màu - size bị trùng.
        $usedCombinations = [];

        // Lấy danh sách màu và kích thước hợp lệ.
        $allowedColors = $this->colorOptions();
        $allowedSizes = $this->sizeOptions();

        // Mỗi sản phẩm phải có ít nhất một biến thể.
        if (empty($variants)) {
            return ['Vui lòng thêm ít nhất một biến thể sản phẩm.'];
        }

        foreach ($variants as $index => $variant) {
            // Số dòng hiển thị cho người dùng bắt đầu từ 1.
            $rowNumber = $index + 1;

            // Kiểm tra màu sắc bắt buộc và phải nằm trong select cho phép.
            if ($variant['color'] === '') {
                $errors[] = "Biến thể dòng {$rowNumber} chưa chọn màu sắc.";
            } elseif (!in_array($variant['color'], $allowedColors, true)) {
                $errors[] = "Màu sắc ở dòng {$rowNumber} không hợp lệ.";
            }

            // Kiểm tra kích thước bắt buộc và phải nằm trong select cho phép.
            if ($variant['size'] === '') {
                $errors[] = "Biến thể dòng {$rowNumber} chưa chọn kích thước.";
            } elseif (!in_array($variant['size'], $allowedSizes, true)) {
                $errors[] = "Kích thước ở dòng {$rowNumber} không hợp lệ.";
            }

            // Chỉ kiểm tra trùng khi cả màu và size đã được chọn.
            if ($variant['color'] !== '' && $variant['size'] !== '') {
                // Tạo khóa đại diện cho một tổ hợp màu - size.
                $combinationKey = $variant['color'] . '|' . $variant['size'];

                // Báo lỗi nếu tổ hợp này đã xuất hiện ở dòng trước.
                if (isset($usedCombinations[$combinationKey])) {
                    $errors[] = 'Biến thể '
                        . $variant['color']
                        . ' - '
                        . $variant['size']
                        . ' bị trùng.';
                }

                // Đánh dấu tổ hợp đã được sử dụng.
                $usedCombinations[$combinationKey] = true;
            }
        }

        return $errors;
    }

    /**
     * Danh sách màu sắc nhân viên được phép chọn.
     */
    private function colorOptions(): array
    {
        return [
            'Đen',
            'Trắng',
            'Xám',
            'Đỏ',
            'Xanh dương',
            'Xanh navy',
            'Xanh lá',
            'Vàng',
            'Cam',
            'Hồng',
            'Tím',
            'Nâu',
            'Be',
        ];
    }

    /**
     * Danh sách kích thước nhân viên được phép chọn.
     */
    private function sizeOptions(): array
    {
        return [
            'Freesize',
            'XS',
            'S',
            'M',
            'L',
            'XL',
            'XXL',
            '28',
            '29',
            '30',
            '31',
            '32',
            '33',
            '34',
            '35',
            '36',
        ];
    }

    /**
     * Hiển thị form sản phẩm cùng các danh sách lựa chọn.
     */
    private function renderProductForm(
        string $pageTitle,
        array $product,
        array $variants,
        array $errors,
        string $formAction
    ): void {
        $this->render('admin/products/form', [
            'pageTitle' => $pageTitle,
            'product' => $product,
            'variants' => $variants,
            'errors' => $errors,
            'formAction' => $formAction,
            'categories' => $this->categoryModel->getOptions(),
            'brands' => $this->brandModel->getOptions(),
            'colorOptions' => $this->colorOptions(),
            'sizeOptions' => $this->sizeOptions(),
        ]);
    }
}
