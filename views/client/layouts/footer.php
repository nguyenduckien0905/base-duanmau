<!-- Chân trang dùng chung cho tất cả trang Client. -->
<footer class="footer">
    <div class="container">
        <!-- Chia nội dung footer thành ba cột trên màn hình lớn. -->
        <div class="footer-grid">
            <div>
                <h3>K FASHION</h3>
                <p>Thời trang trẻ trung, dễ phối và phù hợp với phong cách mỗi ngày.</p>
            </div>
            <div>
                <h3>Liên kết</h3>
                <p><a href="<?= e(url('/')) ?>">Trang chủ</a></p>
                <p><a href="<?= e(url('products')) ?>">Sản phẩm</a></p>
            </div>
            <div>
                <h3>Hỗ trợ</h3>
                <p>Hotline: 0909 888 999</p>
                <p>Email: support@kfashion.vn</p>
            </div>
        </div>
        <!-- date('Y') luôn hiển thị năm hiện tại theo cấu hình múi giờ. -->
        <div class="copyright">© <?= date('Y') ?> K Fashion. All rights reserved.</div>
    </div>
</footer>
