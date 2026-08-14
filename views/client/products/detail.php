<?php
// Tạo dữ liệu hiển thị từ bảng product_variants, không đọc cột color/size cũ.
$availableVariants = array_values(array_filter(
    $variants,
    static fn(array $variant): bool => (int) $variant['stock'] > 0
));

$colors = array_values(array_unique(array_column($variants, 'color')));
$sizes = array_values(array_unique(array_column($variants, 'size')));

$availableColors = array_values(array_unique(
    array_column($availableVariants, 'color')
));

$firstColor = $availableColors[0] ?? '';
$firstColorVariants = array_values(array_filter(
    $availableVariants,
    static fn(array $variant): bool => $variant['color'] === $firstColor
));
$firstVariant = $firstColorVariants[0] ?? null;

$variantJson = json_encode(
    array_map(
        static fn(array $variant): array => [
            'variant_id' => (int) $variant['variant_id'],
            'color' => (string) $variant['color'],
            'size' => (string) $variant['size'],
            'stock' => (int) $variant['stock'],
        ],
        $availableVariants
    ),
    JSON_UNESCAPED_UNICODE
    | JSON_HEX_TAG
    | JSON_HEX_AMP
    | JSON_HEX_APOS
    | JSON_HEX_QUOT
);
?>

<div class="container">
    <div class="breadcrumb">
        <a href="<?= e(url('/')) ?>">Trang chủ</a>
        / <a href="<?= e(url('products')) ?>">Sản phẩm</a>
        / <?= e($product['product_name']) ?>
    </div>

    <section class="detail-grid">
        <div class="detail-image">
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
            <div class="eyebrow"><?= e($product['category_name']) ?></div>
            <h1><?= e($product['product_name']) ?></h1>

            <?php if (!empty($product['brand_name'])): ?>
                <div class="product-meta">
                    Thương hiệu: <?= e($product['brand_name']) ?>
                </div>
            <?php endif; ?>

            <div class="detail-price"><?= e(formatPrice($product['price'])) ?></div>

            <?php if ((int) ($reviewSummary['review_count'] ?? 0) > 0): ?>
                <div class="product-rating-summary">
                    <span class="review-stars">★</span>
                    <strong><?= e(number_format((float) $reviewSummary['average_rating'], 1)) ?>/5</strong>
                    <span>(<?= e($reviewSummary['review_count']) ?> đánh giá đã mua hàng)</span>
                </div>
            <?php endif; ?>

            <?php if ((int) $product['stock'] > 0): ?>
                <span class="stock">
                    Tổng kho: <?= e($product['stock']) ?> sản phẩm
                </span>
            <?php else: ?>
                <span class="stock out">Tạm hết hàng</span>
            <?php endif; ?>

            <div class="specs">
                <?php if (!empty($product['material'])): ?>
                    <div class="spec-row">
                        <strong>Chất liệu</strong>
                        <span><?= e($product['material']) ?></span>
                    </div>
                <?php endif; ?>

                <?php if (!empty($colors)): ?>
                    <div class="spec-row">
                        <strong>Màu sắc</strong>
                        <span><?= e(implode(', ', $colors)) ?></span>
                    </div>
                <?php endif; ?>

                <?php if (!empty($sizes)): ?>
                    <div class="spec-row">
                        <strong>Kích thước</strong>
                        <span><?= e(implode(', ', $sizes)) ?></span>
                    </div>
                <?php endif; ?>
            </div>

            <?php if (!empty($product['description'])): ?>
                <h3>Mô tả sản phẩm</h3>
                <div class="description"><?= e($product['description']) ?></div>
            <?php endif; ?>

            <?php if (!empty($availableVariants)): ?>
                <form
                    class="add-cart-form"
                    action="<?= e(url('cart/add')) ?>"
                    method="post"
                    id="variant-add-cart-form"
                >
                    <?= csrfField() ?>
                    <input
                        type="hidden"
                        name="product_id"
                        value="<?= e($product['product_id']) ?>"
                    >

                    <label>
                        Màu sắc
                        <select
                            class="field"
                            id="variant-color"
                            name="selected_color"
                            required
                        >
                            <?php foreach ($availableColors as $color): ?>
                                <option value="<?= e($color) ?>">
                                    <?= e($color) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </label>

                    <label>
                        Kích thước
                        <select
                            class="field"
                            id="variant-size"
                            name="variant_id"
                            required
                        >
                            <?php foreach ($firstColorVariants as $variant): ?>
                                <option value="<?= e($variant['variant_id']) ?>">
                                    <?= e($variant['size']) ?>
                                    — còn <?= e($variant['stock']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </label>

                    <label>
                        Số lượng
                        <input
                            class="field"
                            id="variant-quantity"
                            type="number"
                            name="quantity"
                            value="1"
                            min="1"
                            max="<?= e($firstVariant['stock'] ?? 1) ?>"
                            required
                        >
                    </label>

                    <p class="variant-stock-note" id="variant-stock-note">
                        Còn <?= e($firstVariant['stock'] ?? 0) ?> sản phẩm
                        cho lựa chọn này.
                    </p>

                    <button class="btn btn-primary" type="submit">
                        Thêm vào giỏ
                    </button>
                </form>
            <?php elseif (empty($variants)): ?>
                <div class="alert alert-error">
                    Sản phẩm chưa được Admin thiết lập biến thể màu và size.
                </div>
            <?php else: ?>
                <div class="alert alert-error">
                    Tất cả biến thể của sản phẩm hiện đã hết hàng.
                </div>
            <?php endif; ?>

            <div class="detail-actions">
                <a class="btn btn-light" href="<?= e(url('products')) ?>">
                    ← Tiếp tục xem sản phẩm
                </a>
            </div>
        </div>
    </section>
</div>

<?php if (!empty($availableVariants)): ?>
    <script>
        (() => {
            const variants = <?= $variantJson ?: '[]' ?>;
            const colorSelect = document.getElementById('variant-color');
            const sizeSelect = document.getElementById('variant-size');
            const quantityInput = document.getElementById('variant-quantity');
            const stockNote = document.getElementById('variant-stock-note');

            const selectedVariant = () => variants.find(
                (variant) => variant.variant_id === Number(sizeSelect.value)
            );

            const updateStock = () => {
                const variant = selectedVariant();
                const stock = variant ? variant.stock : 0;

                quantityInput.max = String(Math.max(stock, 1));

                if (Number(quantityInput.value) > stock) {
                    quantityInput.value = String(Math.max(stock, 1));
                }

                stockNote.textContent = `Còn ${stock} sản phẩm cho lựa chọn này.`;
            };

            const updateSizes = () => {
                const matchingVariants = variants.filter(
                    (variant) => variant.color === colorSelect.value
                );

                sizeSelect.replaceChildren();

                matchingVariants.forEach((variant) => {
                    const option = new Option(
                        `${variant.size} — còn ${variant.stock}`,
                        String(variant.variant_id)
                    );
                    sizeSelect.add(option);
                });

                updateStock();
            };

            colorSelect.addEventListener('change', updateSizes);
            sizeSelect.addEventListener('change', updateStock);
        })();
    </script>
<?php endif; ?>

<section class="section product-reviews-section">
    <div class="container">
        <div class="section-heading">
            <div>
                <h2>Đánh giá từ khách đã mua</h2>
                <p>Chỉ khách đã xác nhận nhận hàng mới có thể đánh giá.</p>
            </div>
        </div>

        <?php if (!empty($reviews)): ?>
            <div class="product-review-list">
                <?php foreach ($reviews as $review): ?>
                    <article class="product-review-card">
                        <div class="product-review-heading">
                            <strong><?= e($review['fullname']) ?></strong>
                            <span class="review-stars"><?= str_repeat('★', (int) $review['rating']) ?></span>
                        </div>
                        <p><?= e($review['comment'] ?: 'Không có nội dung.') ?></p>
                        <small><?= e(date('d/m/Y H:i', strtotime($review['created_at']))) ?></small>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty compact-empty">
                <p>Sản phẩm chưa có đánh giá.</p>
            </div>
        <?php endif; ?>
    </div>
</section>

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
                <?php foreach ($relatedProducts as $item): ?>
                    <?php require PATH_VIEW . 'client/products/_card.php'; ?>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
<?php endif; ?>
