<!-- Form thêm hoặc sửa banner. -->
<div class="form-card">
    <?php require PATH_VIEW . 'admin/layouts/errors.php'; ?>

    <form action="<?= e($formAction) ?>" method="post" enctype="multipart/form-data">
        <?= csrfField() ?>

        <div class="form-group">
            <label for="title">Tiêu đề banner <span>*</span></label>
            <input
                id="title"
                type="text"
                name="title"
                value="<?= e($banner['title']) ?>"
                maxlength="200"
                required
            >
        </div>

        <div class="form-group">
            <label for="link">Đường dẫn khi bấm banner</label>
            <input
                id="link"
                type="text"
                name="link"
                value="<?= e($banner['link']) ?>"
                maxlength="255"
                placeholder="index.php?action=products"
            >
        </div>

        <div class="form-group">
            <label for="image">
                Ảnh banner <?= empty($banner['image']) ? '<span>*</span>' : '' ?>
            </label>
            <input id="image" type="file" name="image" accept=".jpg,.jpeg,.png,.webp">
            <small>JPG, PNG hoặc WEBP; tối đa 2 MB.</small>

            <?php if (!empty($banner['image'])): ?>
                <img
                    class="banner-preview"
                    src="<?= e(BASE_ASSETS_UPLOADS . $banner['image']) ?>"
                    alt="<?= e($banner['title']) ?>"
                >
            <?php endif; ?>
        </div>

        <label class="check-row">
            <input
                type="checkbox"
                name="status"
                value="1"
                <?= (int) $banner['status'] === 1 ? 'checked' : '' ?>
            >
            <span>Hiển thị banner trên website</span>
        </label>

        <div class="form-actions">
            <a class="btn btn-light" href="<?= e(url('admin/banners')) ?>">Quay lại</a>
            <button class="btn btn-primary" type="submit">Lưu banner</button>
        </div>
    </form>
</div>
