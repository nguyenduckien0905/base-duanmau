<?php

class ClientOrderModel extends BaseModel
{
    public function previewCoupon(string $code, float $subtotal): array
    {
        $coupon = $this->first(
            'SELECT * FROM coupons WHERE UPPER(code) = :code LIMIT 1',
            ['code' => strtoupper(trim($code))]
        );
        return $this->validateAndCalculateCoupon($coupon, $subtotal);
    }

    public function createOrder(array $order, array $items): int
    {
        $paymentMethod = (string) ($order['payment_method'] ?? 'cod');
        $paymentProof = $order['payment_proof'] ?? null;
        $couponCode = strtoupper(trim((string) ($order['coupon_code'] ?? '')));
        unset($order['payment_method'], $order['payment_proof'], $order['coupon_code']);

        if ($paymentMethod === 'bank_transfer' && empty($paymentProof)) {
            throw new DomainException('Vui lòng tải ảnh minh chứng khi thanh toán chuyển khoản.');
        }

        $subtotal = 0.0;
        foreach ($items as $item) {
            $subtotal += (float) $item['price'] * (int) $item['quantity'];
        }
        $shippingFee = $subtotal >= 500000 ? 0.0 : 30000.0;

        $this->pdo->beginTransaction();

        try {
            $couponId = null;
            $discount = 0.0;

            if ($couponCode !== '') {
                $couponStatement = $this->pdo->prepare(
                    'SELECT * FROM coupons
                     WHERE UPPER(code) = :code LIMIT 1 FOR UPDATE'
                );
                $couponStatement->execute(['code' => $couponCode]);
                $coupon = $couponStatement->fetch();
                $coupon = $coupon === false ? null : $coupon;
                $couponResult = $this->validateAndCalculateCoupon($coupon, $subtotal);
                $couponId = (int) $couponResult['coupon']['coupon_id'];
                $discount = (float) $couponResult['discount'];

                $couponUpdate = $this->pdo->prepare(
                    'UPDATE coupons SET quantity = quantity - 1
                     WHERE coupon_id = :coupon_id AND quantity > 0'
                );
                $couponUpdate->execute(['coupon_id' => $couponId]);
                if ($couponUpdate->rowCount() !== 1) {
                    throw new DomainException('Mã giảm giá đã hết lượt sử dụng.');
                }
            }

            $order['coupon_id'] = $couponId;
            $order['subtotal'] = $subtotal;
            $order['shipping_fee'] = $shippingFee;
            $order['discount'] = $discount;
            $order['total_price'] = max(0, $subtotal + $shippingFee - $discount);
            $order['status'] = 'pending';

            $orderStatement = $this->pdo->prepare(
                'INSERT INTO orders (
                    user_id, coupon_id, receiver_name, receiver_phone,
                    shipping_address, note, subtotal, shipping_fee,
                    discount, total_price, status
                 ) VALUES (
                    :user_id, :coupon_id, :receiver_name, :receiver_phone,
                    :shipping_address, :note, :subtotal, :shipping_fee,
                    :discount, :total_price, :status
                 )'
            );
            $orderStatement->execute($order);
            $orderId = (int) $this->pdo->lastInsertId();

            $stockStatement = $this->pdo->prepare(
                'UPDATE product_variants
                 INNER JOIN products ON products.product_id = product_variants.product_id
                 SET product_variants.stock = product_variants.stock - :quantity_decrement
                 WHERE product_variants.variant_id = :variant_id
                   AND product_variants.product_id = :product_id
                   AND product_variants.status = 1
                   AND products.status = 1
                   AND product_variants.stock >= :quantity_check'
            );
            $itemStatement = $this->pdo->prepare(
                'INSERT INTO order_items (
                    order_id, product_id, variant_id, product_name,
                    size, color, price, quantity
                 ) VALUES (
                    :order_id, :product_id, :variant_id, :product_name,
                    :size, :color, :price, :quantity
                 )'
            );
            $affectedProductIds = [];

            foreach ($items as $item) {
                $stockStatement->execute([
                    'quantity_decrement' => $item['quantity'],
                    'quantity_check' => $item['quantity'],
                    'variant_id' => $item['variant_id'],
                    'product_id' => $item['product_id'],
                ]);
                if ($stockStatement->rowCount() !== 1) {
                    throw new DomainException(
                        'Phân loại ' . $item['color'] . ' - ' . $item['size']
                        . ' của sản phẩm “' . $item['product_name'] . '” không đủ tồn kho.'
                    );
                }
                $item['order_id'] = $orderId;
                $itemStatement->execute($item);
                $affectedProductIds[(int) $item['product_id']] = true;
            }

            $totalStockStatement = $this->pdo->prepare(
                'UPDATE products SET stock = (
                    SELECT COALESCE(SUM(product_variants.stock), 0)
                    FROM product_variants
                    WHERE product_variants.product_id = products.product_id
                      AND product_variants.status = 1
                 ) WHERE product_id = :product_id'
            );
            foreach (array_keys($affectedProductIds) as $productId) {
                $totalStockStatement->execute(['product_id' => $productId]);
            }

            $paymentStatement = $this->pdo->prepare(
                'INSERT INTO payments (
                    order_id, method, transaction_id, proof_image,
                    proof_uploaded_at, status
                 ) VALUES (
                    :order_id, :method, NULL, :proof_image,
                    :proof_uploaded_at, :status
                 )'
            );
            $paymentStatement->execute([
                'order_id' => $orderId,
                'method' => $paymentMethod,
                'proof_image' => $paymentMethod === 'bank_transfer' ? $paymentProof : null,
                'proof_uploaded_at' => $paymentMethod === 'bank_transfer'
                    ? date('Y-m-d H:i:s') : null,
                'status' => 'pending',
            ]);

            $this->pdo->commit();
            return $orderId;
        } catch (Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $exception;
        }
    }

    public function getByUser(int $userId): array
    {
        return $this->all(
            'SELECT orders.*, coupons.code AS coupon_code,
                    payments.method AS payment_method,
                    payments.status AS payment_status
             FROM orders
             LEFT JOIN coupons ON coupons.coupon_id = orders.coupon_id
             LEFT JOIN payments ON payments.order_id = orders.order_id
             WHERE orders.user_id = :user_id
             ORDER BY orders.created_at DESC',
            ['user_id' => $userId]
        );
    }

    public function findForUser(int $orderId, int $userId): ?array
    {
        return $this->first(
            'SELECT orders.*, coupons.code AS coupon_code,
                    payments.method AS payment_method,
                    payments.status AS payment_status,
                    payments.proof_image, payments.proof_uploaded_at,
                    payments.admin_note
             FROM orders
             LEFT JOIN coupons ON coupons.coupon_id = orders.coupon_id
             LEFT JOIN payments ON payments.order_id = orders.order_id
             WHERE orders.order_id = :order_id AND orders.user_id = :user_id',
            ['order_id' => $orderId, 'user_id' => $userId]
        );
    }

    public function getItems(int $orderId, int $userId): array
    {
        return $this->all(
            'SELECT order_items.*, reviews.review_id,
                    reviews.rating AS review_rating,
                    reviews.comment AS review_comment
             FROM order_items
             LEFT JOIN reviews
                ON reviews.user_id = :user_id
               AND reviews.product_id = order_items.product_id
             WHERE order_items.order_id = :order_id
             ORDER BY order_items.order_item_id ASC',
            ['order_id' => $orderId, 'user_id' => $userId]
        );
    }

    public function confirmReceived(int $orderId, int $userId): void
    {
        $this->pdo->beginTransaction();
        try {
            $statement = $this->pdo->prepare(
                'UPDATE orders
                 SET status = :completed, completed_at = CURRENT_TIMESTAMP
                 WHERE order_id = :order_id AND user_id = :user_id
                   AND status = :delivered'
            );
            $statement->execute([
                'completed' => 'completed',
                'delivered' => 'delivered',
                'order_id' => $orderId,
                'user_id' => $userId,
            ]);
            if ($statement->rowCount() !== 1) {
                throw new DomainException(
                    'Đơn hàng chưa ở trạng thái đã giao hoặc đã được xác nhận.'
                );
            }

            $paymentStatement = $this->pdo->prepare(
                'UPDATE payments
                 SET status = :paid,
                     paid_at = COALESCE(paid_at, CURRENT_TIMESTAMP),
                     verified_at = COALESCE(verified_at, CURRENT_TIMESTAMP)
                 WHERE order_id = :order_id AND method = :cod'
            );
            $paymentStatement->execute([
                'paid' => 'paid',
                'order_id' => $orderId,
                'cod' => 'cod',
            ]);
            $this->pdo->commit();
        } catch (Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $exception;
        }
    }

    private function validateAndCalculateCoupon(?array $coupon, float $subtotal): array
    {
        if (!$coupon || (int) $coupon['status'] !== 1) {
            throw new DomainException('Mã giảm giá không tồn tại hoặc đã bị tắt.');
        }
        $now = time();
        if ($now < strtotime($coupon['start_date']) || $now > strtotime($coupon['end_date'])) {
            throw new DomainException('Mã giảm giá chưa bắt đầu hoặc đã hết hạn.');
        }
        if ((int) $coupon['quantity'] <= 0) {
            throw new DomainException('Mã giảm giá đã hết lượt sử dụng.');
        }
        if ($subtotal < (float) $coupon['min_order_value']) {
            throw new DomainException(
                'Đơn hàng cần tối thiểu ' . formatPrice($coupon['min_order_value'])
                . ' để dùng mã này.'
            );
        }

        if ($coupon['discount_type'] === 'percent') {
            $discount = $subtotal * (float) $coupon['discount_value'] / 100;
            if ($coupon['max_discount'] !== null) {
                $discount = min($discount, (float) $coupon['max_discount']);
            }
        } else {
            $discount = (float) $coupon['discount_value'];
        }

        return [
            'coupon' => $coupon,
            'discount' => min($subtotal, round($discount, 2)),
        ];
    }
}
