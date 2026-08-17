<?php

/**
 * Controller quản lý giỏ hàng bằng session.
 *
 * Mỗi dòng giỏ được định danh bằng variant_id để màu, size và tồn kho luôn là
 * cùng một tổ hợp mà Admin đã tạo.
 */
class ClientCartController
{
    private ClientProductModel $productModel;

    public function __construct()
    {
        $this->productModel = new ClientProductModel();
    }

    /**
     * Hiển thị giỏ hàng.
     */
    public function index(): void
    {
        $cart = $this->syncCart();
        $subtotal = $this->subtotal($cart);

        $pageTitle = 'Giỏ hàng';
        $contentView = PATH_VIEW . 'client/cart/index.php';
        require PATH_VIEW . 'client/layouts/master.php';
    }

    /**
     * Thêm đúng một biến thể vào giỏ.
     */
    public function add(): void
    {
        if (!isPost()) {
            abort404();
            return;
        }

        verifyCsrf();

        $productId = max(0, (int) ($_POST['product_id'] ?? 0));
        $variantId = max(0, (int) ($_POST['variant_id'] ?? 0));
        $quantity = max(1, (int) ($_POST['quantity'] ?? 1));
        $selectedColor = trim(
            (string) ($_POST['selected_color'] ?? '')
        );

        // Model kiểm tra đồng thời sản phẩm, biến thể và trạng thái đang bán.
        $variant = $this->productModel->findPurchasableVariant(
            $variantId,
            $productId
        );

        if (!$variant) {
            setFlash(
                'error',
                'Phân loại sản phẩm không tồn tại hoặc đã ngừng bán.'
            );
            redirect('products/detail', ['id' => $productId]);
        }

        // Không để giao diện bị sửa thành màu không thuộc variant đã gửi.
        if ($selectedColor === '' || $selectedColor !== $variant['color']) {
            setFlash('error', 'Màu sắc và kích thước không khớp.');
            redirect('products/detail', ['id' => $productId]);
        }

        if ((int) $variant['stock'] <= 0) {
            setFlash('error', 'Phân loại bạn chọn đã hết hàng.');
            redirect('products/detail', ['id' => $productId]);
        }

        if ($quantity > (int) $variant['stock']) {
            setFlash(
                'error',
                'Phân loại bạn chọn chỉ còn '
                . (int) $variant['stock']
                . ' sản phẩm.'
            );
            redirect('products/detail', ['id' => $productId]);
        }

        // Chuẩn hóa giỏ cũ trước khi cộng thêm sản phẩm mới.
        $cart = $this->syncCart();
        $key = $this->cartKey($variantId);

        $newQuantity = (int) ($cart[$key]['quantity'] ?? 0) + $quantity;
        $newQuantity = min($newQuantity, (int) $variant['stock']);

        $cart[$key] = $this->cartItem($variant, $newQuantity);
        $_SESSION['cart'] = $cart;

        setFlash('success', 'Đã thêm đúng màu và kích thước vào giỏ hàng.');
        redirect('cart');
    }

    /**
     * Cập nhật số lượng tất cả dòng giỏ.
     */
    public function update(): void
    {
        if (!isPost()) {
            abort404();
            return;
        }

        verifyCsrf();

        $quantities = $_POST['quantities'] ?? [];
        $cart = $this->syncCart();

        foreach ($cart as $key => $item) {
            $quantity = max(
                0,
                (int) ($quantities[$key] ?? $item['quantity'])
            );

            if ($quantity === 0) {
                unset($cart[$key]);
                continue;
            }

            $cart[$key]['quantity'] = min(
                $quantity,
                (int) $item['stock']
            );
        }

        $_SESSION['cart'] = $cart;
        setFlash('success', 'Đã cập nhật giỏ hàng.');
        redirect('cart');
    }

    /**
     * Xóa một dòng biến thể khỏi giỏ.
     */
    public function remove(): void
    {
        if (!isPost()) {
            abort404();
            return;
        }

        verifyCsrf();

        $key = (string) ($_POST['key'] ?? '');
        unset($_SESSION['cart'][$key]);

        setFlash('success', 'Đã xóa sản phẩm khỏi giỏ.');
        redirect('cart');
    }

    /**
     * Đồng bộ session với dữ liệu Admin hiện tại.
     *
     * Hàm cũng chuyển giỏ cũ dạng product|size|color sang variant_id nếu tìm
     * được một tổ hợp khớp hoàn toàn.
     */
    private function syncCart(): array
    {
        $oldCart = $_SESSION['cart'] ?? [];
        $syncedCart = [];

        if (!is_array($oldCart)) {
            $_SESSION['cart'] = [];
            return [];
        }

        foreach ($oldCart as $item) {
            if (!is_array($item)) {
                continue;
            }

            $productId = max(0, (int) ($item['product_id'] ?? 0));
            $variantId = max(0, (int) ($item['variant_id'] ?? 0));

            // Nâng cấp im lặng dòng giỏ được tạo bởi Client phiên bản cũ.
            if ($variantId === 0 && $productId > 0) {
                $legacyVariant = $this->productModel->findVariantByAttributes(
                    $productId,
                    trim((string) ($item['color'] ?? '')),
                    trim((string) ($item['size'] ?? ''))
                );

                $variantId = (int) ($legacyVariant['variant_id'] ?? 0);
            }

            if ($variantId === 0) {
                continue;
            }

            $variant = $this->productModel->findPurchasableVariant(
                $variantId,
                $productId
            );

            if (!$variant || (int) $variant['stock'] <= 0) {
                continue;
            }

            $key = $this->cartKey($variantId);
            $quantity = max(1, (int) ($item['quantity'] ?? 1));

            // Nếu giỏ cũ có hai dòng trùng biến thể thì gộp lại an toàn.
            $quantity += (int) ($syncedCart[$key]['quantity'] ?? 0);
            $quantity = min($quantity, (int) $variant['stock']);

            $syncedCart[$key] = $this->cartItem($variant, $quantity);
        }

        $_SESSION['cart'] = $syncedCart;
        return $syncedCart;
    }

    /**
     * Tạo dữ liệu session từ bản ghi database, không nhận giá từ trình duyệt.
     */
    private function cartItem(array $variant, int $quantity): array
    {
        return [
            'variant_id' => (int) $variant['variant_id'],
            'product_id' => (int) $variant['product_id'],
            'product_name' => $variant['product_name'],
            'image' => $variant['image'],
            'price' => (float) $variant['price'],
            'size' => $variant['size'],
            'color' => $variant['color'],
            'stock' => (int) $variant['stock'],
            'quantity' => $quantity,
        ];
    }

    /**
     * Khóa session không chứa dữ liệu người dùng nhập tay.
     */
    private function cartKey(int $variantId): string
    {
        return 'variant_' . $variantId;
    }

    /**
     * Tính tạm tính của giỏ.
     */
    private function subtotal(array $cart): float
    {
        $subtotal = 0;

        foreach ($cart as $item) {
            $subtotal += (float) $item['price']
                * (int) $item['quantity'];
        }

        return $subtotal;
    }
}
