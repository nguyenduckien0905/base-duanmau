<?php

/**
 * Model quản lý sản phẩm và các biến thể màu - size của sản phẩm.
 */
class ProductModel extends BaseModel
{
    /**
     * Lấy danh sách sản phẩm, tổng kho biến thể và số biến thể đang bán.
     */
    public function getAll(
        string $keyword = '',
        int $categoryId = 0,
        int $limit = 20,
        int $offset = 0
    ): array {
        // LIMIT và OFFSET được ép thành số nguyên an toàn trước khi ghép SQL.
        $limit = max(1, min($limit, 100));
        $offset = max(0, $offset);

        // Dùng chung điều kiện với countAll() để tổng bản ghi luôn chính xác.
        [$whereSql, $params] = $this->productFilters(
            $keyword,
            $categoryId
        );

        // Subquery variant_summary giúp tránh GROUP BY toàn bộ cột products.
        $sql = 'SELECT products.*,
                       categories.name AS category_name,
                       brands.brand_name,
                       COALESCE(variant_summary.total_stock, products.stock) AS total_stock,
                       COALESCE(variant_summary.variant_count, 0) AS variant_count
                FROM products
                INNER JOIN categories
                    ON categories.category_id = products.category_id
                LEFT JOIN brands
                    ON brands.brand_id = products.brand_id
                LEFT JOIN (
                    SELECT product_id,
                           SUM(stock) AS total_stock,
                           COUNT(*) AS variant_count
                    FROM product_variants
                    WHERE status = 1
                    GROUP BY product_id
                ) AS variant_summary
                    ON variant_summary.product_id = products.product_id
                ' . $whereSql;

        // Chỉ lấy đúng số dòng của trang hiện tại từ database.
        $sql .= ' ORDER BY products.product_id DESC
                  LIMIT ' . $limit . ' OFFSET ' . $offset;

        return $this->all($sql, $params);
    }

    /**
     * Đếm tổng sản phẩm theo cùng điều kiện tìm kiếm và danh mục.
     */
    public function countAll(string $keyword = '', int $categoryId = 0): int
    {
        [$whereSql, $params] = $this->productFilters(
            $keyword,
            $categoryId
        );

        $result = $this->first(
            'SELECT COUNT(*) AS total
             FROM products
             INNER JOIN categories
                ON categories.category_id = products.category_id
             ' . $whereSql,
            $params
        );

        return (int) ($result['total'] ?? 0);
    }

    /**
     * Tìm thông tin chung của một sản phẩm.
     */
    public function find(int $id): ?array
    {
        return $this->first(
            'SELECT * FROM products WHERE product_id = :id',
            ['id' => $id]
        );
    }

    /**
     * Lấy các biến thể đang sử dụng của một sản phẩm.
     */
    public function getVariants(int $productId): array
    {
        return $this->all(
            'SELECT variant_id, product_id, color, size, stock, status
             FROM product_variants
             WHERE product_id = :product_id
               AND status = 1
             ORDER BY color ASC, size ASC',
            ['product_id' => $productId]
        );
    }

    /**
     * Thêm sản phẩm và biến thể trong cùng một transaction.
     */
    public function createWithVariants(array $product, array $variants): int
    {
        // Bắt đầu transaction để tránh sản phẩm có mà biến thể không có.
        $this->pdo->beginTransaction();

        try {
            // Tổng tồn kho sản phẩm bằng tổng kho của mọi biến thể.
            $product['stock'] = $this->calculateTotalStock($variants);

            // color và size cũ không được ghi nữa; dữ liệu nằm ở product_variants.
            $sql = 'INSERT INTO products (
                        category_id, brand_id, product_name, description,
                        material, stock, price, image, status
                    ) VALUES (
                        :category_id, :brand_id, :product_name, :description,
                        :material, :stock, :price, :image, :status
                    )';

            // Thêm thông tin chung của sản phẩm.
            $this->execute($sql, $product);

            // Lấy id của sản phẩm vừa được thêm.
            $productId = (int) $this->pdo->lastInsertId();

            // Thêm toàn bộ tổ hợp màu - size.
            $this->syncVariants($productId, $variants);

            // Xác nhận toàn bộ thay đổi.
            $this->pdo->commit();

            return $productId;
        } catch (Throwable $exception) {
            // Hủy toàn bộ nếu bất kỳ câu SQL nào thất bại.
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $exception;
        }
    }

    /**
     * Cập nhật sản phẩm và biến thể trong cùng một transaction.
     */
    public function updateWithVariants(
        int $id,
        array $product,
        array $variants
    ): int {
        // Bắt đầu transaction.
        $this->pdo->beginTransaction();

        try {
            // Đồng bộ tổng kho cho code Client cũ.
            $product['stock'] = $this->calculateTotalStock($variants);

            // Thêm id vào mảng bind của PDO.
            $product['id'] = $id;

            // Cập nhật thông tin chung của sản phẩm.
            $sql = 'UPDATE products
                    SET category_id = :category_id,
                        brand_id = :brand_id,
                        product_name = :product_name,
                        description = :description,
                        material = :material,
                        stock = :stock,
                        price = :price,
                        image = :image,
                        status = :status
                    WHERE product_id = :id';

            $changedRows = $this->execute($sql, $product);

            // Đồng bộ các tổ hợp màu - size.
            $this->syncVariants($id, $variants);

            // Xác nhận thay đổi.
            $this->pdo->commit();

            return $changedRows;
        } catch (Throwable $exception) {
            // Hủy toàn bộ khi có lỗi.
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $exception;
        }
    }

    /**
     * Xóa sản phẩm; khóa ngoại sẽ tự xóa product_variants.
     */
    public function delete(int $id): int
    {
        return $this->execute(
            'DELETE FROM products WHERE product_id = :id',
            ['id' => $id]
        );
    }

    /**
     * Đồng bộ danh sách biến thể của một sản phẩm.
     */
    private function syncVariants(int $productId, array $variants): void
    {
        // Tạm ngừng mọi biến thể cũ.
        // Không xóa thật để sau này order_items vẫn có thể tham chiếu variant_id.
        $this->execute(
            'UPDATE product_variants
             SET stock = 0, status = 0
             WHERE product_id = :product_id',
            ['product_id' => $productId]
        );

        // Nếu tổ hợp đã tồn tại thì kích hoạt và cập nhật lại tồn kho.
        $sql = 'INSERT INTO product_variants (
                    product_id, color, size, stock, status
                ) VALUES (
                    :product_id, :color, :size, :stock, 1
                )
                ON DUPLICATE KEY UPDATE
                    stock = VALUES(stock),
                    status = 1,
                    updated_at = CURRENT_TIMESTAMP';

        // Chuẩn bị câu SQL một lần để dùng cho nhiều biến thể.
        $statement = $this->pdo->prepare($sql);

        // Thêm hoặc cập nhật từng biến thể.
        foreach ($variants as $variant) {
            $statement->execute([
                'product_id' => $productId,
                'color' => $variant['color'],
                'size' => $variant['size'],
                'stock' => (int) $variant['stock'],
            ]);
        }
    }

    /**
     * Tính tổng tồn kho từ danh sách biến thể.
     */
    private function calculateTotalStock(array $variants): int
    {
        $totalStock = 0;

        foreach ($variants as $variant) {
            $totalStock += (int) $variant['stock'];
        }

        return $totalStock;
    }

    /**
     * Tạo điều kiện dùng chung cho câu lấy dữ liệu và câu COUNT.
     */
    private function productFilters(
        string $keyword,
        int $categoryId
    ): array {
        $whereSql = 'WHERE 1 = 1';
        $params = [];

        if ($keyword !== '') {
            $whereSql .= ' AND products.product_name LIKE :keyword';
            $params['keyword'] = '%' . $keyword . '%';
        }

        if ($categoryId > 0) {
            $whereSql .= ' AND products.category_id = :category_id';
            $params['category_id'] = $categoryId;
        }

        return [$whereSql, $params];
    }
}
