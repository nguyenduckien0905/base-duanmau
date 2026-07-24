<?php

/**
 * Model thao tác với bảng brands.
 */
class BrandModel extends BaseModel
{
    /**
     * Lấy toàn bộ thương hiệu.
     */
    public function getAll(): array
    {
        return $this->all('SELECT * FROM brands ORDER BY brand_id DESC');
    }

    /**
     * Lấy thương hiệu đang hoạt động cho form sản phẩm.
     */
    public function getOptions(): array
    {
        return $this->all(
            'SELECT brand_id, brand_name
             FROM brands
             WHERE status = 1
             ORDER BY brand_name ASC'
        );
    }

    /**
     * Tìm thương hiệu theo id.
     */
    public function find(int $id): ?array
    {
        return $this->first(
            'SELECT * FROM brands WHERE brand_id = :id',
            ['id' => $id]
        );
    }

    /**
     * Thêm thương hiệu.
     */
    public function create(array $data): int
    {
        $sql = 'INSERT INTO brands (brand_name, logo, status)
                VALUES (:brand_name, :logo, :status)';

        return $this->execute($sql, $data);
    }

    /**
     * Cập nhật thương hiệu.
     */
    public function update(int $id, array $data): int
    {
        // Gắn khóa chính vào dữ liệu bind.
        $data['id'] = $id;

        $sql = 'UPDATE brands
                SET brand_name = :brand_name,
                    logo = :logo,
                    status = :status
                WHERE brand_id = :id';

        return $this->execute($sql, $data);
    }

    /**
     * Xóa thương hiệu.
     */
    public function delete(int $id): int
    {
        return $this->execute(
            'DELETE FROM brands WHERE brand_id = :id',
            ['id' => $id]
        );
    }
}
