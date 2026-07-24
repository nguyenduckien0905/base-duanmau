<?php

/**
 * Model xử lý dữ liệu đăng nhập của Admin.
 */
class AdminAuthModel extends BaseModel
{
    /**
     * Tìm tài khoản theo email và lấy kèm tên vai trò.
     */
    public function findByEmail(string $email): ?array
    {
        // Chỉ lấy đúng một tài khoản có email được truyền vào.
        $sql = 'SELECT users.*, roles.role_name
                FROM users
                INNER JOIN roles ON roles.role_id = users.role_id
                WHERE users.email = :email
                LIMIT 1';

        // Trả bản ghi tìm được hoặc null.
        return $this->first($sql, ['email' => $email]);
    }
}
