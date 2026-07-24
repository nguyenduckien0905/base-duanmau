<?php

/**
 * Model quản lý đánh giá sản phẩm.
 */
class ReviewModel extends BaseModel
{
    /**
     * Lấy danh sách đánh giá kèm người dùng và sản phẩm.
     */
    public function getAll(): array
    {
        $sql = 'SELECT reviews.*, users.fullname, users.email,
                       products.product_name
                FROM reviews
                INNER JOIN users ON users.user_id = reviews.user_id
                INNER JOIN products ON products.product_id = reviews.product_id
                ORDER BY reviews.created_at DESC';

        return $this->all($sql);
    }

    /**
     * Ẩn hoặc hiện một đánh giá.
     */
    public function toggleStatus(int $id): int
    {
        return $this->execute(
            'UPDATE reviews
             SET status = IF(status = 1, 0, 1)
             WHERE review_id = :id',
            ['id' => $id]
        );
    }

    /**
     * Xóa một đánh giá.
     */
    public function delete(int $id): int
    {
        return $this->execute(
            'DELETE FROM reviews WHERE review_id = :id',
            ['id' => $id]
        );
    }
}
