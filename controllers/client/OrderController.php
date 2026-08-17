<?php

/**
 * Controller thanh toán, lịch sử và xác nhận nhận hàng của Client.
 */
class ClientOrderController
{
    private ClientOrderModel $orderModel;
    private ClientProductModel $productModel;
    private ClientAuthModel $authModel;

    public function __construct()
    {
        $this->orderModel = new ClientOrderModel();
        $this->productModel = new ClientProductModel();
        $this->authModel = new ClientAuthModel();
    }

    /** Hiển thị form, áp coupon và xử lý đặt hàng. */
    public function checkout(): void
    {
        requireClient('checkout');
        $this->ensureActiveClient();

        $cart = $_SESSION['cart'] ?? [];
        if (empty($cart)) {
            setFlash('error', 'Giỏ hàng đang trống.');
            redirect('cart');
        }

        [$cart, $subtotal] = $this->validateCart($cart);
        if (empty($cart)) {
            setFlash(
                'error',
                'Các phân loại trong giỏ đã hết hàng hoặc ngừng bán.'
            );
            redirect('cart');
        }

        $shippingFee = $subtotal >= 500000 ? 0 : 30000;
        $client = currentClient();
        $form = [
            'receiver_name' => trim(
                (string) ($_POST['receiver_name'] ?? $client['fullname'])
            ),
            'receiver_phone' => trim(
                (string) ($_POST['receiver_phone'] ?? $client['phone'])
            ),
            'shipping_address' => trim(
                (string) (
                    $_POST['shipping_address']
                    ?? ($client['address'] ?? '')
                )
            ),
            'note' => trim((string) ($_POST['note'] ?? '')),
            'payment_method' => trim(
                (string) ($_POST['payment_method'] ?? 'cod')
            ),
            'coupon_code' => strtoupper(
                trim((string) ($_POST['coupon_code'] ?? ''))
            ),
        ];

        $errors = [];
        $coupon = null;
        $discount = 0.0;

        if ($form['coupon_code'] !== '') {
            try {
                $couponResult = $this->orderModel->previewCoupon(
                    $form['coupon_code'],
                    $subtotal
                );
                $coupon = $couponResult['coupon'];
                $discount = (float) $couponResult['discount'];
            } catch (DomainException $exception) {
                $errors[] = $exception->getMessage();
            }
        }

        $total = max(0, $subtotal + $shippingFee - $discount);

        if (isPost()) {
            verifyCsrf();
            $intent = (string) ($_POST['intent'] ?? 'place_order');

            if ($intent === 'apply_coupon') {
                if ($form['coupon_code'] === '') {
                    $errors[] = 'Vui lòng nhập mã giảm giá.';
                }
            } elseif ($intent === 'place_order') {
                $this->validateCheckoutForm($form, $errors);
                $paymentProof = null;

                if (empty($errors) && $form['payment_method'] === 'bank_transfer') {
                    try {
                        $paymentProof = uploadPaymentProof('payment_proof');
                        if ($paymentProof === null) {
                            $errors[] = 'Vui lòng tải ảnh minh chứng chuyển khoản.';
                        }
                    } catch (RuntimeException $exception) {
                        $errors[] = $exception->getMessage();
                    }
                }

                if (empty($errors)) {
                    $orderData = [
                        'user_id' => (int) $client['user_id'],
                        'receiver_name' => $form['receiver_name'],
                        'receiver_phone' => $form['receiver_phone'],
                        'shipping_address' => $form['shipping_address'],
                        'note' => $form['note'],
                        'payment_method' => $form['payment_method'],
                        'payment_proof' => $paymentProof,
                        'coupon_code' => $form['coupon_code'],
                    ];

                    $items = array_map(
                        static fn(array $item): array => [
                            'product_id' => (int) $item['product_id'],
                            'variant_id' => (int) $item['variant_id'],
                            'product_name' => $item['product_name'],
                            'size' => $item['size'],
                            'color' => $item['color'],
                            'price' => (float) $item['price'],
                            'quantity' => (int) $item['quantity'],
                        ],
                        array_values($cart)
                    );

                    try {
                        $orderId = $this->orderModel->createOrder(
                            $orderData,
                            $items
                        );
                        unset($_SESSION['cart']);

                        $message = $form['payment_method'] === 'bank_transfer'
                            ? 'Đặt hàng thành công. Nhân viên sẽ kiểm tra '
                                . 'thanh toán và liên hệ với bạn.'
                            : 'Đặt hàng thành công. Nhân viên sẽ sớm xác nhận đơn.';

                        setFlash('success', $message);
                        redirect('orders/show', ['id' => $orderId]);
                    } catch (Throwable $exception) {
                        deletePaymentProof($paymentProof);
                        $errors[] = $exception instanceof DomainException
                            ? $exception->getMessage()
                            : 'Không thể tạo đơn hàng. Vui lòng thử lại.';
                    }
                }
            } else {
                $errors[] = 'Yêu cầu thanh toán không hợp lệ.';
            }
        }

        $pageTitle = 'Thanh toán';
        $contentView = PATH_VIEW . 'client/cart/checkout.php';
        require PATH_VIEW . 'client/layouts/master.php';
    }

    /** Hiển thị lịch sử đơn. */
    public function history(): void
    {
        requireClient();
        $this->ensureActiveClient();
        $orders = $this->orderModel->getByUser(
            (int) currentClient()['user_id']
        );
        $pageTitle = 'Đơn hàng của tôi';
        $contentView = PATH_VIEW . 'client/orders/history.php';
        require PATH_VIEW . 'client/layouts/master.php';
    }

    /** Hiển thị chi tiết đơn thuộc tài khoản hiện tại. */
    public function show(): void
    {
        requireClient();
        $this->ensureActiveClient();

        $userId = (int) currentClient()['user_id'];
        $orderId = max(0, (int) ($_GET['id'] ?? 0));
        $order = $this->orderModel->findForUser($orderId, $userId);

        if (!$order) {
            http_response_code(404);
            $pageTitle = 'Không tìm thấy đơn hàng';
            $contentView = PATH_VIEW . 'client/errors/404.php';
            require PATH_VIEW . 'client/layouts/master.php';
            return;
        }

        $items = $this->orderModel->getItems($orderId, $userId);
        $pageTitle = 'Đơn hàng #' . $orderId;
        $contentView = PATH_VIEW . 'client/orders/show.php';
        require PATH_VIEW . 'client/layouts/master.php';
    }

    /** Khách xác nhận đã nhận hàng sau khi Admin xác nhận đã giao. */
    public function confirmReceived(): void
    {
        requireClient();
        $this->ensureActiveClient();

        if (!isPost()) {
            abort404();
            return;
        }

        verifyCsrf();
        $orderId = max(0, (int) ($_GET['id'] ?? 0));

        try {
            $this->orderModel->confirmReceived(
                $orderId,
                (int) currentClient()['user_id']
            );
            setFlash(
                'success',
                'Cảm ơn bạn đã xác nhận nhận hàng. Bạn có thể đánh giá sản phẩm.'
            );
        } catch (DomainException $exception) {
            setFlash('error', $exception->getMessage());
        }

        redirect('orders/show', ['id' => $orderId]);
    }

    /** Kiểm tra thông tin người nhận và phương thức thanh toán. */
    private function validateCheckoutForm(array $form, array &$errors): void
    {
        if ($form['receiver_name'] === '') {
            $errors[] = 'Vui lòng nhập tên người nhận.';
        }
        if (!preg_match('/^[0-9+ .-]{9,15}$/', $form['receiver_phone'])) {
            $errors[] = 'Số điện thoại không hợp lệ.';
        }
        if ($form['shipping_address'] === '') {
            $errors[] = 'Vui lòng nhập địa chỉ nhận hàng.';
        }
        if (!in_array(
            $form['payment_method'],
            ['cod', 'bank_transfer'],
            true
        )) {
            $errors[] = 'Phương thức thanh toán không hợp lệ.';
        }
    }

    /** Đồng bộ lại giỏ từ product_variants trước khi tạo đơn. */
    private function validateCart(array $cart): array
    {
        $syncedCart = [];
        $subtotal = 0;

        foreach ($cart as $item) {
            if (!is_array($item)) {
                continue;
            }

            $productId = max(0, (int) ($item['product_id'] ?? 0));
            $variantId = max(0, (int) ($item['variant_id'] ?? 0));

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

            $key = 'variant_' . $variantId;
            $quantity = max(1, (int) ($item['quantity'] ?? 1));
            $quantity += (int) ($syncedCart[$key]['quantity'] ?? 0);
            $quantity = min($quantity, (int) $variant['stock']);

            $syncedCart[$key] = [
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

        foreach ($syncedCart as $item) {
            $subtotal += (float) $item['price'] * (int) $item['quantity'];
        }

        $_SESSION['cart'] = $syncedCart;
        return [$syncedCart, $subtotal];
    }

    /** Chặn tài khoản đã bị Admin khóa. */
    private function ensureActiveClient(): void
    {
        $user = $this->authModel->find((int) currentClient()['user_id']);
        if (!$user || (int) $user['status'] !== 1) {
            unset($_SESSION['client']);
            setFlash('error', 'Tài khoản không tồn tại hoặc đã bị khóa.');
            redirect('login');
        }
    }
}
