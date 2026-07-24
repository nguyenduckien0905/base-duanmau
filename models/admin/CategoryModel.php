<?php

/**
 * Model thao tác với bảng categories.
 */
class CategoryModel extends BaseModel
{
    /**
     * Lấy danh sách danh mục kèm tên danh mục cha.
     */
    public function getAll(): array
    {
        // Tự join bảng categories để lấy tên danh mục cha.
        $sql = 'SELECT child.*, parent.name AS parent_name
                FROM categories AS child
                LEFT JOIN categories AS parent ON parent.category_id = child.parent_id
                ORDER BY child.category_id DESC';

        // Trả danh sách danh mục.
        return $this->all($sql);
    }

    /**
     * Lấy danh sách dùng cho ô chọn danh mục cha.
     */
    public function getOptions(?int $exceptId = null): array
    {
        // Không cho một danh mục chọn chính nó làm danh mục cha khi sửa.
        if ($exceptId !== null) {
            return $this->all(
                'SELECT category_id, name
                 FROM categories
                 WHERE category_id != :id
                 ORDER BY name ASC',
                ['id' => $exceptId]
            );
        }

        // Khi thêm mới thì lấy toàn bộ danh mục.
        return $this->all(
            'SELECT category_id, name FROM categories ORDER BY name ASC'
        );
    }

    /**
     * Tìm danh mục theo khóa chính.
     */
    public function find(int $id): ?array
    {
        return $this->first(
            'SELECT * FROM categories WHERE category_id = :id',
            ['id' => $id]
        );
    }

    /**
     * Thêm danh mục mới.
     */
    public function create(array $data): int
    {
        // Câu SQL dùng placeholder để bind dữ liệu an toàn.
        $sql = 'INSERT INTO categories (parent_id, name, image, status)
                VALUES (:parent_id, :name, :image, :status)';

        // Trả số dòng đã thêm.
        return $this->execute($sql, $data);
    }

    /**
     * Cập nhật một danh mục.
     */
    public function update(int $id, array $data): int
    {
        // Bổ sung id vào mảng dữ liệu.
        $data['id'] = $id;

        // Cập nhật các cột có trong form.
        $sql = 'UPDATE categories
                SET parent_id = :parent_id,
                    name = :name,
                    image = :image,
                    status = :status
                WHERE category_id = :id';

        // Trả số dòng đã cập nhật.
        return $this->execute($sql, $data);
    }

    /**
     * Xóa danh mục.
     */
    public function delete(int $id): int
    {
        return $this->execute(
            'DELETE FROM categories WHERE category_id = :id',
            ['id' => $id]
        );
    }
}
