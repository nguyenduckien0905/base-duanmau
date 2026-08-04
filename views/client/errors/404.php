<!-- Trang này được ProductController sử dụng khi id sản phẩm không tồn tại. -->
<section class="section">
    <div class="container">
        <div class="empty">
            <h1>404</h1>
            <h2>Không tìm thấy nội dung</h2>
            <p>Dữ liệu không tồn tại hoặc bạn không có quyền truy cập.</p>
            <!-- Nút đưa người dùng về trang chủ thay vì để họ ở trang lỗi. -->
            <a class="btn btn-primary" href="<?= e(url('/')) ?>">Quay lại trang chủ</a>
        </div>
    </div>
</section>
