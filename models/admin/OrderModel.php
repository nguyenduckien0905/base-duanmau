<?php

/**
 * Model thao tác với đơn hàng và chi tiết đơn hàng.
 */
class OrderModel extends BaseModel
{
    /**
     * Lấy danh sách đơn hàng theo trạng thái hoặc từ khóa.
     */
    public function getAll(string $status = '', string $keyword = ''): array
    {
        // Lấy thêm tên khách hàng và trạng thái thanh toán.
        $sql = 'SELECT orders.*, users.fullname, users.email,
                       payments.method AS payment_method,
                       payments.status AS payment_status
                FROM orders
                INNER JOIN users ON users.user_id = orders.user_id
                LEFT JOIN payments ON payments.order_id = orders.order_id
                WHERE 1 = 1';

        // Mảng tham số cho PDO.
        $params = [];

        // Lọc theo trạng thái nếu người dùng đã chọn.
        if ($status !== '') {
            $sql .= ' AND orders.status = :status';
            $params['status'] = $status;
        }

        // Tìm theo mã đơn, tên hoặc số điện thoại người nhận.
        if ($keyword !== '') {
            $sql .= ' AND (
                CAST(orders.order_id AS CHAR) LIKE :keyword_order
                OR orders.receiver_name LIKE :keyword_name
                OR orders.receiver_phone LIKE :keyword_phone
            )';

            // Native prepared statement cần tên placeholder khác nhau.
            $params['keyword_order'] = '%' . $keyword . '%';
            $params['keyword_name'] = '%' . $keyword . '%';
            $params['keyword_phone'] = '%' . $keyword . '%';
        }

        // Đơn mới được xếp lên đầu.
        $sql .= ' ORDER BY orders.created_at DESC';

        return $this->all($sql, $params);
    }

    /**
     * Lấy thông tin chung của một đơn hàng.
     */
    public function find(int $id): ?array
    {
        $sql = 'SELECT orders.*, users.fullname, users.email,
                       payments.method AS payment_method,
                       payments.status AS payment_status,
                       payments.transaction_id
                FROM orders
                INNER JOIN users ON users.user_id = orders.user_id
                LEFT JOIN payments ON payments.order_id = orders.order_id
                WHERE orders.order_id = :id';

        return $this->first($sql, ['id' => $id]);
    }

    /**
     * Lấy các sản phẩm của một đơn hàng.
     */
    public function getItems(int $orderId): array
    {
        return $this->all(
            'SELECT * FROM order_items WHERE order_id = :order_id',
            ['order_id' => $orderId]
        );
    }

    /**
     * Cập nhật trạng thái đơn hàng.
     */
    public function updateStatus(int $id, string $status): int
    {
        return $this->execute(
            'UPDATE orders SET status = :status WHERE order_id = :id',
            [
                'status' => $status,
                'id' => $id,
            ]
        );
    }
}
