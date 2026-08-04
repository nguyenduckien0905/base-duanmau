<?php

/**
 * File được đặt tại models/client/ProductModel.php.
 * Tên class là ClientProductModel để không trùng với ProductModel của Admin.
 * Model Client chỉ đọc dữ liệu; việc thêm, sửa, xóa vẫn do Admin xử lý.
 */
class ClientProductModel extends BaseModel
{
    /**
     * Lấy danh sách sản phẩm cho trang Client.
     *
     * @param string $keyword Từ khóa tìm theo tên sản phẩm.
     * @param int $categoryId Mã danh mục cần lọc; 0 nghĩa là lấy tất cả.
     * @param string $sort Kiểu sắp xếp được chọn trên giao diện.
     */
    public function getAll(
        string $keyword = '',
        int $categoryId = 0,
        string $sort = 'newest'
    ): array {
        // SELECT products.* lấy toàn bộ cột của bảng products.
        // INNER JOIN categories lấy tên danh mục và chỉ nhận sản phẩm có danh mục.
        // LEFT JOIN brands vẫn nhận sản phẩm dù sản phẩm chưa có thương hiệu.
        // status = 1 bảo đảm Client chỉ thấy sản phẩm được phép bán.
        $sql = 'SELECT products.*, categories.name AS category_name,
                       brands.brand_name
                FROM products
                INNER JOIN categories ON categories.category_id = products.category_id
                LEFT JOIN brands ON brands.brand_id = products.brand_id
                WHERE products.status = 1';

        // Mảng chứa các giá trị sẽ bind vào placeholder của câu SQL.
        $params = [];

        // Chỉ thêm điều kiện tìm tên khi người dùng có nhập từ khóa.
        if ($keyword !== '') {
            // LIKE kết hợp hai dấu % cho phép tìm từ khóa ở bất kỳ vị trí nào.
            $sql .= ' AND products.product_name LIKE :keyword';

            // Không nối trực tiếp dữ liệu vào SQL để hạn chế SQL Injection.
            $params['keyword'] = '%' . $keyword . '%';
        }

        // categoryId bằng 0 nghĩa là người dùng chọn "Tất cả danh mục".
        if ($categoryId > 0) {
            // Thêm điều kiện lọc đúng category_id được chọn.
            $sql .= ' AND products.category_id = :category_id';

            // Gán dữ liệu cho placeholder :category_id.
            $params['category_id'] = $categoryId;
        }

        // match chọn chính xác đoạn ORDER BY tương ứng với lựa chọn sắp xếp.
        $sql .= match ($sort) {
            // Giá từ thấp đến cao.
            'price_asc' => ' ORDER BY products.price ASC',
            // Giá từ cao xuống thấp.
            'price_desc' => ' ORDER BY products.price DESC',
            // Tên sản phẩm theo bảng chữ cái A đến Z.
            'name_asc' => ' ORDER BY products.product_name ASC',
            // Mặc định id lớn hơn (sản phẩm thêm sau) hiển thị trước.
            default => ' ORDER BY products.product_id DESC',
        };

        // BaseModel::all() chạy SQL và trả về nhiều dòng dạng mảng.
        return $this->all($sql, $params);
    }

    /**
     * Lấy một số sản phẩm mới để hiển thị trên trang chủ.
     */
    public function getFeatured(int $limit = 8): array
    {
        // Giới hạn từ 1 đến 20 để tránh đưa giá trị LIMIT quá lớn vào SQL.
        $limit = max(1, min($limit, 20));

        // Trả các sản phẩm đang bán theo thứ tự mới nhất.
        return $this->all(
            'SELECT products.*, categories.name AS category_name,
                    brands.brand_name
             FROM products
             INNER JOIN categories ON categories.category_id = products.category_id
             LEFT JOIN brands ON brands.brand_id = products.brand_id
             WHERE products.status = 1
             ORDER BY products.product_id DESC
             LIMIT ' . $limit
        );
    }

    /**
     * Tìm một sản phẩm đang được phép bán theo khóa chính.
     */
    public function find(int $id): ?array
    {
        // BaseModel::first() trả một dòng; không tìm thấy thì trả null.
        return $this->first(
            'SELECT products.*, categories.name AS category_name,
                    brands.brand_name
             FROM products
             INNER JOIN categories ON categories.category_id = products.category_id
             LEFT JOIN brands ON brands.brand_id = products.brand_id
             WHERE products.product_id = :id AND products.status = 1',
            // Bind id vào :id thay vì nối trực tiếp vào câu SQL.
            ['id' => $id]
        );
    }

    /**
     * Lấy sản phẩm cùng danh mục để gợi ý ở cuối trang chi tiết.
     */
    public function getRelated(int $categoryId, int $exceptId, int $limit = 4): array
    {
        // Chỉ cho phép lấy từ 1 đến 8 sản phẩm liên quan.
        $limit = max(1, min($limit, 8));

        // Truy vấn sản phẩm cùng category_id nhưng khác product_id đang xem.
        return $this->all(
            'SELECT products.*, categories.name AS category_name,
                    brands.brand_name
             FROM products
             INNER JOIN categories ON categories.category_id = products.category_id
             LEFT JOIN brands ON brands.brand_id = products.brand_id
             WHERE products.status = 1
               AND products.category_id = :category_id
               AND products.product_id != :except_id
             ORDER BY products.product_id DESC
             LIMIT ' . $limit,
            // Mảng dữ liệu được bind vào hai placeholder trong SQL.
            [
                // Danh mục của sản phẩm đang xem.
                'category_id' => $categoryId,
                // Id cần loại trừ để sản phẩm không tự gợi ý chính nó.
                'except_id' => $exceptId,
            ]
        );
    }

    /**
     * Lấy danh mục đang hoạt động để tạo menu và bộ lọc.
     */
    public function getCategories(): array
    {
        // Chỉ lấy hai cột cần dùng và sắp xếp tên A–Z.
        return $this->all(
            'SELECT category_id, name
             FROM categories
             WHERE status = 1
             ORDER BY name ASC'
        );
    }
}
