<?php

// File này nằm trong controllers/client để tách biệt với AdminProductController.
// Controller xử lý toàn bộ luồng xem sản phẩm phía Client.
class ClientProductController
{
    // Model dùng để đọc dữ liệu sản phẩm từ cơ sở dữ liệu.
    private ClientProductModel $productModel;

    private ClientReviewModel $reviewModel;

    // Hàm khởi tạo controller.
    public function __construct()
    {
        // Tạo đối tượng model để sử dụng cho danh sách, tìm kiếm và chi tiết.
        $this->productModel = new ClientProductModel();
        $this->reviewModel = new ClientReviewModel();
    }

    // Hiển thị danh sách, tìm kiếm, lọc và sắp xếp sản phẩm.
    public function index(): void
    {
        // Lấy từ khóa trên URL; ép kiểu chuỗi và xóa khoảng trắng hai đầu.
        $keyword = trim((string) ($_GET['keyword'] ?? ''));

        // Lấy mã danh mục; max(0, ...) giúp không nhận số âm.
        $categoryId = max(0, (int) ($_GET['category_id'] ?? 0));

        // Nếu URL không có sort thì mặc định sắp xếp sản phẩm mới nhất.
        $sort = (string) ($_GET['sort'] ?? 'newest');

        // Danh sách các kiểu sắp xếp hợp lệ mà chương trình cho phép.
        $allowedSorts = ['newest', 'price_asc', 'price_desc', 'name_asc'];

        // Nếu người dùng sửa URL thành giá trị lạ thì đưa về kiểu mặc định.
        if (!in_array($sort, $allowedSorts, true)) {
            $sort = 'newest';
        }

        // Client hiển thị 12 sản phẩm trên mỗi trang.
        $perPage = 12;
        $page = max(1, (int) ($_GET['page'] ?? 1));

        // Đếm tổng kết quả trước để giới hạn page hợp lệ.
        $totalProducts = $this->productModel->countAll(
            $keyword,
            $categoryId
        );
        $totalPages = max(1, (int) ceil($totalProducts / $perPage));
        $page = min($page, $totalPages);
        $offset = ($page - 1) * $perPage;

        // Chỉ lấy dữ liệu thuộc trang hiện tại.
        $products = $this->productModel->getAll(
            $keyword,
            $categoryId,
            $sort,
            $perPage,
            $offset
        );

        // Lấy danh mục để tạo các lựa chọn trong ô lọc.
        $categories = $this->productModel->getCategories();

        // Đổi tiêu đề trang khi người dùng đang tìm kiếm.
        $pageTitle = $keyword === '' ? 'Sản phẩm' : 'Kết quả tìm kiếm';

        // Thông tin phân trang được view dùng để tạo nút và phần mô tả.
        $fromProduct = $totalProducts === 0 ? 0 : $offset + 1;
        $toProduct = min($offset + $perPage, $totalProducts);

        // Chọn file view hiển thị danh sách sản phẩm.
        $contentView = PATH_VIEW . 'client/products/index.php';

        // Nạp layout chung của Client.
        require PATH_VIEW . 'client/layouts/master.php';
    }

    // Route tìm kiếm dùng lại hoàn toàn xử lý của trang danh sách.
    public function search(): void
    {
        // Gọi index() để tránh viết lặp code tìm kiếm và lọc.
        $this->index();
    }

    // Hiển thị thông tin chi tiết của một sản phẩm.
    public function detail(): void
    {
        // Lấy id sản phẩm từ URL và không chấp nhận số âm.
        $id = max(0, (int) ($_GET['id'] ?? 0));

        // Tìm sản phẩm đang hoạt động theo id.
        $product = $this->productModel->find($id);

        // Nếu không có sản phẩm thì hiển thị trang lỗi 404.
        if ($product === null) {
            // Gửi mã trạng thái HTTP 404 cho trình duyệt.
            http_response_code(404);

            // Đặt tiêu đề cho trang lỗi.
            $pageTitle = 'Không tìm thấy sản phẩm';

            // Chọn view lỗi 404 làm nội dung chính.
            $contentView = PATH_VIEW . 'client/errors/404.php';

            // Nạp layout để trang lỗi vẫn có header và footer.
            require PATH_VIEW . 'client/layouts/master.php';

            // Dừng phương thức để không chạy tiếp phần chi tiết bên dưới.
            return;
        }

        // Lấy các tổ hợp màu-size do Admin quản lý.
        $variants = $this->productModel->getVariants(
            (int) $product['product_id']
        );

        // Lấy 4 sản phẩm cùng danh mục và loại trừ sản phẩm đang xem.
        $relatedProducts = $this->productModel->getRelated(
            (int) $product['category_id'],
            (int) $product['product_id']
        );

        $reviews = $this->reviewModel->getVisibleByProduct(
            (int) $product['product_id']
        );
        $reviewSummary = $this->reviewModel->getSummary(
            (int) $product['product_id']
        );

        // Dùng tên sản phẩm làm tiêu đề trang.
        $pageTitle = $product['product_name'];

        // Chọn view chi tiết sản phẩm.
        $contentView = PATH_VIEW . 'client/products/detail.php';

        // Nạp layout chung để hiển thị trang.
        require PATH_VIEW . 'client/layouts/master.php';
    }
}
