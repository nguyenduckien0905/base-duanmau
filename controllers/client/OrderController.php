<?php

/**
 * Controller thanh toán và lịch sử đơn hàng của khách hàng.
 */
class ClientOrderController
{
    // Model tạo đơn và đọc các đơn đã đặt.
    private ClientOrderModel $orderModel;

    // Model sản phẩm dùng kiểm tra lại giỏ trước khi thanh toán.
    private ClientProductModel $productModel;

    // Model tài khoản dùng kiểm tra khách có bị Admin khóa hay không.
    private ClientAuthModel $authModel;

    // Khởi tạo các model cần dùng.
    public function __construct()
    {
        $this->orderModel = new ClientOrderModel();
        $this->productModel = new ClientProductModel();
        $this->authModel = new ClientAuthModel();
    }

    /**
     * Hiển thị form và xử lý thanh toán.
     */
    public function checkout(): void
    {
        // Thanh toán chỉ dành cho khách đã đăng nhập.
        requireClient('checkout');

        // Kiểm tra lại trạng thái tài khoản trong database.
        $this->ensureActiveClient();

        // Lấy giỏ từ session.
        $cart = $_SESSION['cart'] ?? [];

        // Không cho mở checkout khi giỏ rỗng.
        if (empty($cart)) {
            setFlash('error', 'Giỏ hàng đang trống.');
            redirect('cart');
        }

        // Đồng bộ giỏ và tính tạm tính từ giá hiện tại trong database.
        [$cart, $subtotal] = $this->validateCart($cart);

        // Nếu tất cả sản phẩm bị loại trong lúc đồng bộ thì quay về giỏ.
        if (empty($cart)) {
            setFlash('error', 'Không còn sản phẩm hợp lệ để thanh toán.');
            redirect('cart');
        }

        // Miễn phí giao hàng cho đơn từ 500.000 đồng.
        $shippingFee = $subtotal >= 500000 ? 0 : 30000;

        // Bản này chưa áp dụng coupon phía Client nên giảm giá bằng 0.
        $discount = 0;

        // Tổng tiền cuối cùng của đơn.
        $total = $subtotal + $shippingFee - $discount;

        // Lấy tài khoản hiện tại để điền sẵn thông tin người nhận.
        $client = currentClient();

        // Giữ dữ liệu form nếu thanh toán xảy ra lỗi.
        $form = [
            'receiver_name' => trim((string) ($_POST['receiver_name'] ?? $client['fullname'])),
            'receiver_phone' => trim((string) ($_POST['receiver_phone'] ?? $client['phone'])),
            'shipping_address' => trim((string) ($_POST['shipping_address'] ?? $client['address'])),
            'note' => trim((string) ($_POST['note'] ?? '')),
            'payment_method' => trim((string) ($_POST['payment_method'] ?? 'cod')),
        ];

        // Mảng lỗi gửi sang view.
        $errors = [];

        // Chỉ tạo đơn khi người dùng gửi form POST.
        if (isPost()) {
            verifyCsrf();

            // Kiểm tra các trường nhận hàng bắt buộc.
            if ($form['receiver_name'] === '') {
                $errors[] = 'Vui lòng nhập tên người nhận.';
            }

            if (!preg_match('/^[0-9+ .-]{9,15}$/', $form['receiver_phone'])) {
                $errors[] = 'Số điện thoại không hợp lệ.';
            }

            if ($form['shipping_address'] === '') {
                $errors[] = 'Vui lòng nhập địa chỉ nhận hàng.';
            }

            // Chỉ nhận hai phương thức mà giao diện cung cấp.
            if (!in_array($form['payment_method'], ['cod', 'bank_transfer'], true)) {
                $errors[] = 'Phương thức thanh toán không hợp lệ.';
            }

            // Chỉ gọi model khi dữ liệu hợp lệ.
            if (empty($errors)) {
                // Chuẩn bị dữ liệu đúng tên cột bảng orders.
                $orderData = [
                    'user_id' => (int) $client['user_id'],
                    'receiver_name' => $form['receiver_name'],
                    'receiver_phone' => $form['receiver_phone'],
                    'shipping_address' => $form['shipping_address'],
                    'note' => $form['note'],
                    'subtotal' => $subtotal,
                    'shipping_fee' => $shippingFee,
                    'discount' => $discount,
                    'total_price' => $total,
                    'status' => 'pending',
                    'payment_method' => $form['payment_method'],
                ];

                // Chuyển giỏ thành dữ liệu đúng cột bảng order_items.
                $items = array_map(
                    static fn(array $item): array => [
                        'product_id' => (int) $item['product_id'],
                        'product_name' => $item['product_name'],
                        'size' => $item['size'],
                        'color' => $item['color'],
                        'price' => (float) $item['price'],
                        'quantity' => (int) $item['quantity'],
                    ],
                    array_values($cart)
                );

                try {
                    // Tạo đơn, chi tiết, thanh toán và trừ kho.
                    $orderId = $this->orderModel->createOrder($orderData, $items);

                    // Chỉ xóa giỏ sau khi transaction tạo đơn thành công.
                    unset($_SESSION['cart']);

                    // Chuyển sang chi tiết đơn vừa đặt.
                    setFlash('success', 'Đặt hàng thành công.');
                    redirect('orders/show', ['id' => $orderId]);
                } catch (Throwable $exception) {
                    // Không in lỗi SQL nhạy cảm; chỉ báo nội dung an toàn cho người dùng.
                    $errors[] = $exception instanceof DomainException
                        ? $exception->getMessage()
                        : 'Không thể tạo đơn hàng. Vui lòng thử lại.';
                }
            }
        }

        // Chọn view thanh toán.
        $pageTitle = 'Thanh toán';
        $contentView = PATH_VIEW . 'client/cart/checkout.php';
        require PATH_VIEW_CLIENT_MAIN;
    }

    /**
     * Hiển thị lịch sử đơn của tài khoản đang đăng nhập.
     */
    public function history(): void
    {
        requireClient();
        $this->ensureActiveClient();

        // Chỉ lấy đơn có user_id của người đang đăng nhập.
        $orders = $this->orderModel->getByUser((int) currentClient()['user_id']);

        $pageTitle = 'Đơn hàng của tôi';
        $contentView = PATH_VIEW . 'client/orders/history.php';
        require PATH_VIEW_CLIENT_MAIN;
    }

    /**
     * Hiển thị chi tiết một đơn thuộc tài khoản hiện tại.
     */
    public function show(): void
    {
        requireClient();
        $this->ensureActiveClient();

        // Lấy id đơn từ URL.
        $orderId = max(0, (int) ($_GET['id'] ?? 0));

        // Model kiểm tra đồng thời order_id và user_id để chống xem đơn người khác.
        $order = $this->orderModel->findForUser(
            $orderId,
            (int) currentClient()['user_id']
        );

        // Không tìm thấy hoặc không có quyền thì trả trang 404.
        if (!$order) {
            http_response_code(404);
            $pageTitle = 'Không tìm thấy đơn hàng';
            $contentView = PATH_VIEW . 'client/errors/404.php';
            require PATH_VIEW_CLIENT_MAIN;
            return;
        }

        // Lấy các sản phẩm thuộc đơn.
        $items = $this->orderModel->getItems($orderId);

        $pageTitle = 'Đơn hàng #' . $orderId;
        $contentView = PATH_VIEW . 'client/orders/show.php';
        require PATH_VIEW_CLIENT_MAIN;
    }

    /**
     * Đọc lại sản phẩm, giới hạn tồn kho và tính tạm tính an toàn.
     */
    private function validateCart(array $cart): array
    {
        $subtotal = 0;

        foreach ($cart as $key => $item) {
            $product = $this->productModel->find((int) $item['product_id']);

            // Loại sản phẩm bị xóa, tắt hoặc hết hàng.
            if (!$product || (int) $product['stock'] <= 0) {
                unset($cart[$key]);
                continue;
            }

            // Cập nhật dữ liệu có thể đã được Admin thay đổi.
            $cart[$key]['product_name'] = $product['product_name'];
            $cart[$key]['image'] = $product['image'];
            $cart[$key]['price'] = (float) $product['price'];
            $cart[$key]['stock'] = (int) $product['stock'];
            $cart[$key]['quantity'] = min(
                (int) $item['quantity'],
                (int) $product['stock']
            );

            // Cộng thành tiền của dòng vào tạm tính.
            $subtotal += $cart[$key]['price'] * $cart[$key]['quantity'];
        }

        // Lưu lại giỏ đã đồng bộ.
        $_SESSION['cart'] = $cart;

        return [$cart, $subtotal];
    }

    /**
     * Ngăn tài khoản đã bị Admin khóa tiếp tục thanh toán bằng session cũ.
     */
    private function ensureActiveClient(): void
    {
        // Tìm lại tài khoản khách hàng theo user_id trong session.
        $user = $this->authModel->find((int) currentClient()['user_id']);

        // Xóa phiên và bắt đăng nhập lại nếu tài khoản không còn hoạt động.
        if (!$user || (int) $user['status'] !== 1) {
            unset($_SESSION['client']);
            setFlash('error', 'Tài khoản không tồn tại hoặc đã bị khóa.');
            redirect('login');
        }
    }
}
