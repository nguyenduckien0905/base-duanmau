<div class="container">
    <!-- Breadcrumb giúp người dùng biết vị trí hiện tại và quay lại nhanh. -->
    <div class="breadcrumb">
        <a href="<?= e(url('/')) ?>">Trang chủ</a>
        / <a href="<?= e(url('products')) ?>">Sản phẩm</a>
        / <?= e($product['product_name']) ?>
    </div>

    <!-- Lưới hai cột: ảnh bên trái và thông tin bên phải. -->
    <section class="detail-grid">
        <div class="detail-image">
            <!-- Kiểm tra ảnh trước khi tạo thẻ img. -->
            <?php if (!empty($product['image'])): ?>
                <img
                    src="<?= e(BASE_ASSETS_UPLOADS . $product['image']) ?>"
                    alt="<?= e($product['product_name']) ?>"
                >
            <?php else: ?>
                <div class="image-placeholder">Chưa có ảnh</div>
            <?php endif; ?>
        </div>

        <div class="detail-info">
            <!-- Tên danh mục được dùng làm nhãn nhỏ phía trên tên sản phẩm. -->
            <div class="eyebrow"><?= e($product['category_name']) ?></div>
            <h1><?= e($product['product_name']) ?></h1>

            <!-- Chỉ in thương hiệu nếu sản phẩm đã được gán brand. -->
            <?php if (!empty($product['brand_name'])): ?>
                <div class="product-meta">Thương hiệu: <?= e($product['brand_name']) ?></div>
            <?php endif; ?>

            <div class="detail-price"><?= e(formatPrice($product['price'])) ?></div>

            <!-- Tồn kho lớn hơn 0 hiển thị nhãn xanh, ngược lại nhãn đỏ. -->
            <?php if ((int) $product['stock'] > 0): ?>
                <span class="stock">Còn <?= e($product['stock']) ?> sản phẩm</span>
            <?php else: ?>
                <span class="stock out">Tạm hết hàng</span>
            <?php endif; ?>

            <!-- Mỗi thông số đều được kiểm tra để không hiển thị dòng trống. -->
            <div class="specs">
                <?php if (!empty($product['material'])): ?>
                    <div class="spec-row"><strong>Chất liệu</strong><span><?= e($product['material']) ?></span></div>
                <?php endif; ?>
                <?php if (!empty($product['color'])): ?>
                    <div class="spec-row"><strong>Màu sắc</strong><span><?= e($product['color']) ?></span></div>
                <?php endif; ?>
                <?php if (!empty($product['size'])): ?>
                    <div class="spec-row"><strong>Kích thước</strong><span><?= e($product['size']) ?></span></div>
                <?php endif; ?>
            </div>

            <!-- Mô tả là tùy chọn nên chỉ tạo khu vực khi có nội dung. -->
            <?php if (!empty($product['description'])): ?>
                <h3>Mô tả sản phẩm</h3>
                <div class="description"><?= e($product['description']) ?></div>
            <?php endif; ?>

            <!-- Form thêm giỏ chỉ hiện khi sản phẩm còn hàng. -->
            <?php if ((int) $product['stock'] > 0): ?>
                <form class="add-cart-form" action="<?= e(url('cart/add')) ?>" method="post">
                    <?= csrfField() ?>
                    <input type="hidden" name="product_id" value="<?= e($product['product_id']) ?>">

                    <?php if (!empty($product['size'])): ?>
                        <label>
                            Kích thước
                            <select class="field" name="size" required>
                                <?php foreach (array_filter(array_map('trim', explode(',', $product['size']))) as $size): ?>
                                    <option value="<?= e($size) ?>"><?= e($size) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </label>
                    <?php else: ?>
                        <input type="hidden" name="size" value="">
                    <?php endif; ?>

                    <?php if (!empty($product['color'])): ?>
                        <label>
                            Màu sắc
                            <select class="field" name="color" required>
                                <?php foreach (array_filter(array_map('trim', explode(',', $product['color']))) as $color): ?>
                                    <option value="<?= e($color) ?>"><?= e($color) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </label>
                    <?php else: ?>
                        <input type="hidden" name="color" value="">
                    <?php endif; ?>

                    <label>
                        Số lượng
                        <input
                            class="field"
                            type="number"
                            name="quantity"
                            value="1"
                            min="1"
                            max="<?= e($product['stock']) ?>"
                            required
                        >
                    </label>

                    <button class="btn btn-primary" type="submit">Thêm vào giỏ</button>
                </form>
            <?php endif; ?>

            <div class="detail-actions">
                <a class="btn btn-light" href="<?= e(url('products')) ?>">← Tiếp tục xem sản phẩm</a>
            </div>
        </div>
    </section>
</div>

<!-- Chỉ hiển thị khu vực gợi ý khi model tìm được sản phẩm cùng danh mục. -->
<?php if (!empty($relatedProducts)): ?>
    <section class="section section-soft">
        <div class="container">
            <div class="section-heading">
                <div>
                    <h2>Sản phẩm liên quan</h2>
                    <p>Các sản phẩm cùng danh mục</p>
                </div>
            </div>
            <div class="product-grid">
                <!-- Dùng lại card chung để giao diện đồng nhất. -->
                <?php foreach ($relatedProducts as $item): ?>
                    <?php require PATH_VIEW . 'client/products/_card.php'; ?>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
<?php endif; ?>
