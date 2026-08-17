<?php

/**
 * Model xác thực tài khoản khách hàng.
 * Bảng users cũng chính là bảng được AdminUserController quản lý.
 */
class ClientAuthModel extends BaseModel
{
    /**
     * Tìm một tài khoản bằng email.
     */
    public function findByEmail(string $email): ?array
    {
        // LIMIT 1 vì email của mỗi tài khoản phải là duy nhất.
        return $this->first(
            'SELECT users.*, roles.role_name
             FROM users
             INNER JOIN roles ON roles.role_id = users.role_id
             WHERE users.email = :email
             LIMIT 1',
            ['email' => $email]
        );
    }

    /**
     * Tìm khách hàng theo khóa chính.
     */
    public function find(int $id): ?array
    {
        // Chỉ lấy tài khoản vai trò khách hàng (role_id = 3).
        return $this->first(
            'SELECT * FROM users
             WHERE user_id = :id AND role_id = 3
             LIMIT 1',
            ['id' => $id]
        );
    }

    /**
     * Tạo tài khoản khách hàng mới.
     */
    public function create(array $data): int
    {
        // role_id = 3 giúp tài khoản mới xuất hiện trong nhóm khách hàng ở Admin.
       $sql = 'INSERT INTO users (
            role_id,fullname, email, password,phone, avatar, status )
             VALUES (
             3, :fullname, :email, :password, :phone, NULL,  1)';
        // Chạy INSERT và trả số dòng được thêm.
        return $this->execute($sql, $data);
    }

    /**
     * Cập nhật thông tin liên hệ của khách hàng.
     */
    public function updateProfile(int $id, array $data): int
    {
        // Thêm id để bind vào điều kiện WHERE.
        $data['id'] = $id;

        // Chỉ cho Client sửa các trường thông tin cá nhân an toàn.
        return $this->execute(
            'UPDATE users
             SET fullname = :fullname,
                 phone = :phone,
                 address = :address
             WHERE user_id = :id AND role_id = 3',
            $data
        );
    }
}
