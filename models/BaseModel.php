<?php

/**
 * Model nền chịu trách nhiệm kết nối database và chạy câu lệnh PDO.
 */
class BaseModel
{
    // Biến kết nối được dùng lại trong các model con.
    protected PDO $pdo;

    /**
     * Tạo kết nối PDO khi model được khởi tạo.
     */
    public function __construct()
    {
        // Chuỗi DSN chỉ rõ máy chủ, cổng, database và bảng mã.
        $dsn = 'mysql:host=' . DB_HOST
            . ';port=' . DB_PORT
            . ';dbname=' . DB_NAME
            . ';charset=utf8mb4';

        // Tạo đối tượng PDO bằng thông tin trong configs/env.php.
        $this->pdo = new PDO(
            $dsn,
            DB_USERNAME,
            DB_PASSWORD,
            DB_OPTIONS
        );
    }

    /**
     * Lấy nhiều bản ghi từ database.
     */
    protected function all(string $sql, array $params = []): array
    {
        // Chuẩn bị câu SQL để tránh SQL Injection.
        $statement = $this->pdo->prepare($sql);

        // Gắn dữ liệu và chạy câu SQL.
        $statement->execute($params);

        // Trả toàn bộ kết quả dạng mảng.
        return $statement->fetchAll();
    }

    /**
     * Lấy một bản ghi từ database.
     */
    protected function first(string $sql, array $params = []): ?array
    {
        // Chuẩn bị câu SQL.
        $statement = $this->pdo->prepare($sql);

        // Chạy câu SQL với tham số truyền vào.
        $statement->execute($params);

        // Lấy bản ghi đầu tiên.
        $result = $statement->fetch();

        // Trả null khi không tìm thấy dữ liệu.
        return $result === false ? null : $result;
    }

    /**
     * Chạy INSERT, UPDATE hoặc DELETE và trả số dòng bị tác động.
     */
    protected function execute(string $sql, array $params = []): int
    {
        // Chuẩn bị câu SQL.
        $statement = $this->pdo->prepare($sql);

        // Chạy câu SQL với dữ liệu đã được bind an toàn.
        $statement->execute($params);

        // Trả số dòng đã thay đổi.
        return $statement->rowCount();
    }
}
