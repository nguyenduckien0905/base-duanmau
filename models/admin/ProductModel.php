<?php

/**
 * Model thao tác với bảng products.
 */
class ProductModel extends BaseModel
{
    /**
     * Lấy danh sách sản phẩm, có hỗ trợ tìm kiếm và lọc danh mục.
     */
    public function getAll(string $keyword = '', int $categoryId = 0): array
    {
        // Câu SQL gốc lấy kèm tên danh mục và thương hiệu.
        $sql = 'SELECT products.*, categories.name AS category_name,
                       brands.brand_name
                FROM products
                INNER JOIN categories ON categories.category_id = products.category_id
                LEFT JOIN brands ON brands.brand_id = products.brand_id
                WHERE 1 = 1';

        // Mảng chứa dữ liệu bind vào câu SQL.
        $params = [];

        // Thêm điều kiện tìm theo tên khi có từ khóa.
        if ($keyword !== '') {
            $sql .= ' AND products.product_name LIKE :keyword';
            $params['keyword'] = '%' . $keyword . '%';
        }

        // Thêm điều kiện lọc theo danh mục khi có category id.
        if ($categoryId > 0) {
            $sql .= ' AND products.category_id = :category_id';
            $params['category_id'] = $categoryId;
        }

        // Sản phẩm mới nhất hiển thị trước.
        $sql .= ' ORDER BY products.product_id DESC';

        // Trả danh sách sản phẩm.
        return $this->all($sql, $params);
    }

    /**
     * Tìm một sản phẩm.
     */
    public function find(int $id): ?array
    {
        return $this->first(
            'SELECT * FROM products WHERE product_id = :id',
            ['id' => $id]
        );
    }

    /**
     * Thêm sản phẩm.
     */
    public function create(array $data): int
    {
        $sql = 'INSERT INTO products (
                    category_id, brand_id, product_name, description,
                    material, color, size, stock, price, image, status
                ) VALUES (
                    :category_id, :brand_id, :product_name, :description,
                    :material, :color, :size, :stock, :price, :image, :status
                )';

        return $this->execute($sql, $data);
    }

    /**
     * Cập nhật sản phẩm.
     */
    public function update(int $id, array $data): int
    {
        // Thêm id để dùng trong mệnh đề WHERE.
        $data['id'] = $id;

        $sql = 'UPDATE products
                SET category_id = :category_id,
                    brand_id = :brand_id,
                    product_name = :product_name,
                    description = :description,
                    material = :material,
                    color = :color,
                    size = :size,
                    stock = :stock,
                    price = :price,
                    image = :image,
                    status = :status
                WHERE product_id = :id';

        return $this->execute($sql, $data);
    }

    /**
     * Xóa sản phẩm.
     */
    public function delete(int $id): int
    {
        return $this->execute(
            'DELETE FROM products WHERE product_id = :id',
            ['id' => $id]
        );
    }
}
