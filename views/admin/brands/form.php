<!-- Form thêm hoặc sửa thương hiệu. -->
<div class="form-card">
    <?php require PATH_VIEW . 'admin/layouts/errors.php'; ?>

    <form action="<?= e($formAction) ?>" method="post" enctype="multipart/form-data">
        <?= csrfField() ?>

        <div class="form-group">
            <label for="brand_name">Tên thương hiệu <span>*</span></label>
            <input
                id="brand_name"
                type="text"
                name="brand_name"
                value="<?= e($brand['brand_name']) ?>"
                maxlength="100"
                required
            >
        </div>

        <div class="form-group">
            <label for="logo">Logo thương hiệu</label>
            <input id="logo" type="file" name="logo" accept=".jpg,.jpeg,.png,.webp">
            <small>JPG, PNG hoặc WEBP; tối đa 2 MB.</small>

            <?php if (!empty($brand['logo'])): ?>
                <img
                    class="image-preview"
                    src="<?= e(BASE_ASSETS_UPLOADS . $brand['logo']) ?>"
                    alt="<?= e($brand['brand_name']) ?>"
                >
            <?php endif; ?>
        </div>

        <label class="check-row">
            <input
                type="checkbox"
                name="status"
                value="1"
                <?= (int) $brand['status'] === 1 ? 'checked' : '' ?>
            >
            <span>Cho phép thương hiệu hoạt động</span>
        </label>

        <div class="form-actions">
            <a class="btn btn-light" href="<?= e(url('admin/brands')) ?>">Quay lại</a>
            <button class="btn btn-primary" type="submit">Lưu thương hiệu</button>
        </div>
    </form>
</div>
