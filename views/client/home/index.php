<!-- Khu vực banner giới thiệu đầu trang. -->
<section class="hero">
    <div class="container">
        <div class="hero-content">
            <div class="eyebrow">Bộ sưu tập mới</div>
            <h1>Phong cách mới cho mỗi ngày</h1>
            <p>Khám phá những thiết kế dễ mặc, hiện đại và được cập nhật liên tục.</p>
            <!-- Nút dẫn người dùng đến toàn bộ danh sách sản phẩm. -->
            <a class="btn btn-primary" href="<?= e(url('products')) ?>">Mua sắm ngay</a>
        </div>
    </div>
</section>

<!-- Chỉ hiển thị khu vực danh mục khi database có dữ liệu. -->
<?php if (!empty($categories)): ?>
    <section class="section section-soft">
        <div class="container">
            <div class="section-heading">
                <div>
                    <h2>Danh mục nổi bật</h2>
                    <p>Chọn nhanh dòng sản phẩm bạn quan tâm</p>
                </div>
            </div>
            <div class="category-grid">
                <!-- array_slice(..., 0, 8) chỉ lấy tối đa 8 danh mục đầu tiên. -->
                <?php foreach (array_slice($categories, 0, 8) as $category): ?>
                    <a
                        class="category-card"
                        href="<?= e(url('products', ['category_id' => $category['category_id']])) ?>"
                    >
                        <!-- Gửi category_id lên URL để trang products lọc dữ liệu. -->
                        <?= e($category['name']) ?> →
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
<?php endif; ?>

<!-- Khu vực sản phẩm mới được lấy từ HomeController. -->
<section class="section">
    <div class="container">
        <div class="section-heading">
            <div>
                <h2>Sản phẩm mới</h2>
                <p>Những sản phẩm vừa được cập nhật</p>
            </div>
            <a href="<?= e(url('products')) ?>">Xem tất cả →</a>
        </div>

        <!-- Nếu có sản phẩm thì tạo lưới card. -->
        <?php if (!empty($featuredProducts)): ?>
            <div class="product-grid">
                <!-- Lặp qua từng sản phẩm và dùng lại partial _card.php. -->
                <?php foreach ($featuredProducts as $item): ?>
                    <?php require PATH_VIEW . 'client/products/_card.php'; ?>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <!-- Thông báo thân thiện khi bảng products chưa có sản phẩm hoạt động. -->
            <div class="empty">
                <h2>Chưa có sản phẩm</h2>
                <p>Sản phẩm đang được cập nhật.</p>
            </div>
        <?php endif; ?>
    </div>
</section>
