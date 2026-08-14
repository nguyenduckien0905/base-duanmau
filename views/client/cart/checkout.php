<section class="section">
    <div class="container">
        <div class="section-heading">
            <div><h1>Thanh toán</h1><p>Kiểm tra thông tin trước khi đặt đơn.</p></div>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <?php foreach (array_unique($errors) as $error): ?>
                    <div><?= e($error) ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form class="checkout-layout" action="<?= e(url('checkout')) ?>"
              method="post" enctype="multipart/form-data" id="checkout-form">
            <?= csrfField() ?>

            <div class="checkout-panel client-form">
                <h2>Thông tin nhận hàng</h2>
                <label>Người nhận
                    <input class="field" type="text" name="receiver_name"
                           value="<?= e($form['receiver_name']) ?>" required>
                </label>
                <label>Số điện thoại
                    <input class="field" type="tel" name="receiver_phone"
                           value="<?= e($form['receiver_phone']) ?>" required>
                </label>
                <label>Địa chỉ giao hàng
                    <textarea class="field" name="shipping_address" rows="4" required><?= e($form['shipping_address']) ?></textarea>
                </label>
                <label>Ghi chú
                    <textarea class="field" name="note" rows="3"><?= e($form['note']) ?></textarea>
                </label>

                <h2>Phương thức thanh toán</h2>
                <label class="radio-row">
                    <input type="radio" name="payment_method" value="cod"
                           <?= $form['payment_method'] === 'cod' ? 'checked' : '' ?>>
                    Thanh toán khi nhận hàng (COD)
                </label>
                <label class="radio-row">
                    <input type="radio" name="payment_method" value="bank_transfer"
                           <?= $form['payment_method'] === 'bank_transfer' ? 'checked' : '' ?>>
                    Chuyển khoản ngân hàng
                </label>

                <div class="bank-transfer-box" id="bank-transfer-box">
                    <div class="bank-qr-wrap">
                        <img class="bank-qr-image" src="<?= e(BANK_QR_IMAGE) ?>"
                             alt="Mã QR chuyển khoản <?= e(BANK_NAME) ?>">
                    </div>
                    <div class="bank-information">
                        <h3>Thông tin chuyển khoản</h3>
                        <p><strong>Ngân hàng:</strong> <?= e(BANK_NAME) ?></p>
                        <p><strong>Số tài khoản:</strong> <?= e(BANK_ACCOUNT_NUMBER) ?></p>
                        <p><strong>Chủ tài khoản:</strong> <?= e(BANK_ACCOUNT_HOLDER) ?></p>
                        <p class="bank-note">Nội dung: Họ tên và số điện thoại nhận hàng.</p>
                    </div>
                    <label class="payment-proof-field">
                        Ảnh chụp màn hình đã chuyển khoản <span>*</span>
                        <input class="field" id="payment-proof" type="file"
                               name="payment_proof"
                               accept="image/jpeg,image/png,image/webp">
                        <small>JPG, PNG hoặc WEBP; tối đa 5 MB.</small>
                    </label>
                </div>
            </div>

            <aside class="cart-summary">
                <h2>Đơn hàng</h2>
                <?php foreach ($cart as $item): ?>
                    <div class="checkout-item">
                        <span><?= e($item['product_name']) ?> × <?= e($item['quantity']) ?>
                            <small>Màu: <?= e($item['color']) ?> · Size: <?= e($item['size']) ?></small>
                        </span>
                        <strong><?= e(formatPrice($item['price'] * $item['quantity'])) ?></strong>
                    </div>
                <?php endforeach; ?>

                <div class="coupon-box">
                    <label for="coupon_code">Mã giảm giá</label>
                    <div class="coupon-input-row">
                        <input class="field" id="coupon_code" type="text"
                               name="coupon_code" value="<?= e($form['coupon_code']) ?>"
                               maxlength="50" placeholder="VD: SALE10">
                        <button class="btn btn-light" type="submit" name="intent"
                                value="apply_coupon" formnovalidate>Áp dụng</button>
                    </div>
                    <?php if ($coupon): ?>
                        <p class="coupon-success">
                            Đã áp dụng <strong><?= e($coupon['code']) ?></strong>,
                            giảm <?= e(formatPrice($discount)) ?>.
                        </p>
                    <?php endif; ?>
                </div>

                <div class="summary-row"><span>Tạm tính</span><strong><?= e(formatPrice($subtotal)) ?></strong></div>
                <div class="summary-row"><span>Phí giao hàng</span><strong><?= e(formatPrice($shippingFee)) ?></strong></div>
                <div class="summary-row"><span>Giảm giá</span><strong>-<?= e(formatPrice($discount)) ?></strong></div>
                <div class="summary-row grand-total"><span>Tổng thanh toán</span><strong><?= e(formatPrice($total)) ?></strong></div>

                <button class="btn btn-primary btn-full" type="submit"
                        name="intent" value="place_order">Hoàn tất đặt hàng</button>
                <p class="checkout-contact-note">
                    Nhân viên sẽ kiểm tra chuyển khoản và liên hệ với bạn.
                </p>
            </aside>
        </form>
    </div>
</section>

<script>
    (() => {
        const radios = document.querySelectorAll('input[name="payment_method"]');
        const bankBox = document.getElementById('bank-transfer-box');
        const proofInput = document.getElementById('payment-proof');
        const updatePaymentFields = () => {
            const selected = document.querySelector('input[name="payment_method"]:checked');
            const isBank = selected?.value === 'bank_transfer';
            bankBox.hidden = !isBank;
            proofInput.required = isBank;
        };
        radios.forEach((radio) => radio.addEventListener('change', updatePaymentFields));
        updatePaymentFields();
    })();
</script>
