<!-- Nội dung chính của trang danh sách và tìm kiếm sản phẩm. -->
<section class="section">
    <div class="container">
        <div class="section-heading">
            <div>
                <!-- Dùng toán tử ba ngôi để đổi tiêu đề theo trạng thái tìm kiếm. -->
                <h1><?= $keyword !== '' ? 'Kết quả tìm kiếm' : 'Tất cả sản phẩm' ?></h1>
                <p>
                    <!-- Khi có keyword thì in từ khóa và số lượng kết quả. -->
                    <?php if ($keyword !== ''): ?>
                        Từ khóa “<?= e($keyword) ?>” · <?= count($products) ?> sản phẩm
                    <?php else: ?>
                        Có <?= count($products) ?> sản phẩm phù hợp
                    <?php endif; ?>
                </p>
            </div>
        </div>

        <!-- Form GET giữ điều kiện lọc trên URL sau khi tải lại trang. -->
        <form class="filter-bar" action="<?= e(url('products')) ?>" method="get">
            <!-- action giúp router gọi đúng ProductController::index(). -->
            <input type="hidden" name="action" value="products">
            <input
                class="field"
                type="search"
                name="keyword"
                value="<?= e($keyword) ?>"
                placeholder="Nhập tên sản phẩm"
            >
            <!-- Ô chọn danh mục; giá trị 0 tương ứng với tất cả danh mục. -->
            <select class="field" name="category_id">
                <option value="0">Tất cả danh mục</option>
                <!-- selected giữ lại danh mục người dùng vừa chọn. -->
                <?php foreach ($categories as $category): ?>
                    <option
                        value="<?= e($category['category_id']) ?>"
                        <?= $categoryId === (int) $category['category_id'] ? 'selected' : '' ?>
                    >
                        <?= e($category['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <!-- Ô chọn cách sắp xếp sản phẩm. -->
            <select class="field" name="sort">
                <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Mới nhất</option>
                <option value="price_asc" <?= $sort === 'price_asc' ? 'selected' : '' ?>>Giá tăng dần</option>
                <option value="price_desc" <?= $sort === 'price_desc' ? 'selected' : '' ?>>Giá giảm dần</option>
                <option value="name_asc" <?= $sort === 'name_asc' ? 'selected' : '' ?>>Tên A–Z</option>
            </select>
            <button class="btn btn-dark" type="submit">Lọc sản phẩm</button>
        </form>

        <!-- Chỉ tạo lưới khi truy vấn trả về ít nhất một sản phẩm. -->
        <?php if (!empty($products)): ?>
            <div class="product-grid">
                <!-- $item được partial _card.php sử dụng để in dữ liệu. -->
                <?php foreach ($products as $item): ?>
                    <?php require PATH_VIEW . 'client/products/_card.php'; ?>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <!-- Trạng thái rỗng khi không có sản phẩm phù hợp bộ lọc. -->
            <div class="empty">
                <h2>Không tìm thấy sản phẩm</h2>
                <p>Hãy thử từ khóa hoặc danh mục khác.</p>
                <a class="btn btn-primary" href="<?= e(url('products')) ?>">Xem tất cả sản phẩm</a>
            </div>
        <?php endif; ?>
    </div>
</section>
