<!-- Trang thanh toán chỉ mở khi đã đăng nhập và giỏ không rỗng. -->
<section class="section">
    <div class="container">
        <div class="section-heading">
            <div>
                <h1>Thanh toán</h1>
                <p>Kiểm tra thông tin nhận hàng trước khi đặt đơn.</p>
            </div>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <?php foreach ($errors as $error): ?>
                    <div><?= e($error) ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form class="checkout-layout" action="<?= e(url('checkout')) ?>" method="post">
            <?= csrfField() ?>

            <div class="checkout-panel client-form">
                <h2>Thông tin nhận hàng</h2>

                <label>
                    Người nhận
                    <input class="field" type="text" name="receiver_name" value="<?= e($form['receiver_name']) ?>" required>
                </label>

                <label>
                    Số điện thoại
                    <input class="field" type="tel" name="receiver_phone" value="<?= e($form['receiver_phone']) ?>" required>
                </label>

                <label>
                    Địa chỉ giao hàng
                    <textarea class="field" name="shipping_address" rows="4" required><?= e($form['shipping_address']) ?></textarea>
                </label>

                <label>
                    Ghi chú
                    <textarea class="field" name="note" rows="3"><?= e($form['note']) ?></textarea>
                </label>

                <h2>Phương thức thanh toán</h2>
                <label class="radio-row">
                    <input type="radio" name="payment_method" value="cod" <?= $form['payment_method'] === 'cod' ? 'checked' : '' ?>>
                    Thanh toán khi nhận hàng (COD)
                </label>
                <label class="radio-row">
                    <input type="radio" name="payment_method" value="bank_transfer" <?= $form['payment_method'] === 'bank_transfer' ? 'checked' : '' ?>>
                    Chuyển khoản ngân hàng
                </label>
            </div>

            <aside class="cart-summary">
                <h2>Đơn hàng</h2>
                <?php foreach ($cart as $item): ?>
                    <div class="checkout-item">
                        <span><?= e($item['product_name']) ?> × <?= e($item['quantity']) ?></span>
                        <strong><?= e(formatPrice($item['price'] * $item['quantity'])) ?></strong>
                    </div>
                <?php endforeach; ?>

                <div class="summary-row"><span>Tạm tính</span><strong><?= e(formatPrice($subtotal)) ?></strong></div>
                <div class="summary-row"><span>Phí giao hàng</span><strong><?= e(formatPrice($shippingFee)) ?></strong></div>
                <div class="summary-row grand-total"><span>Tổng thanh toán</span><strong><?= e(formatPrice($total)) ?></strong></div>

                <button class="btn btn-primary btn-full" type="submit">Đặt hàng</button>
            </aside>
        </form>
    </div>
</section>
