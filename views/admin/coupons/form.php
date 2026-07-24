<!-- Form thêm hoặc sửa mã giảm giá. -->
<div class="form-card form-card-wide">
    <?php require PATH_VIEW . 'admin/layouts/errors.php'; ?>

    <form action="<?= e($formAction) ?>" method="post">
        <?= csrfField() ?>

        <div class="form-grid">
            <div class="form-group">
                <label for="code">Mã giảm giá <span>*</span></label>
                <input
                    id="code"
                    type="text"
                    name="code"
                    value="<?= e($coupon['code']) ?>"
                    maxlength="50"
                    placeholder="VD: SALE10"
                    required
                >
            </div>

            <div class="form-group">
                <label for="discount_type">Loại giảm <span>*</span></label>
                <select id="discount_type" name="discount_type" required>
                    <option
                        value="percent"
                        <?= $coupon['discount_type'] === 'percent' ? 'selected' : '' ?>
                    >
                        Theo phần trăm
                    </option>
                    <option
                        value="fixed"
                        <?= $coupon['discount_type'] === 'fixed' ? 'selected' : '' ?>
                    >
                        Số tiền cố định
                    </option>
                </select>
            </div>

            <div class="form-group">
                <label for="discount_value">Giá trị giảm <span>*</span></label>
                <input
                    id="discount_value"
                    type="number"
                    name="discount_value"
                    value="<?= e($coupon['discount_value']) ?>"
                    min="1"
                    step="0.01"
                    required
                >
            </div>

            <div class="form-group">
                <label for="max_discount">Giảm tối đa</label>
                <input
                    id="max_discount"
                    type="number"
                    name="max_discount"
                    value="<?= e($coupon['max_discount'] ?? '') ?>"
                    min="0"
                    step="1000"
                    placeholder="Để trống nếu không giới hạn"
                >
            </div>

            <div class="form-group">
                <label for="min_order_value">Giá trị đơn tối thiểu</label>
                <input
                    id="min_order_value"
                    type="number"
                    name="min_order_value"
                    value="<?= e($coupon['min_order_value']) ?>"
                    min="0"
                    step="1000"
                >
            </div>

            <div class="form-group">
                <label for="quantity">Số lượng</label>
                <input
                    id="quantity"
                    type="number"
                    name="quantity"
                    value="<?= e($coupon['quantity']) ?>"
                    min="0"
                >
            </div>

            <div class="form-group">
                <label for="start_date">Ngày bắt đầu <span>*</span></label>
                <input
                    id="start_date"
                    type="datetime-local"
                    name="start_date"
                    value="<?= e(date('Y-m-d\TH:i', strtotime($coupon['start_date']))) ?>"
                    required
                >
            </div>

            <div class="form-group">
                <label for="end_date">Ngày kết thúc <span>*</span></label>
                <input
                    id="end_date"
                    type="datetime-local"
                    name="end_date"
                    value="<?= e(date('Y-m-d\TH:i', strtotime($coupon['end_date']))) ?>"
                    required
                >
            </div>
        </div>

        <label class="check-row">
            <input
                type="checkbox"
                name="status"
                value="1"
                <?= (int) $coupon['status'] === 1 ? 'checked' : '' ?>
            >
            <span>Cho phép sử dụng mã giảm giá</span>
        </label>

        <div class="form-actions">
            <a class="btn btn-light" href="<?= e(url('admin/coupons')) ?>">Quay lại</a>
            <button class="btn btn-primary" type="submit">Lưu mã giảm giá</button>
        </div>
    </form>
</div>
