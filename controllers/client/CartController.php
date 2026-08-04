<?php

/**
 * Controller quản lý giỏ hàng bằng session.
 */
class CartController
{
    // Model sản phẩm dùng để kiểm tra giá, trạng thái và tồn kho hiện tại.
    private ClientProductModel $productModel;

    // Khởi tạo model sản phẩm.
    public function __construct()
    {
        $this->productModel = new ClientProductModel();
    }

    /**
     * Hiển thị giỏ hàng.
     */
    public function index(): void
    {
        // Đồng bộ lại dữ liệu giỏ với database trước khi hiển thị.
        $cart = $this->syncCart();

        // Tính tổng tiền từ giỏ đã đồng bộ.
        $subtotal = $this->subtotal($cart);

        // Chọn view giỏ hàng.
        $pageTitle = 'Giỏ hàng';
        $contentView = PATH_VIEW . 'client/cart/index.php';
        require PATH_VIEW_CLIENT_MAIN;
    }

    /**
     * Thêm sản phẩm vào giỏ.
     */
    public function add(): void
    {
        // Chỉ nhận dữ liệu thêm giỏ qua POST.
        if (!isPost()) {
            abort404();
            return;
        }

        verifyCsrf();

        // Lấy dữ liệu sản phẩm và phân loại từ form chi tiết.
        $productId = max(0, (int) ($_POST['product_id'] ?? 0));
        $quantity = max(1, (int) ($_POST['quantity'] ?? 1));
        $size = trim((string) ($_POST['size'] ?? ''));
        $color = trim((string) ($_POST['color'] ?? ''));

        // Luôn đọc lại sản phẩm từ database, không tin giá gửi từ trình duyệt.
        $product = $this->productModel->find($productId);

        // Không thêm sản phẩm không tồn tại hoặc đã ngừng bán.
        if (!$product) {
            setFlash('error', 'Sản phẩm không tồn tại hoặc đã ngừng bán.');
            redirect('products');
        }

        // Không cho thêm quá số lượng còn trong kho.
        if ((int) $product['stock'] < $quantity) {
            setFlash('error', 'Số lượng sản phẩm trong kho không đủ.');
            redirect('products/detail', ['id' => $productId]);
        }

        // Ghép id, size và color thành khóa riêng cho từng phân loại.
        $key = $productId . '|' . $size . '|' . $color;

        // Lấy giỏ hiện tại hoặc tạo mảng rỗng.
        $cart = $_SESSION['cart'] ?? [];

        // Nếu phân loại đã có thì cộng thêm số lượng.
        $newQuantity = ($cart[$key]['quantity'] ?? 0) + $quantity;

        // Chặn tổng số lượng trong giỏ vượt quá tồn kho.
        $newQuantity = min($newQuantity, (int) $product['stock']);

        // Lưu dữ liệu cần thiết để hiển thị nhanh trong session.
        $cart[$key] = [
            'product_id' => (int) $product['product_id'],
            'product_name' => $product['product_name'],
            'image' => $product['image'],
            'price' => (float) $product['price'],
            'size' => $size,
            'color' => $color,
            'stock' => (int) $product['stock'],
            'quantity' => $newQuantity,
        ];

        // Ghi giỏ mới trở lại session.
        $_SESSION['cart'] = $cart;

        // Thông báo và chuyển đến trang giỏ hàng.
        setFlash('success', 'Đã thêm sản phẩm vào giỏ hàng.');
        redirect('cart');
    }

    /**
     * Cập nhật số lượng tất cả sản phẩm trong giỏ.
     */
    public function update(): void
    {
        if (!isPost()) {
            abort404();
            return;
        }

        verifyCsrf();

        // Lấy mảng quantity có key tương ứng với từng dòng giỏ.
        $quantities = $_POST['quantities'] ?? [];
        $cart = $_SESSION['cart'] ?? [];

        // Duyệt từng dòng giỏ hiện tại.
        foreach ($cart as $key => $item) {
            // Số lượng 0 được hiểu là xóa sản phẩm khỏi giỏ.
            $quantity = max(0, (int) ($quantities[$key] ?? $item['quantity']));

            if ($quantity === 0) {
                unset($cart[$key]);
                continue;
            }

            // Không cho số lượng vượt quá tồn kho đã biết.
            $cart[$key]['quantity'] = min($quantity, (int) $item['stock']);
        }

        // Lưu giỏ sau cập nhật.
        $_SESSION['cart'] = $cart;
        setFlash('success', 'Đã cập nhật giỏ hàng.');
        redirect('cart');
    }

    /**
     * Xóa một dòng sản phẩm khỏi giỏ.
     */
    public function remove(): void
    {
        if (!isPost()) {
            abort404();
            return;
        }

        verifyCsrf();

        // Key được gửi từ form xóa của đúng dòng sản phẩm.
        $key = (string) ($_POST['key'] ?? '');

        // Xóa key nếu nó tồn tại.
        unset($_SESSION['cart'][$key]);

        setFlash('success', 'Đã xóa sản phẩm khỏi giỏ.');
        redirect('cart');
    }

    /**
     * Đọc lại từng sản phẩm để giỏ luôn khớp dữ liệu Admin.
     */
    private function syncCart(): array
    {
        $cart = $_SESSION['cart'] ?? [];

        foreach ($cart as $key => $item) {
            $product = $this->productModel->find((int) $item['product_id']);

            // Admin xóa hoặc tắt sản phẩm thì loại sản phẩm khỏi giỏ.
            if (!$product || (int) $product['stock'] <= 0) {
                unset($cart[$key]);
                continue;
            }

            // Cập nhật lại tên, ảnh, giá và tồn kho mới nhất từ Admin.
            $cart[$key]['product_name'] = $product['product_name'];
            $cart[$key]['image'] = $product['image'];
            $cart[$key]['price'] = (float) $product['price'];
            $cart[$key]['stock'] = (int) $product['stock'];
            $cart[$key]['quantity'] = min(
                (int) $item['quantity'],
                (int) $product['stock']
            );
        }

        $_SESSION['cart'] = $cart;
        return $cart;
    }

    /**
     * Tính tạm tính của giỏ.
     */
    private function subtotal(array $cart): float
    {
        $subtotal = 0;

        foreach ($cart as $item) {
            $subtotal += (float) $item['price'] * (int) $item['quantity'];
        }

        return $subtotal;
    }
}
