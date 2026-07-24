<!-- Form thêm hoặc sửa danh mục. -->
<div class="form-card">
    <?php require PATH_VIEW . 'admin/layouts/errors.php'; ?>

    <form action="<?= e($formAction) ?>" method="post" enctype="multipart/form-data">
        <?= csrfField() ?>

        <div class="form-grid">
            <div class="form-group">
                <label for="name">Tên danh mục <span>*</span></label>
                <input
                    id="name"
                    type="text"
                    name="name"
                    value="<?= e($category['name']) ?>"
                    maxlength="100"
                    required
                >
            </div>

            <div class="form-group">
                <label for="parent_id">Danh mục cha</label>
                <select id="parent_id" name="parent_id">
                    <option value="">-- Đây là danh mục gốc --</option>
                    <?php foreach ($parentCategories as $parent): ?>
                        <option
                            value="<?= e($parent['category_id']) ?>"
                            <?= (int) ($category['parent_id'] ?? 0) === (int) $parent['category_id'] ? 'selected' : '' ?>
                        >
                            <?= e($parent['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label for="image">Ảnh danh mục</label>
            <input id="image" type="file" name="image" accept=".jpg,.jpeg,.png,.webp">
            <small>JPG, PNG hoặc WEBP; tối đa 2 MB.</small>

            <?php if (!empty($category['image'])): ?>
                <img
                    class="image-preview"
                    src="<?= e(BASE_ASSETS_UPLOADS . $category['image']) ?>"
                    alt="<?= e($category['name']) ?>"
                >
            <?php endif; ?>
        </div>

        <label class="check-row">
            <input
                type="checkbox"
                name="status"
                value="1"
                <?= (int) $category['status'] === 1 ? 'checked' : '' ?>
            >
            <span>Hiển thị danh mục trên website</span>
        </label>

        <div class="form-actions">
            <a class="btn btn-light" href="<?= e(url('admin/categories')) ?>">Quay lại</a>
            <button class="btn btn-primary" type="submit">Lưu danh mục</button>
        </div>
    </form>
</div>
