<?php

/**
 * Model tạo và đọc đơn hàng phía Client.
 * Các bảng orders, order_items và payments cũng được AdminOrderController đọc.
 */
class ClientOrderModel extends BaseModel
{
    /**
     * Tạo đơn hàng, chi tiết đơn và thanh toán trong cùng một transaction.
     */
    public function createOrder(array $order, array $items): int
    {
        // Lấy phương thức thanh toán ra riêng vì cột này thuộc bảng payments.
        $paymentMethod = $order['payment_method'];

        // Xóa phần tử thừa để PDO chỉ nhận placeholder có trong INSERT orders.
        unset($order['payment_method']);

        // Transaction bảo đảm không tạo đơn thiếu sản phẩm hoặc thiếu thanh toán.
        $this->pdo->beginTransaction();

        try {
            // Chuẩn bị câu lệnh thêm thông tin chung của đơn hàng.
            $statement = $this->pdo->prepare(
                'INSERT INTO orders (
                    user_id, receiver_name, receiver_phone, shipping_address,
                    note, subtotal, shipping_fee, discount, total_price, status
                 ) VALUES (
                    :user_id, :receiver_name, :receiver_phone, :shipping_address,
                    :note, :subtotal, :shipping_fee, :discount, :total_price, :status
                 )'
            );

            // Gửi dữ liệu đơn hàng vào câu SQL bằng prepared statement.
            $statement->execute($order);

            // Lấy id của đơn hàng vừa được tạo.
            $orderId = (int) $this->pdo->lastInsertId();

            // Chuẩn bị câu lệnh thêm từng sản phẩm vào bảng order_items.
            $itemStatement = $this->pdo->prepare(
                'INSERT INTO order_items (
                    order_id, product_id, product_name,
                    size, color, price, quantity
                 ) VALUES (
                    :order_id, :product_id, :product_name,
                    :size, :color, :price, :quantity
                 )'
            );

            // Lặp giỏ hàng để lưu ảnh chụp tên, giá, phân loại tại lúc đặt hàng.
            foreach ($items as $item) {
                // Gắn order_id mới tạo vào từng dòng chi tiết.
                $item['order_id'] = $orderId;

                // Thêm dòng chi tiết đơn hàng.
                $itemStatement->execute($item);

                // Trừ tồn kho nhưng không bao giờ để stock nhỏ hơn 0.
                $stockStatement = $this->pdo->prepare(
                    'UPDATE products
                     SET stock = stock - :quantity
                     WHERE product_id = :product_id
                       AND stock >= :quantity'
                );

                // Chạy cập nhật tồn kho theo số lượng khách đã mua.
                $stockStatement->execute([
                    'quantity' => $item['quantity'],
                    'product_id' => $item['product_id'],
                ]);

                // Nếu tồn kho thay đổi 0 dòng thì dữ liệu giỏ đã không còn hợp lệ.
                if ($stockStatement->rowCount() !== 1) {
                    throw new DomainException(
                        'Sản phẩm “' . $item['product_name'] . '” không đủ tồn kho.'
                    );
                }
            }

            // Tạo bản ghi thanh toán để Admin hiển thị phương thức và trạng thái.
            $paymentStatement = $this->pdo->prepare(
                'INSERT INTO payments (order_id, method, status, transaction_id)
                 VALUES (:order_id, :method, :status, NULL)'
            );

            // COD và chuyển khoản đều bắt đầu ở trạng thái chưa thanh toán.
            $paymentStatement->execute([
                'order_id' => $orderId,
                'method' => $paymentMethod,
                'status' => 'unpaid',
            ]);

            // Xác nhận tất cả thay đổi khi không có lỗi.
            $this->pdo->commit();

            // Trả id để controller chuyển sang trang đặt hàng thành công.
            return $orderId;
        } catch (Throwable $exception) {
            // Hoàn tác toàn bộ INSERT và UPDATE khi có bất kỳ lỗi nào.
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            // Ném lỗi ra controller để hiển thị thông báo.
            throw $exception;
        }
    }

    /**
     * Lấy lịch sử đơn của đúng khách hàng đang đăng nhập.
     */
    public function getByUser(int $userId): array
    {
        return $this->all(
            'SELECT orders.*, payments.method AS payment_method,
                    payments.status AS payment_status
             FROM orders
             LEFT JOIN payments ON payments.order_id = orders.order_id
             WHERE orders.user_id = :user_id
             ORDER BY orders.created_at DESC',
            ['user_id' => $userId]
        );
    }

    /**
     * Lấy một đơn hàng và kiểm tra đơn đó thuộc đúng khách hàng.
     */
    public function findForUser(int $orderId, int $userId): ?array
    {
        return $this->first(
            'SELECT orders.*, payments.method AS payment_method,
                    payments.status AS payment_status
             FROM orders
             LEFT JOIN payments ON payments.order_id = orders.order_id
             WHERE orders.order_id = :order_id
               AND orders.user_id = :user_id',
            ['order_id' => $orderId, 'user_id' => $userId]
        );
    }

    /**
     * Lấy sản phẩm thuộc một đơn hàng.
     */
    public function getItems(int $orderId): array
    {
        return $this->all(
            'SELECT * FROM order_items WHERE order_id = :order_id',
            ['order_id' => $orderId]
        );
    }
}
