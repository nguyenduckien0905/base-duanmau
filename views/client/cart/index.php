<!-- Trang giỏ hàng lấy dữ liệu từ session sau khi đã đồng bộ database. -->
<section class="section">
    <div class="container">
        <div class="section-heading">
            <div>
                <h1>Giỏ hàng</h1>
                <p><?= cartCount() ?> sản phẩm đang được chọn</p>
            </div>
        </div>

        <?php if (!empty($cart)): ?>
            <!-- Một form cập nhật số lượng cho toàn bộ giỏ. -->
            <form action="<?= e(url('cart/update')) ?>" method="post">
                <?= csrfField() ?>

                <div class="cart-layout">
                    <div class="cart-list">
                        <?php foreach ($cart as $key => $item): ?>
                            <article class="cart-item">
                                <div class="cart-thumb">
                                    <?php if (!empty($item['image'])): ?>
                                        <img src="<?= e(BASE_ASSETS_UPLOADS . $item['image']) ?>" alt="<?= e($item['product_name']) ?>">
                                    <?php else: ?>
                                        <div class="image-placeholder">Chưa có ảnh</div>
                                    <?php endif; ?>
                                </div>

                                <div class="cart-info">
                                    <a href="<?= e(url('products/detail', ['id' => $item['product_id']])) ?>">
                                        <h3><?= e($item['product_name']) ?></h3>
                                    </a>
                                    <p>Size: <?= e($item['size'] ?: '-') ?> · Màu: <?= e($item['color'] ?: '-') ?></p>
                                    <strong class="product-price"><?= e(formatPrice($item['price'])) ?></strong>
                                </div>

                                <label class="cart-quantity">
                                    Số lượng
                                    <input
                                        class="field"
                                        type="number"
                                        name="quantities[<?= e($key) ?>]"
                                        value="<?= e($item['quantity']) ?>"
                                        min="0"
                                        max="<?= e($item['stock']) ?>"
                                    >
                                </label>

                                <strong><?= e(formatPrice($item['price'] * $item['quantity'])) ?></strong>

                                <!-- Nút xóa dùng formaction để gửi cùng CSRF nhưng sang route remove. -->
                                <button
                                    class="link-button danger"
                                    type="submit"
                                    name="key"
                                    value="<?= e($key) ?>"
                                    formaction="<?= e(url('cart/remove')) ?>"
                                >
                                    Xóa
                                </button>
                            </article>
                        <?php endforeach; ?>
                    </div>

                    <aside class="cart-summary">
                        <h2>Tóm tắt đơn hàng</h2>
                        <div class="summary-row">
                            <span>Tạm tính</span>
                            <strong><?= e(formatPrice($subtotal)) ?></strong>
                        </div>
                        <p>Phí giao hàng được tính ở bước thanh toán.</p>
                        <button class="btn btn-light btn-full" type="submit">Cập nhật giỏ</button>
                        <a class="btn btn-primary btn-full" href="<?= e(url('checkout')) ?>">Tiến hành thanh toán</a>
                    </aside>
                </div>
            </form>
        <?php else: ?>
            <div class="empty">
                <h2>Giỏ hàng đang trống</h2>
                <p>Hãy chọn sản phẩm trước khi thanh toán.</p>
                <a class="btn btn-primary" href="<?= e(url('products')) ?>">Xem sản phẩm</a>
            </div>
        <?php endif; ?>
    </div>
</section>
