<!-- Chân trang -->
<footer class="admin-main bg-white border-top py-3">
    <div class="container-fluid">
        <div class="d-flex justify-content-between">
            <span>
                &copy; <?= date('Y') ?> Shop bán quần áo
            </span>

            <span class="text-muted">
                Trang quản trị
            </span>
        </div>
    </div>
</footer>

<!-- Bootstrap JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
    /*
    |--------------------------------------------------------------------------
    | ĐÓNG/MỞ SIDEBAR TRÊN ĐIỆN THOẠI
    |--------------------------------------------------------------------------
    */

    // Lấy nút mở menu.
    const toggleSidebar = document.getElementById('toggleSidebar');

    // Lấy sidebar.
    const adminSidebar = document.getElementById('adminSidebar');

    // Chỉ xử lý khi hai phần tử tồn tại.
    if (toggleSidebar && adminSidebar) {
        toggleSidebar.addEventListener('click', function () {
            adminSidebar.classList.toggle('show');
        });
    }
</script>

</body>
</html>