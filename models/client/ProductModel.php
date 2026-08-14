<?php

/**
 * Model đọc sản phẩm và biến thể cho khu vực khách hàng.
 *
 * Admin quản lý tồn kho theo từng dòng trong product_variants. Vì vậy Client
 * không dùng products.color, products.size hoặc products.stock để quyết định
 * khách có thể mua gì.
 */
class ClientProductModel extends BaseModel
{
    /**
     * Lấy danh sách sản phẩm cho trang Client.
     */
    public function getAll(
        string $keyword = '',
        int $categoryId = 0,
        string $sort = 'newest',
        int $limit = 12,
        int $offset = 0
    ): array {
        $limit = max(1, min($limit, 100));
        $offset = max(0, $offset);

        [$whereSql, $params] = $this->productFilters(
            $keyword,
            $categoryId
        );

        $sql = $this->productSelect() . ' ' . $whereSql;

        $sql .= match ($sort) {
            'price_asc' => ' ORDER BY products.price ASC',
            'price_desc' => ' ORDER BY products.price DESC',
            'name_asc' => ' ORDER BY products.product_name ASC',
            default => ' ORDER BY products.product_id DESC',
        };

        // Database chỉ trả đúng 12 sản phẩm của trang hiện tại.
        $sql .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;

        return $this->normalizeProductRows(
            $this->all($sql, $params)
        );
    }

    /**
     * Đếm tổng sản phẩm đang bán theo điều kiện tìm kiếm và danh mục.
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
     * Lấy sản phẩm mới cho trang chủ.
     */
    public function getFeatured(int $limit = 8): array
    {
        $limit = max(1, min($limit, 20));

        return $this->normalizeProductRows(
            $this->all(
                $this->productSelect()
                . ' WHERE products.status = 1
                    ORDER BY products.product_id DESC
                    LIMIT ' . $limit
            )
        );
    }

    /**
     * Tìm một sản phẩm đang được phép bán.
     */
    public function find(int $id): ?array
    {
        $product = $this->first(
            $this->productSelect()
            . ' WHERE products.product_id = :id
                  AND products.status = 1',
            ['id' => $id]
        );

        return $this->normalizeProduct($product);
    }

    /**
     * Lấy sản phẩm cùng danh mục.
     */
    public function getRelated(
        int $categoryId,
        int $exceptId,
        int $limit = 4
    ): array {
        $limit = max(1, min($limit, 8));

        return $this->normalizeProductRows(
            $this->all(
                $this->productSelect()
                . ' WHERE products.status = 1
                      AND products.category_id = :category_id
                      AND products.product_id != :except_id
                    ORDER BY products.product_id DESC
                    LIMIT ' . $limit,
                [
                    'category_id' => $categoryId,
                    'except_id' => $exceptId,
                ]
            )
        );
    }

    /**
     * Lấy các biến thể đang hoạt động của một sản phẩm.
     *
     * Biến thể hết hàng vẫn được trả về để trang chi tiết có thể thông báo rõ
     * tổ hợp nào đã hết, nhưng form mua chỉ cho chọn dòng có stock > 0.
     */
    public function getVariants(int $productId): array
    {
        return $this->all(
            'SELECT variant_id, product_id, color, size, stock, status
             FROM product_variants
             WHERE product_id = :product_id
               AND status = 1
             ORDER BY color ASC, size ASC, variant_id ASC',
            ['product_id' => $productId]
        );
    }

    /**
     * Đọc đúng một biến thể có thể bán kèm thông tin sản phẩm hiện tại.
     */
    public function findPurchasableVariant(
        int $variantId,
        int $productId = 0
    ): ?array {
        $sql = 'SELECT
                    product_variants.variant_id,
                    product_variants.product_id,
                    product_variants.color,
                    product_variants.size,
                    product_variants.stock,
                    product_variants.status AS variant_status,
                    products.product_name,
                    products.image,
                    products.price,
                    products.status AS product_status
                FROM product_variants
                INNER JOIN products
                    ON products.product_id = product_variants.product_id
                WHERE product_variants.variant_id = :variant_id
                  AND product_variants.status = 1
                  AND products.status = 1';

        $params = ['variant_id' => $variantId];

        // Khi thêm giỏ, điều kiện này ngăn việc ghép variant của sản phẩm khác.
        if ($productId > 0) {
            $sql .= ' AND product_variants.product_id = :product_id';
            $params['product_id'] = $productId;
        }

        return $this->first($sql, $params);
    }

    /**
     * Hỗ trợ chuyển giỏ session cũ sang variant_id sau khi nâng cấp Client.
     */
    public function findVariantByAttributes(
        int $productId,
        string $color,
        string $size
    ): ?array {
        return $this->first(
            'SELECT variant_id
             FROM product_variants
             WHERE product_id = :product_id
               AND color = :color
               AND size = :size
               AND status = 1
             LIMIT 1',
            [
                'product_id' => $productId,
                'color' => $color,
                'size' => $size,
            ]
        );
    }

    /**
     * Lấy danh mục đang hoạt động để tạo menu và bộ lọc.
     */
    public function getCategories(): array
    {
        return $this->all(
            'SELECT category_id, name
             FROM categories
             WHERE status = 1
             ORDER BY name ASC'
        );
    }

    /**
     * Phần SELECT dùng chung.
     *
     * variant_stock là tổng tồn thực trong product_variants. Hàm normalize sẽ
     * đưa giá trị này về khóa stock để các view cũ vẫn hoạt động.
     */
    private function productSelect(): string
    {
        return 'SELECT
                    products.*,
                    categories.name AS category_name,
                    brands.brand_name,
                    COALESCE((
                        SELECT SUM(product_variants.stock)
                        FROM product_variants
                        WHERE product_variants.product_id = products.product_id
                          AND product_variants.status = 1
                    ), 0) AS variant_stock
                FROM products
                INNER JOIN categories
                    ON categories.category_id = products.category_id
                LEFT JOIN brands
                    ON brands.brand_id = products.brand_id';
    }

    /**
     * Chuẩn hóa một sản phẩm để stock luôn là tổng kho biến thể.
     */
    private function normalizeProduct(?array $product): ?array
    {
        if ($product === null) {
            return null;
        }

        $product['stock'] = (int) ($product['variant_stock'] ?? 0);
        unset($product['variant_stock']);

        return $product;
    }

    /**
     * Chuẩn hóa danh sách sản phẩm.
     */
    private function normalizeProductRows(array $products): array
    {
        return array_map(
            fn(array $product): array => $this->normalizeProduct($product),
            $products
        );
    }

    /**
     * Điều kiện dùng chung cho SELECT danh sách và SELECT COUNT.
     */
    private function productFilters(
        string $keyword,
        int $categoryId
    ): array {
        $whereSql = 'WHERE products.status = 1';
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
