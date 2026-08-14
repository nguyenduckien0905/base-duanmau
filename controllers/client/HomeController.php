<?php

// File này nằm trong controllers/client vì chỉ xử lý giao diện người mua.
// Khai báo controller chịu trách nhiệm xử lý trang chủ phía Client.
class HomeController
{
    // Thuộc tính lưu đối tượng model để controller có thể lấy dữ liệu sản phẩm.
    private ClientProductModel $productModel;

    // Hàm khởi tạo tự động chạy khi gọi new HomeController().
    public function __construct()
    {
        // Tạo model một lần để sử dụng trong các phương thức của controller.
        $this->productModel = new ClientProductModel();
    }

    // Phương thức hiển thị trang chủ Client.
    public function index(): void
    {
        // Tiêu đề được layout master đưa vào thẻ <title>.
        $pageTitle = 'Trang chủ';

        // Lấy các danh mục đang hoạt động để hiển thị ở khu vực danh mục nổi bật.
        $categories = $this->productModel->getCategories();

        // Lấy tối đa 8 sản phẩm mới nhất đang được phép bán.
        $featuredProducts = $this->productModel->getFeatured(8);

        // Khai báo view nội dung sẽ được nhúng vào layout master.
        $contentView = PATH_VIEW . 'client/home/index.php';

        // Nạp layout chung; layout sẽ nạp header, contentView và footer.
        require PATH_VIEW . 'client/layouts/master.php';
    }
}
