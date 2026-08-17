<?php

/**
 * Model quản lý đơn hàng, thanh toán và hoàn kho khi hủy đơn.
 */
class OrderModel extends BaseModel
{
    /**
     * Lấy danh sách đơn hàng theo trạng thái hoặc từ khóa.
     */
    public function getAll(string $status = '', string $keyword = ''): array
    {
        $sql = 'SELECT orders.*, users.fullname, users.email,
                       payments.method AS payment_method,
                       payments.status AS payment_status,
                       payments.proof_image
                FROM orders
                INNER JOIN users ON users.user_id = orders.user_id
                LEFT JOIN payments ON payments.order_id = orders.order_id
                WHERE 1 = 1';
        $params = [];

        if ($status !== '') {
            $sql .= ' AND orders.status = :status';
            $params['status'] = $status;
        }

        if ($keyword !== '') {
            $sql .= ' AND (
                CAST(orders.order_id AS CHAR) LIKE :keyword_order
                OR orders.receiver_name LIKE :keyword_name
                OR orders.receiver_phone LIKE :keyword_phone
            )';
            $params['keyword_order'] = '%' . $keyword . '%';
            $params['keyword_name'] = '%' . $keyword . '%';
            $params['keyword_phone'] = '%' . $keyword . '%';
        }

        $sql .= ' ORDER BY orders.created_at DESC';
        return $this->all($sql, $params);
    }

    /**
     * Lấy thông tin chung, coupon và thanh toán của một đơn.
     */
    public function find(int $id): ?array
    {
        return $this->first(
            'SELECT orders.*, users.fullname, users.email,
                    coupons.code AS coupon_code,
                    payments.method AS payment_method,
                    payments.status AS payment_status,
                    payments.transaction_id,
                    payments.proof_image,
                    payments.proof_uploaded_at,
                    payments.verified_at,
                    payments.admin_note,
                    payments.paid_at
             FROM orders
             INNER JOIN users ON users.user_id = orders.user_id
             LEFT JOIN coupons ON coupons.coupon_id = orders.coupon_id
             LEFT JOIN payments ON payments.order_id = orders.order_id
             WHERE orders.order_id = :id',
            ['id' => $id]
        );
    }

    /**
     * Lấy các sản phẩm của một đơn hàng.
     */
    public function getItems(int $orderId): array
    {
        return $this->all(
            'SELECT *
             FROM order_items
             WHERE order_id = :order_id
             ORDER BY order_item_id ASC',
            ['order_id' => $orderId]
        );
    }

    /**
     * Cập nhật trạng thái; nếu hủy thì hoàn kho và hoàn lượt coupon.
     */
    public function updateStatus(int $id, string $status): int
    {
        $this->pdo->beginTransaction();

        try {
            $orderStatement = $this->pdo->prepare(
                'SELECT order_id, coupon_id, status
                 FROM orders
                 WHERE order_id = :id
                 FOR UPDATE'
            );
            $orderStatement->execute(['id' => $id]);
            $order = $orderStatement->fetch();

            if ($order === false) {
                throw new DomainException('Đơn hàng không tồn tại.');
            }

            if ($status === 'cancelled') {
                $items = $this->getItems($id);
                $restoreStatement = $this->pdo->prepare(
                    'UPDATE product_variants
                     SET stock = stock + :quantity
                     WHERE variant_id = :variant_id'
                );
                $affectedProductIds = [];

                foreach ($items as $item) {
                    $variantId = (int) ($item['variant_id'] ?? 0);
                    $productId = (int) ($item['product_id'] ?? 0);

                    if ($variantId > 0) {
                        $restoreStatement->execute([
                            'quantity' => (int) $item['quantity'],
                            'variant_id' => $variantId,
                        ]);
                    }

                    if ($productId > 0) {
                        $affectedProductIds[$productId] = true;
                    }
                }

                $syncStock = $this->pdo->prepare(
                    'UPDATE products
                     SET stock = (
                        SELECT COALESCE(SUM(product_variants.stock), 0)
                        FROM product_variants
                        WHERE product_variants.product_id = products.product_id
                          AND product_variants.status = 1
                     )
                     WHERE product_id = :product_id'
                );

                foreach (array_keys($affectedProductIds) as $productId) {
                    $syncStock->execute(['product_id' => $productId]);
                }

                if (!empty($order['coupon_id'])) {
                    $this->execute(
                        'UPDATE coupons
                         SET quantity = quantity + 1
                         WHERE coupon_id = :coupon_id',
                        ['coupon_id' => (int) $order['coupon_id']]
                    );
                }
            }

            if ($status === 'delivered') {
                $updateSql = 'UPDATE orders
                              SET status = :status,
                                  delivered_at = CURRENT_TIMESTAMP
                              WHERE order_id = :id';
            } else {
                $updateSql = 'UPDATE orders
                              SET status = :status
                              WHERE order_id = :id';
            }

            $updateStatement = $this->pdo->prepare($updateSql);
            $updateStatement->execute([
                'status' => $status,
                'id' => $id,
            ]);
            $changedRows = $updateStatement->rowCount();

            $this->pdo->commit();
            return $changedRows;
        } catch (Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $exception;
        }
    }

    /**
     * Nhân viên duyệt hoặc từ chối minh chứng thanh toán.
     */
    public function updatePaymentStatus(
        int $orderId,
        string $status,
        string $adminNote
    ): int {
        return $this->execute(
            'UPDATE payments
             SET status = :status,
                 admin_note = :admin_note,
                 verified_at = CASE
                    WHEN :status_for_verified = \'pending\' THEN NULL
                    ELSE CURRENT_TIMESTAMP
                 END,
                 paid_at = CASE
                    WHEN :status_for_paid = \'paid\'
                        THEN COALESCE(paid_at, CURRENT_TIMESTAMP)
                    ELSE NULL
                 END
             WHERE order_id = :order_id',
            [
                'status' => $status,
                'admin_note' => $adminNote === '' ? null : $adminNote,
                'status_for_verified' => $status,
                'status_for_paid' => $status,
                'order_id' => $orderId,
            ]
        );
    }
}
