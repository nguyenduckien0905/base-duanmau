<!-- Form thêm hoặc sửa sản phẩm. -->
<div class="form-card form-card-wide">
    <?php require PATH_VIEW . 'admin/layouts/errors.php'; ?>

    <form action="<?= e($formAction) ?>" method="post" enctype="multipart/form-data">
        <?= csrfField() ?>

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
                <label for="price">Giá bán <span>*</span></label>
                <input
                    id="price"
                    type="number"
                    name="price"
                    value="<?= e($product['price']) ?>"
                    min="1"
                    step="1000"
                    required
                >
            </div>

            <div class="form-group">
                <label for="stock">Tồn kho</label>
                <input
                    id="stock"
                    type="number"
                    name="stock"
                    value="<?= e($product['stock']) ?>"
                    min="0"
                >
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

            <div class="form-group">
                <label for="color">Màu sắc</label>
                <input
                    id="color"
                    type="text"
                    name="color"
                    value="<?= e($product['color']) ?>"
                    maxlength="100"
                    placeholder="Đen, Trắng, Xanh..."
                >
            </div>

            <div class="form-group">
                <label for="size">Kích thước</label>
                <input
                    id="size"
                    type="text"
                    name="size"
                    value="<?= e($product['size']) ?>"
                    maxlength="50"
                    placeholder="S,M,L,XL"
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

        <label class="check-row">
            <input
                type="checkbox"
                name="status"
                value="1"
                <?= (int) $product['status'] === 1 ? 'checked' : '' ?>
            >
            <span>Cho phép bán sản phẩm</span>
        </label>

        <div class="form-actions">
            <a class="btn btn-light" href="<?= e(url('admin/products')) ?>">Quay lại</a>
            <button class="btn btn-primary" type="submit">Lưu sản phẩm</button>
        </div>
    </form>
</div>
