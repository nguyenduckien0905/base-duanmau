<!-- Một card sản phẩm dùng lại ở trang chủ, danh sách và sản phẩm liên quan. -->
<article class="product-card">
    <!-- Bấm vào card sẽ mở chi tiết theo product_id. -->
    <a href="<?= e(url('products/detail', ['id' => $item['product_id']])) ?>">
        <div class="product-image">
            <!-- Chỉ tạo thẻ img khi dữ liệu có tên ảnh. -->
            <?php if (!empty($item['image'])): ?>
                <!-- Đường dẫn ảnh ghép từ thư mục uploads; alt dùng tên sản phẩm;
                     loading="lazy" giúp ảnh chỉ tải khi gần xuất hiện trên màn hình. -->
                <img
                    src="<?= e(BASE_ASSETS_UPLOADS . $item['image']) ?>"
                    alt="<?= e($item['product_name']) ?>"
                    loading="lazy"
                >
            <?php else: ?>
                <!-- Hiển thị ô thay thế nếu sản phẩm chưa được upload ảnh. -->
                <div class="image-placeholder">Chưa có ảnh</div>
            <?php endif; ?>
        </div>
        <div class="product-info">
            <!-- Hiển thị danh mục và thương hiệu của sản phẩm. -->
            <div class="product-meta">
                <?= e($item['category_name']) ?>
                <!-- Thương hiệu là tùy chọn nên cần kiểm tra trước khi in. -->
                <?php if (!empty($item['brand_name'])): ?>
                    · <?= e($item['brand_name']) ?>
                <?php endif; ?>
            </div>
            <!-- e() mã hóa dữ liệu lấy từ database để chống XSS. -->
            <h3 class="product-name"><?= e($item['product_name']) ?></h3>
            <!-- formatPrice() đổi số thành định dạng tiền Việt Nam. -->
            <div class="product-price"><?= e(formatPrice($item['price'])) ?></div>
             <?php if ((int) ($item['stock'] ?? 0) <= 0): ?>
                <span class="product-card-stock-out">Tạm hết hàng</span>
            <?php endif; ?>
        </div>
    </a>
</article>
