<?php

/**
 * Model thao tác với bảng coupons.
 */
class CouponModel extends BaseModel
{
    /**
     * Lấy toàn bộ mã giảm giá.
     */
    public function getAll(): array
    {
        return $this->all(
            'SELECT * FROM coupons ORDER BY coupon_id DESC'
        );
    }

    /**
     * Tìm mã giảm giá theo id.
     */
    public function find(int $id): ?array
    {
        return $this->first(
            'SELECT * FROM coupons WHERE coupon_id = :id',
            ['id' => $id]
        );
    }

    /**
     * Thêm mã giảm giá.
     */
    public function create(array $data): int
    {
        $sql = 'INSERT INTO coupons (
                    code, discount_type, discount_value, min_order_value,
                    max_discount, start_date, end_date, quantity, status
                ) VALUES (
                    :code, :discount_type, :discount_value, :min_order_value,
                    :max_discount, :start_date, :end_date, :quantity, :status
                )';

        return $this->execute($sql, $data);
    }

    /**
     * Cập nhật mã giảm giá.
     */
    public function update(int $id, array $data): int
    {
        // Gắn id cho điều kiện WHERE.
        $data['id'] = $id;

        $sql = 'UPDATE coupons
                SET code = :code,
                    discount_type = :discount_type,
                    discount_value = :discount_value,
                    min_order_value = :min_order_value,
                    max_discount = :max_discount,
                    start_date = :start_date,
                    end_date = :end_date,
                    quantity = :quantity,
                    status = :status
                WHERE coupon_id = :id';

        return $this->execute($sql, $data);
    }

    /**
     * Xóa mã giảm giá.
     */
    public function delete(int $id): int
    {
        return $this->execute(
            'DELETE FROM coupons WHERE coupon_id = :id',
            ['id' => $id]
        );
    }
}
