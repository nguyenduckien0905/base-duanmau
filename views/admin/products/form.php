<!-- Form thêm hoặc sửa sản phẩm. -->
<div class="form-card form-card-wide">
    <?php require PATH_VIEW . 'admin/layouts/errors.php'; ?>

    <form action="<?= e($formAction) ?>" method="post" enctype="multipart/form-data">
        <?= csrfField() ?>

        <!-- Thông tin chung của sản phẩm. -->
        <div class="form-grid">
            <div class="form-group form-span-2">
                <label for="product_name">Tên sản phẩm <span>*</span></label>
                <input
                    id="product_name"
                    type="text"
                    name="product_name"
                    value="<?= e($product['product_name']) ?>"
                    maxlength="200"
                    required
                >
            </div>

            <div class="form-group">
                <label for="category_id">Danh mục <span>*</span></label>
                <select id="category_id" name="category_id" required>
                    <option value="">-- Chọn danh mục --</option>

                    <?php foreach ($categories as $category): ?>
                        <option
                            value="<?= e($category['category_id']) ?>"
                            <?= (int) $product['category_id'] === (int) $category['category_id'] ? 'selected' : '' ?>
                        >
                            <?= e($category['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="brand_id">Thương hiệu</label>
                <select id="brand_id" name="brand_id">
                    <option value="">-- Không có thương hiệu --</option>

                    <?php foreach ($brands as $brand): ?>
                        <option
                            value="<?= e($brand['brand_id']) ?>"
                            <?= (int) ($product['brand_id'] ?? 0) === (int) $brand['brand_id'] ? 'selected' : '' ?>
                        >
                            <?= e($brand['brand_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="price">Giá bán chung <span>*</span></label>
                <input
                    id="price"
                    type="number"
                    name="price"
                    value="<?= e($product['price']) ?>"
                    min="1"
                    step="1000"
                    required
                >
                <small>Các biến thể đang sử dụng cùng một giá bán.</small>
            </div>

            <div class="form-group">
                <label for="material">Chất liệu</label>
                <input
                    id="material"
                    type="text"
                    name="material"
                    value="<?= e($product['material']) ?>"
                    maxlength="100"
                >
            </div>

            <div class="form-group form-span-2">
                <label for="description">Mô tả</label>
                <textarea id="description" name="description" rows="5"><?= e($product['description']) ?></textarea>
            </div>

            <div class="form-group form-span-2">
                <label for="image">Ảnh sản phẩm</label>
                <input id="image" type="file" name="image" accept=".jpg,.jpeg,.png,.webp">
                <small>JPG, PNG hoặc WEBP; tối đa 2 MB.</small>

                <?php if (!empty($product['image'])): ?>
                    <img
                        class="image-preview"
                        src="<?= e(BASE_ASSETS_UPLOADS . $product['image']) ?>"
                        alt="<?= e($product['product_name']) ?>"
                    >
                <?php endif; ?>
            </div>
        </div>

        <!-- Danh sách tổ hợp màu - size - tồn kho. -->
        <section class="variant-box">
            <div class="variant-heading">
                <div>
                    <h2>Biến thể sản phẩm</h2>
                    <p>Mỗi dòng là một tổ hợp màu, size và tồn kho riêng.</p>
                </div>

                <button
                    id="add-variant"
                    class="btn btn-light"
                    type="button"
                >
                    + Thêm biến thể
                </button>
            </div>

            <div class="table-wrap">
                <table class="variant-table">
                    <thead>
                        <tr>
                            <th>Màu sắc</th>
                            <th>Kích thước</th>
                            <th>Tồn kho</th>
                            <th></th>
                        </tr>
                    </thead>

                    <tbody id="variant-body">
                        <?php foreach ($variants as $variant): ?>
                            <tr class="variant-row">
                                <td>
                                    <select name="variant_color[]" required>
                                        <option value="">-- Chọn màu --</option>

                                        <?php foreach ($colorOptions as $color): ?>
                                            <option
                                                value="<?= e($color) ?>"
                                                <?= ($variant['color'] ?? '') === $color ? 'selected' : '' ?>
                                            >
                                                <?= e($color) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>

                                <td>
                                    <select name="variant_size[]" required>
                                        <option value="">-- Chọn size --</option>

                                        <?php foreach ($sizeOptions as $size): ?>
                                            <option
                                                value="<?= e($size) ?>"
                                                <?= ($variant['size'] ?? '') === $size ? 'selected' : '' ?>
                                            >
                                                <?= e($size) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>

                                <td>
                                    <input
                                        type="number"
                                        name="variant_stock[]"
                                        value="<?= e($variant['stock'] ?? 0) ?>"
                                        min="0"
                                        required
                                    >
                                </td>

                                <td>
                                    <button
                                        class="btn btn-danger btn-small remove-variant"
                                        type="button"
                                    >
                                        Xóa dòng
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- Trạng thái chung của sản phẩm. -->
        <label class="check-row">
            <input
                type="checkbox"
                name="status"
                value="1"
                <?= (int) $product['status'] === 1 ? 'checked' : '' ?>
            >
            <span>Cho phép bán sản phẩm</span>
        </label>

        <!-- Nút điều khiển form. -->
        <div class="form-actions">
            <a class="btn btn-light" href="<?= e(url('admin/products')) ?>">
                Quay lại
            </a>

            <button class="btn btn-primary" type="submit">
                Lưu sản phẩm
            </button>
        </div>
    </form>
</div>

<!-- Mẫu HTML dùng khi bấm Thêm biến thể. -->
<template id="variant-row-template">
    <tr class="variant-row">
        <td>
            <select name="variant_color[]" required>
                <option value="">-- Chọn màu --</option>

                <?php foreach ($colorOptions as $color): ?>
                    <option value="<?= e($color) ?>">
                        <?= e($color) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </td>

        <td>
            <select name="variant_size[]" required>
                <option value="">-- Chọn size --</option>

                <?php foreach ($sizeOptions as $size): ?>
                    <option value="<?= e($size) ?>">
                        <?= e($size) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </td>

        <td>
            <input
                type="number"
                name="variant_stock[]"
                value="0"
                min="0"
                required
            >
        </td>

        <td>
            <button
                class="btn btn-danger btn-small remove-variant"
                type="button"
            >
                Xóa dòng
            </button>
        </td>
    </tr>
</template>

<script>
    // Lấy các phần tử cần thao tác.
    const variantBody = document.getElementById('variant-body');
    const addVariantButton = document.getElementById('add-variant');
    const variantTemplate = document.getElementById('variant-row-template');

    // Thêm một dòng biến thể mới.
    addVariantButton.addEventListener('click', function () {
        const newRow = variantTemplate.content.cloneNode(true);
        variantBody.appendChild(newRow);
    });

    // Bắt sự kiện xóa bằng event delegation.
    variantBody.addEventListener('click', function (event) {
        // Bỏ qua nếu phần tử được bấm không phải nút xóa.
        if (!event.target.classList.contains('remove-variant')) {
            return;
        }

        // Lấy tất cả dòng biến thể hiện tại.
        const rows = variantBody.querySelectorAll('.variant-row');

        // Luôn giữ ít nhất một dòng trên form.
        if (rows.length === 1) {
            const currentRow = event.target.closest('.variant-row');

            currentRow.querySelector('[name="variant_color[]"]').value = '';
            currentRow.querySelector('[name="variant_size[]"]').value = '';
            currentRow.querySelector('[name="variant_stock[]"]').value = 0;

            return;
        }

        // Xóa đúng dòng chứa nút vừa được bấm.
        event.target.closest('.variant-row').remove();
    });
</script>
