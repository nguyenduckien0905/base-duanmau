<?php

/**
 * Model lấy số liệu tổng quan cho Dashboard.
 */
class DashboardModel extends BaseModel
{
    /**
     * Lấy các con số thống kê chính.
     */
    public function getStatistics(): array
    {
        // Đếm tổng sản phẩm.
        $products = $this->first('SELECT COUNT(*) AS total FROM products');

        // Đếm tổng thành viên mua hàng.
        $members = $this->first('SELECT COUNT(*) AS total FROM users WHERE role_id = 3');

        // Đếm tổng đơn hàng.
        $orders = $this->first('SELECT COUNT(*) AS total FROM orders');

        // Tính doanh thu của các đơn đã hoàn thành.
        $revenue = $this->first(
            "SELECT COALESCE(SUM(total_price), 0) AS total
             FROM orders
             WHERE status = 'completed'"
        );

        // Gom kết quả thành một mảng dễ dùng ở view.
        return [
            'products' => (int) ($products['total'] ?? 0),
            'members' => (int) ($members['total'] ?? 0),
            'orders' => (int) ($orders['total'] ?? 0),
            'revenue' => (float) ($revenue['total'] ?? 0),
        ];
    }

    /**
     * Lấy năm đơn hàng mới nhất.
     */
    public function getLatestOrders(): array
    {
        // Join users để hiển thị tên người đặt hàng.
        $sql = 'SELECT orders.*, users.fullname
                FROM orders
                INNER JOIN users ON users.user_id = orders.user_id
                ORDER BY orders.created_at DESC
                LIMIT 5';

        // Trả danh sách cho dashboard.
        return $this->all($sql);
    }

    /**
     * Lấy các sản phẩm có tồn kho thấp.
     */
    public function getLowStockProducts(): array
    {
        // Sản phẩm còn từ 0 đến 10 được xem là sắp hết hàng.
        $sql = 'SELECT product_id, product_name, stock
                FROM products
                WHERE stock <= 10
                ORDER BY stock ASC, product_id DESC
                LIMIT 5';

        // Trả danh sách cảnh báo tồn kho.
        return $this->all($sql);
    }
}
