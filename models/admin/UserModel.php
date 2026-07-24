<?php

/**
 * Model quản lý tài khoản người dùng.
 */
class UserModel extends BaseModel
{
    /**
     * Lấy danh sách tài khoản theo vai trò và từ khóa.
     */
    public function getAll(string $keyword = '', int $roleId = 0): array
    {
        // Join roles để hiển thị tên vai trò.
        $sql = 'SELECT users.*, roles.role_name
                FROM users
                INNER JOIN roles ON roles.role_id = users.role_id
                WHERE 1 = 1';

        // Mảng dữ liệu bind.
        $params = [];

        // Tìm theo họ tên, email hoặc số điện thoại.
        if ($keyword !== '') {
            $sql .= ' AND (
                users.fullname LIKE :keyword_name
                OR users.email LIKE :keyword_email
                OR users.phone LIKE :keyword_phone
            )';

            // Mỗi vị trí dùng một placeholder riêng khi tắt emulate prepares.
            $params['keyword_name'] = '%' . $keyword . '%';
            $params['keyword_email'] = '%' . $keyword . '%';
            $params['keyword_phone'] = '%' . $keyword . '%';
        }

        // Lọc theo vai trò khi role id lớn hơn 0.
        if ($roleId > 0) {
            $sql .= ' AND users.role_id = :role_id';
            $params['role_id'] = $roleId;
        }

        // Tài khoản mới hiển thị trước.
        $sql .= ' ORDER BY users.user_id DESC';

        return $this->all($sql, $params);
    }

    /**
     * Tìm tài khoản theo id.
     */
    public function find(int $id): ?array
    {
        return $this->first(
            'SELECT * FROM users WHERE user_id = :id',
            ['id' => $id]
        );
    }

    /**
     * Đảo trạng thái hoạt động của tài khoản.
     */
    public function toggleStatus(int $id): int
    {
        // Nếu status đang là 1 thì đổi thành 0 và ngược lại.
        return $this->execute(
            'UPDATE users SET status = IF(status = 1, 0, 1) WHERE user_id = :id',
            ['id' => $id]
        );
    }
}
