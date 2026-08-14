<?php

/**
 * Model đánh giá phía khách hàng.
 */
class ClientReviewModel extends BaseModel
{
    /**
     * Tạo đánh giá từ một sản phẩm thuộc đơn đã hoàn thành.
     */
    public function createFromCompletedOrderItem(
        int $orderItemId,
        int $userId,
        int $rating,
        string $comment
    ): int {
        $purchasedItem = $this->first(
            'SELECT order_items.order_item_id,
                    order_items.product_id,
                    orders.order_id,
                    orders.status
             FROM order_items
             INNER JOIN orders
                ON orders.order_id = order_items.order_id
             WHERE order_items.order_item_id = :order_item_id
               AND orders.user_id = :user_id
             LIMIT 1',
            [
                'order_item_id' => $orderItemId,
                'user_id' => $userId,
            ]
        );

        if (!$purchasedItem || $purchasedItem['status'] !== 'completed') {
            throw new DomainException(
                'Bạn chỉ được đánh giá sản phẩm thuộc đơn đã nhận.'
            );
        }

        $productId = (int) ($purchasedItem['product_id'] ?? 0);
        if ($productId <= 0) {
            throw new DomainException('Sản phẩm không còn tồn tại để đánh giá.');
        }

        $existing = $this->first(
            'SELECT review_id
             FROM reviews
             WHERE user_id = :user_id
               AND product_id = :product_id
             LIMIT 1',
            [
                'user_id' => $userId,
                'product_id' => $productId,
            ]
        );

        if ($existing) {
            throw new DomainException('Bạn đã đánh giá sản phẩm này rồi.');
        }

        return $this->execute(
            'INSERT INTO reviews (
                user_id, product_id, order_item_id,
                rating, comment, status
             ) VALUES (
                :user_id, :product_id, :order_item_id,
                :rating, :comment, 1
             )',
            [
                'user_id' => $userId,
                'product_id' => $productId,
                'order_item_id' => $orderItemId,
                'rating' => $rating,
                'comment' => $comment === '' ? null : $comment,
            ]
        );
    }

    /**
     * Lấy đánh giá đang được Admin cho hiển thị của một sản phẩm.
     */
    public function getVisibleByProduct(int $productId): array
    {
        return $this->all(
            'SELECT reviews.review_id, reviews.rating, reviews.comment,
                    reviews.created_at, users.fullname
             FROM reviews
             INNER JOIN users ON users.user_id = reviews.user_id
             WHERE reviews.product_id = :product_id
               AND reviews.status = 1
             ORDER BY reviews.created_at DESC',
            ['product_id' => $productId]
        );
    }

    /**
     * Tính điểm trung bình và tổng số đánh giá đang hiển thị.
     */
    public function getSummary(int $productId): array
    {
        return $this->first(
            'SELECT COUNT(*) AS review_count,
                    COALESCE(AVG(rating), 0) AS average_rating
             FROM reviews
             WHERE product_id = :product_id
               AND status = 1',
            ['product_id' => $productId]
        ) ?? [
            'review_count' => 0,
            'average_rating' => 0,
        ];
    }
}
