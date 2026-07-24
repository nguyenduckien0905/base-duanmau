<?php

/**
 * Model thao tác với bảng banners.
 */
class BannerModel extends BaseModel
{
    /**
     * Lấy danh sách banner mới nhất.
     */
    public function getAll(): array
    {
        return $this->all(
            'SELECT * FROM banners ORDER BY banner_id DESC'
        );
    }

    /**
     * Tìm banner theo id.
     */
    public function find(int $id): ?array
    {
        return $this->first(
            'SELECT * FROM banners WHERE banner_id = :id',
            ['id' => $id]
        );
    }

    /**
     * Thêm banner.
     */
    public function create(array $data): int
    {
        $sql = 'INSERT INTO banners (title, image, link, status)
                VALUES (:title, :image, :link, :status)';

        return $this->execute($sql, $data);
    }

    /**
     * Cập nhật banner.
     */
    public function update(int $id, array $data): int
    {
        // Thêm id để dùng trong câu UPDATE.
        $data['id'] = $id;

        $sql = 'UPDATE banners
                SET title = :title,
                    image = :image,
                    link = :link,
                    status = :status
                WHERE banner_id = :id';

        return $this->execute($sql, $data);
    }

    /**
     * Xóa banner.
     */
    public function delete(int $id): int
    {
        return $this->execute(
            'DELETE FROM banners WHERE banner_id = :id',
            ['id' => $id]
        );
    }
}
